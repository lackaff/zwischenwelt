// ****** ****** ****** heightfield.h
#ifndef _HEIGHTFIELD_H_
#define _HEIGHTFIELD_H_

class cM_Texture;
class cHeightField {
public :
	float*	pHeight;
	int		iCX;
	int		iCY;
	cM_Texture*	tex;

	cHeightField	(float fMax,float fMax2,float fMax3,int iCX,int iCY,cM_Texture* tex);
	~cHeightField	();
	void	Draw	();
	void	DrawTile	(int i,int j);
	bool	CollisionCheck	(vector p,vector v,float fRad,float& fraction,bool debug);
	bool	IntersectCheck	(vector p,float fRad,bool debug,vector v);
	bool	IntersectCheck	(vector p,float fRad,int x,int y,vector v);
	vector	GetNormal		(vector p);
};

#endif
// ****** ****** ****** ENDs