// ****** ****** ****** Geometry.h
#ifndef _GEOMETRY_H_
#define _GEOMETRY_H_


class vector3d {
public:
	float	x,y,z;

	vector3d	(const float x,const float y,const float z);
	vector3d	(const float* f);
	vector3d	(const float f);
	vector3d	();

	float&	operator []		(const int i);

	// assignment

	vector3d&	operator +=		(const vector3d& v);
	vector3d&	operator -=		(const vector3d& v);
	vector3d&	operator *=		(const vector3d& v);
	vector3d&	operator /=		(const vector3d& v);
	vector3d&	operator *=		(const float f);
	vector3d&	operator /=		(const float f);

	// global

	friend vector3d	operator +		(const vector3d& v);
	friend vector3d	operator -		(const vector3d& v);

	friend vector3d	operator +		(const vector3d& v1,const vector3d& v2);
	friend vector3d	operator -		(const vector3d& v1,const vector3d& v2);
	friend vector3d	operator *		(const vector3d& v1,const vector3d& v2);
	friend vector3d	operator /		(const vector3d& v1,const vector3d& v2);

	friend vector3d	operator *		(const vector3d& v,const float f);
	friend vector3d	operator *		(const float f,const vector3d& v);
	friend vector3d	operator /		(const vector3d& v,const float f);

	friend float	sqmag			(const vector3d& v);
	friend float	mag				(const vector3d& v);
	friend vector3d	norm			(const vector3d& v);

	friend float	dot				(const vector3d& v1,const vector3d& v2);
	friend vector3d	cross			(const vector3d& v1,const vector3d& v2);

	friend vector3d	innermatrixmult		(const vector3d& v,const float *mat);
	friend vector3d	inversematrixmult	(const vector3d& v,const float *mat);
};

class vector3duv : public vector3d { 
public:
	float u,v;
	vector3duv	(const float x,const float y,const float z,const float u,const float v);
	vector3duv	();
};

class quaternion {

public:
	float	w,x,y,z;

	quaternion	(const float w,const float x,const float y,const float z);
	quaternion	(float ang,vector3d axis);
	quaternion	(const float* f);
	quaternion	(const float f);
	quaternion	();

	float&	operator []		(const int i);
	void	getmatrix		(float *mat);

	void		getinnermatrix	(float *mat);
	vector3d	getaxis			();

	// global

	friend quaternion	operator +		(const quaternion& q1,const quaternion& q2);
	friend quaternion	operator -		(const quaternion& q1,const quaternion& q2);
	friend quaternion	operator *		(const quaternion& q,const float f);
	friend quaternion	operator /		(const quaternion& q,const float f);
	friend quaternion	operator *		(const quaternion& q1,const quaternion& q2);

	// conjugate  q*
	friend quaternion	operator *		(const quaternion& q);
	// multiplicative inverse  q^-1
	friend quaternion	operator ~		(const quaternion& q);


	friend float		sqmag			(const quaternion& q);
	friend float		mag				(const quaternion& q);
	friend float		dot				(const quaternion& q1,const quaternion& q2);
	friend quaternion	norm			(const quaternion& q);
	friend quaternion	power			(const quaternion& q,const float f);

	friend quaternion	slerp			(const float t,const quaternion& q1,const quaternion& q2);
};



// ****** ****** ****** Functions



vector3d	SmoothInterpolate		(vector3d q1,vector3d p1,vector3d p2,vector3d q2,float t);
bool		PointInRect				(vector3d ap,vector3d ax,vector3d ay);
bool		PointInTri				(vector3d a,vector3d b,vector3d c,vector3d p);
vector3d	closestPointOnTriangle	(vector3d a,vector3d b,vector3d c,vector3d p);
vector3d	closestPointOnLine		(vector3d a,vector3d b,vector3d p);
vector3d	closestPointOnQuad		(const vector3d vO,const vector3d vX,const vector3d vY,const vector3d vP);
double		intersectPlaneRay		(vector3d pO,vector3d pN,vector3d rO,vector3d rV);
double		intersectSphereRay		(vector3d sO,double sR,vector3d rO,vector3d rV);
double		intersectEllipseRay		(vector3d sO,vector3d sR,vector3d rO,vector3d rV);
double		distancePlane			(vector3d pO,vector3d pN,vector3d p);
double		distanceRay				(vector3d rO,vector3d rV,vector3d p);

bool		SphereInFrustum		(const vector3d vPos,const float fRad);
bool		SphereInFrustum		(const vector3d vPos,const float fRad,const vector3d* pFrustum);



#endif
// ****** ****** ****** END