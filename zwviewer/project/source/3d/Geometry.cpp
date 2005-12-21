// ****** ****** ****** Geometry.cpp
#include <geometry.h>
#include <math.h>
#include <string.h>
#include <color.h>

/*
#define SIN_MULT		200.0
#define SIN_RANGE		(int)(kpi*SIN_MULT*1.25+100)
#define SIN_COS_DIFF	(kpi*SIN_MULT*0.25)
float	gCOS[SIN_RANGE];


#define	COS(a)	(gCOS[(int)((a)*SIN_MULT)])
#define	SIN(a)	(gCOS[(int)(SIN_COS_DIFF + (a)*SIN_MULT)])
*/


// ****** ****** ****** ****** ******

// ******        vector3d        ******

// ****** ****** ****** ****** ******




// ****** ****** ****** Constructors



vector3duv::vector3duv	(const float x,const float y,const float z,const float u,const float v) 
 : vector3d(x,y,z),u(u),v(v) {}

vector3duv::vector3duv	() {}


vector3d::vector3d	(const float x,const float y,const float z)
 : x(x),y(y),z(z) {}

vector3d::vector3d	(const float f)
 : x(f),y(f),z(f) {}

vector3d::vector3d	(const float *f) {
	memcpy(&x,f,sizeof(float)*3);
}

vector3d::vector3d	() {}





float&	vector3d::operator []		(const int i) {
	return	(&x)[i];
}






// ****** ****** ****** assignment






vector3d&	vector3d::operator +=		(const vector3d& v)
{
	x += v.x;	y += v.y;	z += v.z;
	return *this;
}

vector3d&	vector3d::operator -=		(const vector3d& v)
{
	x -= v.x;	y -= v.y;	z -= v.z;
	return *this;
}

vector3d&	vector3d::operator *=		(const vector3d& v)
{
	x *= v.x;	y *= v.y;	z *= v.z;
	return *this;
}

vector3d&	vector3d::operator /=		(const vector3d& v)
{
	x /= v.x;	y /= v.y;	z /= v.z;
	return *this;
}

vector3d&	vector3d::operator *=		(const float f)
{
	x *= f;	y *= f;	z *= f;
	return *this;
}

vector3d&	vector3d::operator /=		(const float f)
{
	x /= f;	y /= f;	z /= f;
	return *this;
}





// ****** ****** ****** operators







vector3d	operator +		(const vector3d& v)
{
	return v;
}

vector3d	operator -		(const vector3d& v)
{
	return vector3d( -v.x , -v.y , -v.z );
}







vector3d	operator +		(const vector3d& v1,const vector3d& v2)
{
	return vector3d( v1.x + v2.x , v1.y + v2.y , v1.z + v2.z );
}

vector3d	operator -		(const vector3d& v1,const vector3d& v2)
{
	return vector3d( v1.x - v2.x , v1.y - v2.y , v1.z - v2.z );
}

vector3d	operator *		(const vector3d& v1,const vector3d& v2)
{
	return vector3d( v1.x * v2.x , v1.y * v2.y , v1.z * v2.z );
}

vector3d	operator /		(const vector3d& v1,const vector3d& v2)
{
	return vector3d( v1.x / v2.x , v1.y / v2.y , v1.z / v2.z );
}







vector3d	operator *		(const vector3d& v,const float f)
{
	return vector3d( v.x * f , v.y * f , v.z * f );
}

vector3d	operator *		(const float f,const vector3d& v)
{
	return vector3d( v.x * f , v.y * f , v.z * f );
}

vector3d	operator /		(const vector3d& v,const float f)
{
	return vector3d( v.x / f , v.y / f , v.z / f );
}





// ****** ****** ****** functions





float	sqmag			(const vector3d& v)
{
	return v.x * v.x  +  v.y * v.y  +  v.z * v.z;
}

float	mag				(const vector3d& v)
{
	return sqrt( sqmag(v) );
}

