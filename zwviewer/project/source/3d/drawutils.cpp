// ****** ****** ****** drawutils

#include <geometry.h>
#include <math.h>
#include <list.h>
#include <stdlib.h>

#include <os.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library
#include <media.h>

extern vector3d	gvX,gvY,gvZ;

void DrawCylinder (vector3d p1,vector3d p2,vector3d col,float fRad)
{
	vector3d v = p2-p1;
	vector3d x = fRad * norm(cross(vector3d(1,0,0),v));
	vector3d y = fRad * norm(cross(x,v));
	glColor3fv(&col.x);
	glBegin(GL_TRIANGLE_STRIP);
	for (float ang=0;ang<=2.0*kPi;ang+=2.0*kPi/9)
	{
		vector3d p = x * cos(ang) + y * sin(ang);
		glNormal3fv(&p.x);
		v = p1+p;glVertex3fv(&v.x);
		v = p2+p;glVertex3fv(&v.x);
	}
	glEnd();
}


void DrawCylinderCap (vector3d p1,vector3d p2,vector3d col,float fRad)
{
	float ang;
	vector3d v = p2-p1;
	vector3d x;
	if (abs(dot(vector3d(1,0,0),v)) < abs(dot(vector3d(0,1,0),v)))
			x = fRad * norm(cross(vector3d(1,0,0),v));
	else	x = fRad * norm(cross(vector3d(0,1,0),v));
	vector3d y = fRad * norm(cross(x,v));
	glColor3fv(&col.x);
	glBegin(GL_TRIANGLE_STRIP);
	for (ang=0;ang<=2.0*kPi;ang+=2.0*kPi/9)
	{
		vector3d p = x * cos(ang) + y * sin(ang);
		glNormal3fv(&p.x);
		v = p1+p;glVertex3fv(&v.x);
		v = p2+p;glVertex3fv(&v.x);
	}
	glEnd();
	glBegin(GL_TRIANGLE_FAN);
	v = norm(p2-p1);
	glNormal3fv(&v.x);
	for (ang=0;ang<=2.0*kPi;ang+=2.0*kPi/9)
	{
		vector3d p = p2 + x * cos(ang) + y * sin(ang);
		glVertex3fv(&p.x);
	}
	glEnd();
	glBegin(GL_TRIANGLE_FAN);
	v = -v;
	glNormal3fv(&v.x);
	for (ang=0;ang<=2.0*kPi;ang+=2.0*kPi/9)
	{
		vector3d p = p1 + x * cos(-ang) + y * sin(-ang);
		glVertex3fv(&p.x);
	}
	glEnd();
}

void DrawSphere	(vector3d p,vector3d col,float fRad)
{
	#define drawsphereres 7
	glTranslatef(p.x,p.y,p.z);
	GLUquadricObj* pQuad = gluNewQuadric();
	gluQuadricDrawStyle(pQuad,GLU_FILL);
	glColor3fv(&col.x);
	gluSphere(pQuad,fRad,drawsphereres,drawsphereres);
	gluDeleteQuadric(pQuad);
	glTranslatef(-p.x,-p.y,-p.z);
}

void DrawRect	(vector3d p,vector3d w,vector3d h)
{
	glBegin(GL_TRIANGLE_STRIP);
	vector3d v = norm(cross(w,h));
	glNormal3fv(&v.x);
	glVertex3fv(&p.x);
	v = p + w;glVertex3fv(&v.x);
	v = p + h;glVertex3fv(&v.x);
	v = p + w + h;glVertex3fv(&v.x);
	glEnd();
}
void DrawRect	(vector3d p,vector3d w,vector3d h,vector3d col)
{
	glColor3fv(&col.x);
	DrawRect(p,w,h);
}

