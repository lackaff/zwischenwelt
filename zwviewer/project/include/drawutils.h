// ****** ****** ****** drawutils.cpp
#ifndef _DRAWUTILS_H_
#define _DRAWUTILS_H_

#include <geometry.h>
class cM_Texture;
	
void DrawCylinder		(vector3d p1,vector3d p2,vector3d col,float fRad);
void DrawCylinderCap	(vector3d p1,vector3d p2,vector3d col,float fRad);
void DrawSphere			(vector3d p,vector3d col,float fRad);
void DrawRect			(vector3d p,vector3d w,vector3d h,vector3d col);
void DrawBox			(vector3d lefttop,vector3d botright,vector3d col);
void DrawBox			(vector3d lefttop,vector3d botright);
void DrawBillBoard		(const vector3d& pos,float w,float h,const vector3d& col);
void DrawBillBoard		(const vector3d& pos,float w,float h);
void DrawWall			(const vector3d& a,const vector3d& b,float w,float h,bool cap_a,bool cap_b,cM_Texture* tex);

#endif
// ****** ****** ****** ENDIF