vector3d	norm			(const vector3d& v)
{
	float m = mag(v);
	if (m > 0.0) return v / m;
	else return vector3d(0.0);
}





float	dot				(const vector3d& v1,const vector3d& v2)
{
	return v1.x * v2.x  +  v1.y * v2.y  +  v1.z * v2.z;
}





vector3d	cross			(const vector3d& v1,const vector3d& v2)
{
	vector3d	res;

	res.x = v1.y * v2.z  -  v1.z * v2.y;
	res.y = v1.z * v2.x  -  v1.x * v2.z;
	res.z = v1.x * v2.y  -  v1.y * v2.x;
	return res;
}





vector3d	innermatrixmult		(const vector3d& v,const float *mat)
{
	vector3d w;
	w.x = v.x*mat[ 0] + v.y*mat[ 4] + v.z*mat[ 8];
	w.y = v.x*mat[ 1] + v.y*mat[ 5] + v.z*mat[ 9];
	w.z = v.x*mat[ 2] + v.y*mat[ 6] + v.z*mat[10];
	return w;
}





vector3d	inversematrixmult	(const vector3d& v,const float *mat)
{
	vector3d w;
	w.x = v.x*mat[ 0] + v.y*mat[ 1] + v.z*mat[ 2];
	w.y = v.x*mat[ 4] + v.y*mat[ 5] + v.z*mat[ 6];
	w.z = v.x*mat[ 8] + v.y*mat[ 9] + v.z*mat[10];
	return w;
}	







// ****** ****** ****** ****** ******

// ******      quaternion      ******

// ****** ****** ****** ****** ******







// ****** ****** ****** Construktors





quaternion::quaternion		(const float w,const float x,const float y,const float z)
 : w(w),x(x),y(y),z(z) {}


quaternion::quaternion		(float ang,vector3d axis)
{
	ang *= 0.5;
	axis = norm(axis);
	float sinang = sin(ang);
	w = cos(ang);
	x = axis.x*sinang;
	y = axis.y*sinang;
	z = axis.z*sinang;
}


quaternion::quaternion		(const float* f)
{
	memcpy(&w,f,sizeof(float)*4);
}


quaternion::quaternion		(const float f)
 : w(1.0),x(f),y(f),z(f) {}

quaternion::quaternion		() {}



float&	quaternion::operator []		(const int i)
{
	return	(&w)[i];
}



// ****** ****** ****** operators






quaternion	operator +		(const quaternion& q1,const quaternion& q2)
{
	return quaternion( q1.w + q2.w , q1.x + q2.x , q1.y + q2.y , q1.z + q2.z );
}


quaternion	operator -		(const quaternion& q1,const quaternion& q2)
{
	return quaternion( q1.w - q2.w , q1.x - q2.x , q1.y - q2.y , q1.z - q2.z );
}


quaternion	operator *		(const quaternion& q,const float f)
{
	return quaternion( q.w * f , q.x * f , q.y * f , q.z * f );
}


quaternion	operator /		(const quaternion& q,const float f)
{
	return quaternion( q.w / f , q.x / f , q.y / f , q.z / f );
}



quaternion	operator *		(const quaternion& q1,const quaternion& q2)
{
	quaternion	res;
	res.w = q1.w * q2.w  -  q1.x * q2.x  -  q1.y * q2.y  -  q1.z * q2.z;
	res.x = q1.w * q2.x  +  q1.x * q2.w  +  q1.y * q2.z  -  q1.z * q2.y;
	res.y = q1.w * q2.y  +  q1.y * q2.w  +  q1.z * q2.x  -  q1.x * q2.z;
	res.z = q1.w * q2.z  +  q1.z * q2.w  +  q1.x * q2.y  -  q1.y * q2.x;
	return res;
}



// conjugate  q*
// IS THIS CALCULATION NECCESSARY ???
quaternion	operator *		(const quaternion& q)
{
	return quaternion( q.w , -q.x , -q.y , -q.z );
}



