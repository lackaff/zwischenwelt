// ****** ****** ****** sky.cpp

#include <os.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library

#include <drawutils.h>
#include <geometry.h>
#include <media.h>
#include <sky.h>
#include <math.h>
#include <shell.h>
#include <robstring.h>
#include <stdlib.h>
#include <input.h>
#include <stdio.h>

extern vector3d	gvCampos;


cString			skytexlist;
int				skytexcount = 0;
int				skycurtex = 0;

// ****** ****** ****** cSky


cSky*	cSky::pSingleton = 0;
cSky*	cSky::instance ()
{
	if (!pSingleton) pSingleton = new cSky();
	return pSingleton;
}



cSky::cSky	() {
	char* str = gShell.ListFiles("data/sky");
	skytexlist = str;
	if (str) free(str);
	skytexcount = skytexlist.CountLines();
	
	pSkyTex = cTextureManager::getinstance()->get("data/sky/sky3.jpg");
	pSkyTex->lock("sky",0);
}

cSky::~cSky	() {
	pSkyTex->unlock("sky",0);
}

void	cSky::Draw	() {	
	static bool bpressed = false;
	if (!gInput.bKeys[cInput::kkey_6]) bpressed = false;
	else if (!bpressed)
	{
		bpressed = 1;
		skycurtex++;
		
		pSkyTex->unlock("sky",0);
		cString mytex = skytexlist.GetLine(skycurtex%skytexcount);
		pSkyTex = cTextureManager::getinstance()->get(*cString("data/sky/%s",*mytex));
		if (pSkyTex)
		pSkyTex->lock("sky",0);
	}

	glDisable(GL_LIGHTING);
	glEnable(GL_TEXTURE_2D);
	glDisable(GL_DEPTH_TEST);
	glDepthMask(0);

	glColor3f(1,1,1);
	if (pSkyTex) pSkyTex->Bind();

	float fTimeLoop = 80000.0;
	float fTime = (gShell.iClock % (int)fTimeLoop) / fTimeLoop;
	float fCycle = cos(fTime * 2.0 * kPi) * 0.5 + 0.5;
	float fRad = 10.0;

	fCycle = 0;



	glPushMatrix();
	glTranslatef(gvCampos.x,gvCampos.y,gvCampos.z);
	
		glMatrixMode(GL_TEXTURE);
		glPushMatrix();
		glLoadIdentity();

		float fScale = 1.0 - 0.5 * fCycle;
		fScale *= 0.5;
		glTranslatef(0.5,0.5,0.5);
		glTranslatef(fTime,0,0);
		glScalef(fScale,fScale,fScale);
	
		float stepa = kPi*0.05;
		float stepb = kPi*0.1;
		float starta = kPi*0.2;
		float scalez = 1.1;
		float startz = fRad * sin(starta) * scalez;
		starta = - kPi*0.4;

		for (float a=starta;a<kPi*0.5;a+=stepa)
		{
			float z = fRad * sin(a) * scalez - startz;
			float subrad = cos(a);
			float z2 = fRad * sin(a+stepa) * scalez - startz;
			float subrad2 = cos(a+stepa);
			glBegin(GL_TRIANGLE_STRIP);
			//glBegin(GL_LINE_STRIP);
			for (float b=0.0;b<kPi*2.0+stepb;b+=stepb)
			{
				float x = sin(b);
				float y = cos(b);
				glTexCoord2f(subrad2*x,subrad2*y);
				glVertex3f(fRad * subrad2*x,fRad * subrad2*y,z2);
				glTexCoord2f(subrad*x,subrad*y);
				glVertex3f(fRad * subrad*x,fRad * subrad*y,z);
			}
			glEnd();
		}



		glPopMatrix();
		glMatrixMode(GL_MODELVIEW);

	glPopMatrix();

	glDepthMask(1);
	glEnable(GL_DEPTH_TEST);
	glDisable(GL_TEXTURE_2D);
	glEnable(GL_LIGHTING);
}

// ****** ****** ****** END