void DrawBox	(vector3d lefttop,vector3d botright,vector3d col)
{
	DrawRect(lefttop,vector3d(botright.x-lefttop.x,0,0),vector3d(0,botright.y-lefttop.y,0),col);
	DrawRect(lefttop,vector3d(botright.x-lefttop.x,0,0),vector3d(0,0,botright.z-lefttop.z),col);
	DrawRect(lefttop,vector3d(0,botright.y-lefttop.y,0),vector3d(0,0,botright.z-lefttop.z),col);

	DrawRect(botright,vector3d(lefttop.x-botright.x,0,0),vector3d(0,lefttop.y-botright.y,0),col);
	DrawRect(botright,vector3d(lefttop.x-botright.x,0,0),vector3d(0,0,lefttop.z-botright.z),col);
	DrawRect(botright,vector3d(0,lefttop.y-botright.y,0),vector3d(0,0,lefttop.z-botright.z),col);
}

void DrawBillBoard		(const vector3d& pos,float w,float h,const vector3d& col) {
	glColor3fv(&col.x);
	vector3d zero = pos - (0.5*w)*gvX;
	vector3d v = -gvZ;
	glBegin(GL_TRIANGLE_STRIP);
	glNormal3fv(&v.x);
	glVertex3fv(&zero.x);
	v = zero + w*gvX;glVertex3fv(&v.x);
	v = zero + h*gvY;glVertex3fv(&v.x);
	v = zero + w*gvX+h*gvY;glVertex3fv(&v.x);
	glEnd();
}

void DrawBillBoard		(const vector3d& pos,float w,float h) {
	glEnable(GL_TEXTURE_2D);
	vector3d zero = pos - (0.5*w)*gvX;
	vector3d v = -gvZ;
	glBegin(GL_TRIANGLE_STRIP);
	glNormal3fv(&v.x);
	glTexCoord2f(0,1);
	glVertex3fv(&zero.x);
	v = zero + w*gvX;glTexCoord2f(1,1);glVertex3fv(&v.x);
	v = zero + h*gvY;glTexCoord2f(0,0);glVertex3fv(&v.x);
	v = zero + w*gvX+h*gvY;glTexCoord2f(1,0);glVertex3fv(&v.x);
	glEnd();
}


void	DrawWall	(const vector3d& a,const vector3d& b,float w,float h,bool cap_a,bool cap_b,cM_Texture* tex) {
	if (!tex) return;
	glEnable(GL_TEXTURE_2D);
	tex->Bind();
	vector3d up = vector3d(0,0,h);
	vector3d dir = b - a;
	vector3d right = (w*0.5)*norm(cross(dir,up));
	vector3d al = a - right;
	vector3d ar = a + right;
	vector3d bl = b - right;
	vector3d br = b + right;
	vector3d v;
	
	// cap a
	if (cap_a) {
		glBegin(GL_TRIANGLE_STRIP);
		glTexCoord2f(0,0);glVertex3fv(&al.x);
		glTexCoord2f(1,0);glVertex3fv(&ar.x);
		v = al + up;glTexCoord2f(0,1);glVertex3fv(&v.x);
		v = ar + up;glTexCoord2f(1,1);glVertex3fv(&v.x);
		glEnd();
	}
	if (cap_b) {
		glBegin(GL_TRIANGLE_STRIP);
		v = bl + up;glTexCoord2f(0,1);glVertex3fv(&v.x);
		v = br + up;glTexCoord2f(1,1);glVertex3fv(&v.x);
		glTexCoord2f(0,0);glVertex3fv(&bl.x);
		glTexCoord2f(1,0);glVertex3fv(&br.x);
		glEnd();
	}
	
	glBegin(GL_TRIANGLE_STRIP);
	glTexCoord2f(1,0);glVertex3fv(&bl.x);
	glTexCoord2f(0,0);glVertex3fv(&al.x);
	v = bl + up;glTexCoord2f(1,1);glVertex3fv(&v.x);
	v = al + up;glTexCoord2f(0,1);glVertex3fv(&v.x);
	v = br + up;glTexCoord2f(1,0);glVertex3fv(&v.x);
	v = ar + up;glTexCoord2f(0,0);glVertex3fv(&v.x);
	glTexCoord2f(1,1);glVertex3fv(&br.x);
	glTexCoord2f(0,1);glVertex3fv(&ar.x);
	glEnd();
	
	
}

// ****** ****** ****** END