// multiplicative inverse  q^-1
// IS THIS CALCULATION NECCESSARY ???
quaternion	operator ~		(const quaternion& q)
{
	return *q / sqmag(q);
}





// ****** ****** ****** functions





float		sqmag			(const quaternion& q)
{
	return q.w * q.w  +  q.x * q.x  +  q.y * q.y  +  q.z * q.z;
}


float		mag				(const quaternion& q)
{
	return sqrt( sqmag(q) );
}


float			dot				(const quaternion& q1,const quaternion& q2)
{
	return q1.w * q2.w  +  q1.x * q2.x  +  q1.y * q2.y  +  q1.z * q2.z;
}


quaternion		norm			(const quaternion& q)
{
	float m = mag(q);
	if (m > 0.0) return q / m;
	else return quaternion(0.0);
}


quaternion		power			(quaternion& q,const float f)
{
	float fi = f*acos(q.w);
	float oldsin = sqrt(1 - q.w*q.w);
	float multip = sin(fi) / oldsin;
	return quaternion( cos(fi) , q.x * multip , q.y * multip , q.z * multip );
}


quaternion		slerp			(const float t,const quaternion& q1,const quaternion& q2)
{
	if (t <= 0.0) return q1;
	if (t >= 1.0) return q2;
	double cosfi = dot(q1,q2);
	if (cosfi >= 0.999) return q1; // equal quaternions
	double fi = acos(cosfi);
	double rp_sinfi = 1.0 / sqrt(1.0 - cosfi*cosfi);

	double m1 = sin((1.0 - t)*fi);
	double m2 = sin(t*fi);

	return ( q1 * m1  +  q2 * m2 ) * rp_sinfi;
}


void	quaternion::getmatrix	(float *mat)
{
	static float xx,yy,zz,xy,xz,yz,wx,wy,wz;
	xx = x*x;xx += xx;
	yy = y*y;yy += yy;
	zz = z*z;zz += zz;
	xy = x*y;xy += xy;
	xz = x*z;xz += xz;
	yz = y*z;yz += yz;
	wx = w*x;wx += wx;
	wy = w*y;wy += wy;
	wz = w*z;wz += wz;

	mat[ 0] = 1.0 - yy - zz;
	mat[ 1] = xy + wz;
	mat[ 2] = xz - wy;
	mat[ 3] = 0.0;

	mat[ 4] = xy - wz;
	mat[ 5] = 1.0 - xx - zz;
	mat[ 6] = yz + wx;
	mat[ 7] = 0.0;

	mat[ 8] = xz + wy;
	mat[ 9] = yz - wx;
	mat[10] = 1.0 - xx - yy;
	mat[11] = 0.0;

	mat[12] = 0.0;
	mat[13] = 0.0;
	mat[14] = 0.0;
	mat[15] = 1.0;
}


void	quaternion::getinnermatrix	(float *mat)
{
	static float xx,yy,zz,xy,xz,yz,wx,wy,wz;
	xx = x*x;xx += xx;
	yy = y*y;yy += yy;
	zz = z*z;zz += zz;
	xy = x*y;xy += xy;
	xz = x*z;xz += xz;
	yz = y*z;yz += yz;
	wx = w*x;wx += wx;
	wy = w*y;wy += wy;
	wz = w*z;wz += wz;

	mat[ 0] = 1.0 - yy - zz;
	mat[ 1] = xy + wz;
	mat[ 2] = xz - wy;

	mat[ 4] = xy - wz;
	mat[ 5] = 1.0 - xx - zz;
	mat[ 6] = yz + wx;

	mat[ 8] = xz + wy;
	mat[ 9] = yz - wx;
	mat[10] = 1.0 - xx - yy;
}


vector3d	quaternion::getaxis			()
{
	float warg = 1 - w*w;
	if (warg > 0.0)
			return vector3d(x,y,z) / sqrt(warg);
	else if (x != 0.0 || y != 0.0 || z != 0.0)
			return vector3d(x,y,z);
	else	return vector3d(0,0,1);
}









