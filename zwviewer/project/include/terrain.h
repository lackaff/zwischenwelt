// ****** ****** ****** terrain.h
#ifndef _TERRAIN_H_
#define _TERRAIN_H_

#include <geometry.h>
#include <media.h>
#include <string>
#include <SDL/SDL.h>

// ****** ****** ****** terrainpatchraw

class cTerrainPatch;

class cTerrainPatchRaw {
public :
	enum {
		kALRes = 64,
		kTarget_Light,
		kTarget_BaseMat,
		kTarget_Alpha1,
		kTarget_Alpha2,
		kTarget_Alpha3,
		kTarget_Terrain,
		kTool_Set,
		kTool_Add,
		kTool_Modulate,
		kTool_Smooth,
		kTool_Level
	};
	float	light[kALRes*kALRes]; // color ?
	float	alpha[3][kALRes*kALRes];
	cTerrainPatch*	pTer;
	std::string	pMaterialPath[4];

	// constructors
	cTerrainPatchRaw(cTerrainPatch* pTer);
	cTerrainPatchRaw(cTerrainPatch* pTer,const char* szPath);
	cTerrainPatchRaw(cTerrainPatch* pTer,SDL_Surface* pSurface,int iX=0,int iY=0);
	~cTerrainPatchRaw();

	// returns kTarget_Alpha1-3, kTarget_BaseMat for base, or 0 on failure, if not found,inits new alphamap and ter
	int		AddMaterial	(const char* path);
	// shifts mats if neccessary, removes
	void	MaterialToTop	(int i);
	int		GetMaterial	(const char* path);
	void	DelMaterial	(const char* path);
	void	DelMaterial	(int i);

	// file
	void	Load	(const char* szPath);
	void	Save	(const char* szPath);

	// constant init
	void	ClearH	(float h=0.0);
	void	ClearAL	();
	void	ClearA	(int m,float h=0.0);

	// eliminate unused materials
	void	Optimize	();

	// tools
	void	Tool		(int iTarget,int iTool,float fVal,int iX,int iY,float fParam=0.0); // fParam : kTool_Level->targetlevel
	bool	CircleHit	(int iTarget,float fRad,float fX,float fY);
	void	Circle		(int iTarget,int iTool,float fStrength,float fRad,float fFalloff,float fX,float fY); // pow(dist,fFalloff);
	// iCX,iCY = 0 -> full image size
	void	Decal	(int iTarget,int iTool,float fMin,float fMax,int sx,int sy,
			SDL_Surface* pSurface,int iChan,int iX=0,int iY=0,int iCX=0,int iCY=0);

	// height manipulation
	void	HeightMap	(SDL_Surface* pSurface,int iX=0,int iY=0,float fMin=0.0,float fMax=16.0);
	void	AlphaMap	(int iMat,SDL_Surface* pSurface,int iX=0,int iY=0);
	void	LightMap	(SDL_Surface* pSurface,int iX=0,int iY=0);

	float	GetNeighborH		(int x,int y);
	void	GenLight			(vector3d dir,float famb = 0.4);
	// TODO : implement shadow function -> trace ray for object and terrain hit
	void	RenderAlphaLight	();
};


// ****** ****** ****** terrainpatch


class cTerrainPatch {
public :
	float	pData[17*17+17*17+9*9+5*5+3*3];
	vector3d	pNorm[16*16*2];
	float	fMinH,fMaxH,fMidH,fRad;
	float*	pInterpol[5];
	float	fMaxError[5];
	vector3d	vPos;
	int		iLevel;
	float	fTween;
	cTerrainPatch*		pL; // -x
	cTerrainPatch*		pR; // +x
	cTerrainPatch*		pT; // +y
	cTerrainPatch*		pB; // -y
	int					iMatCount;
	cM_Texture*			pMaterial[4];
	uint				pAlphaLight[3]; // even for only one material, one alphalight must be set !
	cTerrainPatchRaw*	pRaw;
	static int			pLevelWidth[5];
	static vector3duv*	pVertexBuffer[5];
	static int*			pVertexIndices[5];
	static int			pVertexBufferLen[5];
	static int			pVertexIndicesLen[5];
	static vector3duv	pAllVertexBuffers[17*17+9*9+5*5+3*3+2*2];
	static int			pAllVertexIndices[16*(17*2+2)-2 + 8*(9*2+2)-2 + 4*(5*2+2)-2 + 2*(3*2+2)-2 + 1*(2*2+2)-2];
	static bool			bVertexBuffersInited;

	// scans 17*17 pixels !!!
	cTerrainPatch	(vector3d vPos,SDL_Surface* pSurface,int iX,int iY);
	~cTerrainPatch	();

	void	Recalc	();
	void	HClip	(vector3d &vCamPos,float delta);
	void	CalcLOD	(vector3d &vCamPos);
	float	GetH	(float x,float y);
	float	GetH	(vector3d &v);
	float	GetNH	(int x,int y);
	void	Draw	();
	bool	RayIntersect	(vector3d o,vector3d dir,float &res,int &foundx,int &foundy);

	static void InitVertexBuffers ();
	static void	PrepareDraw		();
	static void	CleanupDraw		();
};

#endif
// ****** ****** ****** END