// ****** ****** ****** ****** ******

// ******      Functions       ******

// ****** ****** ****** ****** ******


// SmoothInterpolate 
// between p1,p2 with t in [0,1]
vector3d SmoothInterpolate (vector3d q1,vector3d p1,vector3d p2,vector3d q2,float t)
{
	if (t <= 0.0) return p1;
	if (t >= 1.0) return p2;
	//Q(t) = P1*(2t^3-3t^2+1) + R1*(t^3-2t^2+t) + P2*(-2t^3+3t^2) + R2*(t^3-t^2)
	//Q(0) = P1*(1) + R1*(0) + P2*(0) + R2*(0)
	//Q(1) = P1*(0) + R1*(0) + P2*(1) + R2*(0)
	// hermit spline oder so
	// R1,R2 are tangent-vector3ds at P1,P2

	vector3d r1 = (p2 - q1) * 0.5;
	vector3d r2 = (q2 - p1) * 0.5;
	float t2 = t * t;
	float t3 = t2 * t;

	return p1*(2.0*t3-3.0*t2+1.0) + r1*(t3-2.0*t2+t) + p2*(-2.0*t3+3.0*t2) + r2*(t3-t2);
}



// PointInRect
// desc : Checks if a point is inside a rectangular area
//			the point must be in the plane of the rectangle
// params :		
// - ap			vector3d from rect edge a to the point p to check
// - ax			vector3d of the width of the rectangle
// - ay			vector3d of the height of the rectangle
// return value :
// true if inside
bool	PointInRect		(vector3d ap,vector3d ax,vector3d ay)
{
	// ax and ay MUST BE ORTHAGONAL !!!!
	double x = dot(ax,ap)/sqmag(ax);
	if (x < 0.0 || x > 1.0) return 0;
	double y = dot(ay,ap)/sqmag(ay);
	if (y < 0.0 || y > 1.0) return 0;
	return 1;
}





// PointInTri
// desc : Checks if a point is inside a triangle
//			the point must be in the plane of the triangle
// params :		
// - a,b,c		triangle edges
// - p			point to check
// return value :
// true if inside
bool	PointInTri		(vector3d a,vector3d b,vector3d c,vector3d p)
{
	// noch ne idee : 
	// winkel von A zu BC und zu P  und dann noch von nem anderen Punkt

	// noch ne idee : 
	// winkelsumme von P an ABC = 360 ? rundungsfehler

	// noch ne idee : 
	// planarcoordinaten x und y ermitteln : wenn x + y > 1 außerhalb des dreiecks



	vector3d ab = b - a;
	vector3d ac = c - a;
	double sqab = sqmag(ab);

	// a links, b rechts, c oben

	// l ist lot von c auf ab
	// e ist teilstreckenlänge (ac / ab)
	// l kann auch außerhalb von ab liegen !
	double e = dot(ab,ac) / sqab;
	vector3d l = a + e * ab;

	vector3d	lp = p - l;
	vector3d	lc = c - l;

	double	y = dot(lc,lp) / sqmag(lc);

	// über oder unter dem dreieck, unabhängig von 0 < e < 1
	if (y < 0.0 || y > 1.0)
		return 0;

	// lot von p auf ab , x von L aus gesehen
	double	x = dot(ab,lp) / sqab;

	// jetzt je nach art des dreieck : L in ab ?
	if (e < 0.0)
	{
		// L links von AB
		if (x < 0.0 || x + e > 1.0)				return 0;
		if (e != 0.0 && y - x / e < 1.0)		return 0;
		if (e != 1.0 && y + x/(1.0 - e) > 1.0)	return 0;
	} else if (e > 1.0)
	{
		// L rechts von AB
		if (x + e < 0.0 || x > 0.0)				return 0;
		if (e != 0.0 && y - x / e > 1.0)		return 0;
		if (e != 1.0 && y + x/(1.0 - e) < 1.0)	return 0;
	} else {
		// L in AB
		if (x + e < 0.0 || x + e > 1.0)			return 0;
		if (e != 0.0 && y - x / e > 1.0)		return 0;
		if (e != 1.0 && y + x/(1.0 - e) > 1.0)	return 0;
	}

	return 1;
}





// closestPointOnTriangle
// desc : Find the closest point on the triangle boarder to the point p
// params :		
// - a,b,c		triangle edges
// - p			point
// return value :
// point on boarder
vector3d	closestPointOnTriangle	(vector3d a,vector3d b,vector3d c,vector3d p)
{
	vector3d pab = closestPointOnLine(a,b,p);
	vector3d pbc = closestPointOnLine(b,c,p);
	vector3d pca = closestPointOnLine(c,a,p);

	double d1 = sqmag(pab-p);
	double d2 = sqmag(pbc-p);
	double d3 = sqmag(pca-p);

	if (d1 <= d2)
		if (d1 <= d3)	return pab;
		else			return pca;
	else
		if (d2 <= d3)	return pbc;
		else			return pca;
}





// closestPointOnLine
// desc : Find the closest point on the line to the point p
// params :		
// - a,b		line edges
// - p			point
// return value :
// point on line
vector3d	closestPointOnLine		(vector3d a,vector3d b,vector3d p)
{
	vector3d c = p - a;
	vector3d v = b - a;
	float d = sqmag(v);
	float t = dot(v,c)/d;

	if (t <= 0.0) return a;
	if (t >= 1.0) return b;

	return a + t*v;
}




vector3d	closestPointOnQuad		(const vector3d vO,const vector3d vX,const vector3d vY,const vector3d vP)
{
	static	vector3d p[4];
	double	d[4];
	int		min = 0;

	p[0] = closestPointOnLine(vO,vO+vX,vP);
	p[1] = closestPointOnLine(vO+vX,vO+vX+vY,vP);
	p[2] = closestPointOnLine(vO+vX+vY,vO+vY,vP);
	p[3] = closestPointOnLine(vO+vY,vO,vP);
	
	d[0] = sqmag(p[0]-vP);
	d[1] = sqmag(p[1]-vP);
	d[2] = sqmag(p[2]-vP);
	d[3] = sqmag(p[3]-vP);

	if (d[1] <= d[min])	min = 1;
	if (d[2] <= d[min])	min = 2;
	if (d[3] <= d[min])	min = 3;

	return p[min];
}





// intersectPlaneRay
// desc : Find out where a ray intersects a plane
// params :		
// - pO			Plane Origin / any point on plane
// - pN			Plane Normal vector3d (NORMALIZED !!!!!)
// - rO			Ray Origin
// - rV			Ray Direction vector3d
// return value :
// hit fraction of point on ray :
// 0.0 : rO + 0.0*rV  =  rO
// 1.0 : rO + 1.0*rV  =  rO + rV
// inside line from rO to (rO + rV) if inside [0.0;1.0]
double	intersectPlaneRay		(vector3d pO,vector3d pN,vector3d rO,vector3d rV)
{
	double numer = dot(pN,rO - pO);
	double denom = dot(pN,rV);
	return - numer / denom;
}






// intersectSphereRay
// desc : Find out where a ray intersects a sphere
// params :		
// - sO			Sphere Origin / Center
// - sR			Sphere Radius
// - rO			Ray Origin
// - rV			Ray Direction vector3d
// return value :
// hit fraction of point on ray :
// 0.0 : rO + 0.0*rV  =  rO
// 1.0 : rO + 1.0*rV  =  rO + rV
// inside line from rO to (rO + rV) if inside [0.0;1.0]
// if -1.0 (or anything smaller zero ???)  SPHERE WAS NOT HIT
double	intersectSphereRay		(vector3d sO,double sR,vector3d rO,vector3d rV)
{
	vector3d q = sO-rO;// sphere origin to ray origin
	double c = sqmag(q);// distance of sphere origin to ray origin
	double v = dot(q,rV); //1.0 * q * cos(< q,rV)   =  (an / hyp) * hyp ( = q)
	// h² + v² = q²
	// -> h² = q² - v²
	// d² + h² = r²
	// -> d² = r² - h²
	// -> d² = r² - q² + v²
	double d = sR*sR - c + v*v;

	if (d < 0.0)
		return -800000.0;
	return v - sqrt(d);
}




// intersectEllipseRay
// desc : Find out where a ray intersects an Ellipse
// params :		
// - sO			Ellipse Origin / Center
// - sR			Ellipse Radius vector3d
// - rO			Ray Origin
// - rV			Ray Direction vector3d
// return value :
// hit fraction of point on ray :
// 0.0 : rO + 0.0*rV  =  rO
// 1.0 : rO + 1.0*rV  =  rO + rV
// inside line from rO to (rO + rV) if inside [0.0;1.0]
// if -1.0 (or anything smaller zero)  ELLIPSE WAS NOT HIT
double	intersectEllipseRay		(vector3d sO,vector3d sR,vector3d rO,vector3d rV)
{
	// scale space by invert radius -> make ellipsoid a sphere

	vector3d vV = rV/sR;
	double len = mag(vV);
	vV /= len;
	vector3d q = (sO-rO)/sR;
	double c = mag(q);
	double v = dot(q,vV);
	double d = 1.0 - c*c + v*v;

	if (d < 0.0)
		return -1.0;

	return (v - sqrt(d)) * mag(rV) / len;
}





// distancePlane
// desc : Find the distance from a point to a plane
// params :		
// - pO			Plane Origin / any point on plane
// - pN			Plane Normal vector3d (NORMALIZED !!!!!)
// - p			Point
// return value :
// distance
// 0.0 => point is on plane,
// less than zero,  on the side NOT containing normal vector3d
// greater than zero,  on the side CONTAINING normal vector3d
double	distancePlane			(vector3d pO,vector3d pN,vector3d p)
{
	return dot(pN,p - pO);
}





// DistPointRay
// desc : Find the distance from a point to a ray
// params :		
// - rO			Ray Origin
// - rV			Ray Direction vector3d
// - p			point
// return value :
// distance
// 0.0 => point is on line,
// return value always greater zero
double	distanceRay				(vector3d rO,vector3d rV,vector3d p)
{
	vector3d w = p - rO;
	vector3d h = dot(rV,w)*rV / sqmag(rV) - w;  // lot P auf BC
	return mag(h);
}





// planarProjection
// desc : Calculate the planar projection of a vector3d onto a plane with given normal
// params :		
// - vN			Plane normal
// - vV			vector3d to project onto plane
// return value : planar projection of vV
vector3d	planarProjection		(vector3d vN,vector3d vV)
{
	return vV - vN * dot(vN,vV);// distance of vector3d is eliminated
}



extern vector3d	gCamFrustum[6];
bool	SphereInFrustum		(const vector3d vPos,const float fRad) {
	return SphereInFrustum(vPos,fRad,gCamFrustum);
}

// SphereInFrustum
// desc : check if a sphere is withing the viewing frustum
// params :
//	- vPos	pos of the sphere
//	- fRad	radius of the sphere
//	- pFrustum	array of 5 vector3ds : 4 normals and the campoint
// return value :
//  - 0 not in viewing frustum -> not visible
//  - 1 inside viewing frustum -> visible
bool	SphereInFrustum		(const vector3d vPos,const float fRad,const vector3d* pFrustum) {
	vector3d v = vPos - pFrustum[5];
	if (dot(v,pFrustum[0]) > fRad) return 0;// back
	if (dot(v,pFrustum[3]) > fRad) return 0;// left
	if (dot(v,pFrustum[4]) > fRad) return 0;// right
	if (dot(v,pFrustum[1]) > fRad) return 0;// top
	if (dot(v,pFrustum[2]) > fRad) return 0;// bot
	return 1;
}






// ****** ****** ****** END