// ****** ****** ****** TerrainTest.cpp
#include <terrain.h>
#include <geometry.h>
#include <shell.h>
#include <stdio.h>
#include <math.h>
#include <input.h>
#include <list.h>
#include <stdlib.h>
#include <media.h>
#include <robstring.h>
#include <color.h>
#include <assert.h>
#include <roblib.h>

#include <SDL/SDL.h>
#include <os.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>




 

// ****** ****** ****** terrainpatchraw


/*
	RAW terrain class, load & save .ter
	later : raw data released after terrain creation, if not changing
	editor keeps raw data
	method for reloading raw data -> store filename
	constructor : load from .ter file using raw data
	raw data : can init to zero without .ter file,
		can load image parts as height- light- and alphamaps
		generate lightmap using ray-intersect callback funtion,
		has light&alpha "paint" tools, 
		has light darkening & blood decal support, for burns & craters,
		generates and assigns alphalight opengl textures to terrainpatch
*/


// constructors
cTerrainPatchRaw::cTerrainPatchRaw(cTerrainPatch* pTer)
 : pTer(pTer) {
	ClearH();
	ClearAL();
}
cTerrainPatchRaw::cTerrainPatchRaw(cTerrainPatch* pTer,const char* szPath)
 : pTer(pTer) {
	Load(szPath);
}
cTerrainPatchRaw::cTerrainPatchRaw(cTerrainPatch* pTer,SDL_Surface* pSurface,int iX,int iY)
: pTer(pTer) {
	HeightMap(pSurface,iX,iY);
	ClearAL();
}
cTerrainPatchRaw::~cTerrainPatchRaw() {
}

// returns kTarget_Alpha1-3, kTarget_BaseMat for base, or 0 on failure, if not found,inits new alphamap and ter
int		cTerrainPatchRaw::AddMaterial	(const char* path) {
	Optimize();
	int i;
	for (i=0;i<pTer->iMatCount;i++)
		if (stricmp(pMaterialPath[i].c_str(),path) == 0) {
			MaterialToTop(i);
			return kTarget_BaseMat+pTer->iMatCount-1;
			//return kTarget_BaseMat+i;
		}
	if (pTer->iMatCount >= 4) return 0; // error, matspace full
	pTer->iMatCount++;
	pMaterialPath[pTer->iMatCount-1] = path;
	pTer->pMaterial[pTer->iMatCount-1] = cTextureManager::getinstance()->get(path);
	if (pTer->iMatCount >= 2) {
		// clear alphamaps
		int x,y,rowy;
		for (y=0;y<kALRes;y++) {
			rowy = y*kALRes;
			for (x=0;x<kALRes;x++)
				alpha[pTer->iMatCount-2][rowy+x] = 0.0; // TODO : DEBUG : RESET ME TO ZERO
		}
	}
	return kTarget_BaseMat+pTer->iMatCount-1;
}

// get the index of a material, or -1 if unused
// 0-3, 0 is ground
int		cTerrainPatchRaw::GetMaterial	(const char* path) {
	int i;
	for (i=0;i<pTer->iMatCount;i++)
	if (stricmp(pMaterialPath[i].c_str(),path) == 0) 
		return i;
	return -1;
}

// removes material, shifts mats and alphamaps if neccessary
// call RenderAlphaLight(); afterwards !
void	cTerrainPatchRaw::DelMaterial	(const char* path) {
	int i;
	for (i=0;i<pTer->iMatCount;i++)
	if (stricmp(pMaterialPath[i].c_str(),path) == 0) 
		DelMaterial(i);
	//RenderAlphaLight();
}

// 0-3, 0 is ground
// removes material, shifts mats and alphamaps if neccessary
// call RenderAlphaLight(); afterwards !
void	cTerrainPatchRaw::DelMaterial	(int i) {
	int j;
	if (pTer->iMatCount > 1+i) {
		// shift
		for (j=i+1;j<pTer->iMatCount;j++) {
			// shift mats
			pMaterialPath[j-1] = pMaterialPath[j];
			pTer->pMaterial[j-1] = pTer->pMaterial[j];
			if (j-2>=0) memcpy(alpha[j-2],alpha[j-1],kALRes*kALRes*sizeof(float));
		}
	}
	pTer->iMatCount--;
}

// 0-3, 0 is ground
// moves material to top of stack
// call RenderAlphaLight(); afterwards !
void	cTerrainPatchRaw::MaterialToTop	(int i) {
	static float	newalpha[kALRes*kALRes];
	int j,x,y,rowy;
	if (i >= pTer->iMatCount-1) return;
	if (i == 0) {
		// ground level -> no old alpha map
		for (y=0;y<kALRes;y++) {
			rowy = y*kALRes;
			for (x=0;x<kALRes;x++)
				newalpha[rowy+x] = 1.0;
		}
	} else memcpy(newalpha,alpha[i-1],sizeof(float)*kALRes*kALRes);
	
	// coverage by above materials
	for (y=0;y<kALRes;y++) {
		rowy = y*kALRes;
		for (x=0;x<kALRes;x++) {
			if (i >= pTer->iMatCount-2) {
				newalpha[rowy+x] *= 1.0 - alpha[i][rowy+x];
			} else {
				if (i == 0) {
					newalpha[rowy+x] *= 1.0 - alpha[0][rowy+x];
					if (pTer->iMatCount >= 4)
							newalpha[rowy+x] *= 1.0 - (alpha[1][rowy+x] + alpha[2][rowy+x]);
					else	newalpha[rowy+x] *= 1.0 - alpha[1][rowy+x];
				} else {
					newalpha[rowy+x] *= 1.0 - (alpha[1][rowy+x] + alpha[2][rowy+x]);
				}
			}
			// strenghten alpha below ?!?
			// alpha[i][rowy+x] = min(0.0,alpha[i][rowy+x]+newalpha[rowy+x]);
			// TODO : slight visible change, correct me, low prio

			/*
			for (j=i+1;j<pTer->iMatCount;j++) {
				//newalpha[rowy+x] -= alpha[j-1][rowy+x];
				newalpha[rowy+x] *= 1.0 - alpha[j-1][rowy+x];
			}*/
			//newalpha[rowy+x] = max(0.0,newalpha[rowy+x]);
		}
	}
	
	// remember old texture and name
	std::string matpath = pMaterialPath[i];
	cM_Texture* mat = pTer->pMaterial[i];
	// shift above materials down
	for (j=i+1;j<pTer->iMatCount;j++) {
		// shift mats
		pMaterialPath[j-1] = pMaterialPath[j];
		pTer->pMaterial[j-1] = pTer->pMaterial[j];
		if (j-2>=0) memcpy(alpha[j-2],alpha[j-1],kALRes*kALRes*sizeof(float));
	}
	// push me onto top with new alpha map
	i = pTer->iMatCount-1;
	pMaterialPath[i] = matpath;
	pTer->pMaterial[i] = mat;
	memcpy(alpha[i-1],newalpha,kALRes*kALRes*sizeof(float));
}


// eliminate unused materials
// call RenderAlphaLight(); afterwards !
void	cTerrainPatchRaw::Optimize	() {
	int m,n,x,y,rowy,score;
	float visibility;
	#define minscore 10

	for (m=pTer->iMatCount-1;m>=0;m--) {
		score = 0;
		for (y=0;y<kALRes;y++) {
			rowy = y*kALRes;
			for (x=0;x<kALRes;x++) {
				if (m==0)
						visibility = 1.0;
				else	visibility = alpha[m-1][rowy+x];
				for (n=m+1;n<pTer->iMatCount;n++)
					visibility *= (1.0 - alpha[n-1][rowy+x]);
				if (visibility >= 0.2) 
					if (++score >= minscore)
						break;
			}
			if (score >= minscore) break;
		}

		if (score < minscore)
			DelMaterial(m);
	}
	//RenderAlphaLight();
}






// file
/*
2* float : minh,maxh
1* int : matcount
___
matcount * string : matpath
16*16 * 1 : height
64*64 * [1,3] : lightmap
64*64 * [1,2,3] : alpha
*/
void	cTerrainPatchRaw::Load	(const char* szPath) {
	static char matpath[512];
	int len,i;
	FILE* fp = fopen(szPath,"rb");
	if (!fp) return;
	// flush materials
	for (i=pTer->iMatCount-1;i>=0;i--)
		DelMaterial(i);

	fread(&pTer->iMatCount,sizeof(int),1,fp);
	for (i=0;i<pTer->iMatCount;i++) {
		fread(&len,sizeof(int),1,fp);
		len = min(511,len);
		fread(matpath,sizeof(char),len,fp);
		matpath[len] = 0;
		AddMaterial(matpath);
	}
	fread(light,sizeof(float),kALRes*kALRes,fp);
	fread(alpha,sizeof(float),kALRes*kALRes*3,fp);
	fread(pTer->pData,sizeof(float),17*17,fp);
	fclose(fp);
	pTer->Recalc();
	RenderAlphaLight();
}

void	cTerrainPatchRaw::Save	(const char* szPath) {
	int len,i;
	FILE* fp = fopen(szPath,"wb");
	if (!fp) return;
	fwrite(&pTer->iMatCount,sizeof(int),1,fp);
	for (i=0;i<pTer->iMatCount;i++) {
		len = pMaterialPath[i].length();
		len = min(511,len);
		fwrite(&len,sizeof(int),1,fp);
		fwrite(pMaterialPath[i].c_str(),sizeof(char),len,fp);
	}
	fwrite(light,sizeof(float),kALRes*kALRes,fp);
	fwrite(alpha,sizeof(float),kALRes*kALRes*3,fp);
	fwrite(pTer->pData,sizeof(float),17*17,fp);
	fclose(fp);
}

// constant init

// clear height
void	cTerrainPatchRaw::ClearH	(float h) {
	assert(pTer && "no terrain patch assigned");
	int	x,y;
	for (y=0;y<17;++y)
	for (x=0;x<17;++x)
		pTer->pData[17*y+x] = h;
	pTer->Recalc();
}

// clear alpha and light
void	cTerrainPatchRaw::ClearAL	() {
	int	x,y,m;
	for (y=0;y<kALRes;++y)
	for (x=0;x<kALRes;++x) {
		light[kALRes*y+x] = 1.0;
		for (m=0;m<3;++m)
			alpha[m][kALRes*y+x] = 0.0;
	}
	RenderAlphaLight();
}
// call RenderAlphaLight(); afterwards !!
void	cTerrainPatchRaw::ClearA	(int m,float h) {
	// RenderAlphaLight();
	if (m < 0 || m >= 3) return;
	int	x,y;
	for (y=0;y<kALRes;++y)
	for (x=0;x<kALRes;++x) {
		alpha[m][kALRes*y+x] = 0.0;
	}
}



// apply a tool to a specific target ( alpha1-3, light or terrain )
void	cTerrainPatchRaw::Tool	(int iTarget,int iTool,float fVal,int x,int y,float fParam) {
	if (x < 0 || y < 0) return;	

	// kTarget_BaseMat means clear all alphas
	if (iTarget == kTarget_BaseMat) {
		switch (iTool) {
			case kTool_Add:		fVal = -fVal;	break;
			case kTool_Modulate:fVal = 1.0/fVal;break;
		}
		Tool(kTarget_Alpha1,iTool,fVal,x,y);
		Tool(kTarget_Alpha2,iTool,fVal,x,y);
		Tool(kTarget_Alpha3,iTool,fVal,x,y);
		return;
	}
	
	// get datapointer from target
	float* p;
	switch (iTarget) {
		case kTarget_Light: 
			if (x >= kALRes || y >= kALRes) return;
			p = &light[kALRes*y+x];
			break;
		case kTarget_Alpha1:
			if (x >= kALRes || y >= kALRes) return; 
			p = &alpha[0][kALRes*y+x];
			break;
		case kTarget_Alpha2:
			if (x >= kALRes || y >= kALRes) return;
			p = &alpha[1][kALRes*y+x];
			break;
		case kTarget_Alpha3:
			if (x >= kALRes || y >= kALRes) return;
			p = &alpha[2][kALRes*y+x];
			break;
		case kTarget_Terrain:
			if (x >= 17 || y >= 17) return;
			assert(pTer && "no terrain patch assigned");
			p = &pTer->pData[y*17+x];
			break;
	}

	// apply tool
	switch (iTool) {
		case kTool_Set:		*p = fVal;	break;
		case kTool_Add:		*p += fVal;	break;
		case kTool_Modulate:*p *= fVal;	break;
		case kTool_Smooth:
			if (iTarget == kTarget_Terrain)
				*p = (1.0-fVal) * *p + fVal * 0.25 * (
					pTer->GetNH(x+1,y) + pTer->GetNH(x-1,y) + 
					pTer->GetNH(x,y+1) + pTer->GetNH(x,y-1));
		break;
		case kTool_Level:	*p = fVal * fParam + (1.0-fVal) * (*p);	break;
	}
	// alpha and light are clamped
	if (iTarget != kTarget_Terrain)
		*p = min(1.0,max(0.0,*p));
}


bool	cTerrainPatchRaw::CircleHit	(int iTarget,float fRad,float fX,float fY) {
	assert(pTer && "no terrain patch assigned");
	int width;
	if (iTarget != kTarget_Terrain) {
		width = kALRes;
		fX *= 64.0/16.0;
		fY *= 64.0/16.0;
		fRad *= 64.0/16.0;
	} else {
		width = 17;
	}
	float r = fabs(fRad);
	int minx = floor(fX-r);
	int miny = floor(fY-r);
	int maxx = ceil(fX+r);
	int maxy = ceil(fY+r);
	if (maxx < 0 || maxy < 0 || minx >= width || miny >= width) return 0;
	// TODO : only true if really in circle, not only in square
	return 1;
}

// apply tool() on a circular area
void	cTerrainPatchRaw::Circle	(int iTarget,int iTool,
		float fStrength,float fRad,float fFalloff,float fX,float fY) {
	assert(pTer && "no terrain patch assigned");
	int width;
	if (iTarget != kTarget_Terrain) {
		width = kALRes;
		fX *= 64.0/16.0;
		fY *= 64.0/16.0;
		fRad *= 64.0/16.0;
	} else {
		width = 17;
	}
	float r = fabs(fRad);
	int minx = floor(fX-r);
	int miny = floor(fY-r);
	int maxx = ceil(fX+r);
	int maxy = ceil(fY+r);
	if (maxx < 0 || maxy < 0 || minx >= width || miny >= width) return;
	int x,y;
	if (minx < 0) minx = 0; 
	if (miny < 0) miny = 0;
	if (maxx >= width) maxx = width-1; 
	if (maxy >= width) maxy = width-1;

	// render circle
	for (y=miny;y<=maxy;++y)
	for (x=minx;x<=maxx;++x) {
		float dist = hypot(x-fX,y-fY)/r;
		if (fRad > 0.0) dist = 1.0 - dist;
		if (dist > 1.0) continue;
		if (dist < 0.0) continue;
		if (fFalloff != 1.0) dist = pow(dist,fFalloff);
		if (iTool == kTool_Level)
				Tool(iTarget,iTool,dist,x,y,fStrength);
		else	Tool(iTarget,iTool,fStrength * dist,x,y);
	}
	// TODO : falloff

	// recalc AL or heights
	//if (iTarget == kTarget_Terrain)
	//		pTer->Recalc();
	//else	RenderAlphaLight();
}

// applys tool() with the values of a surface
// [0,1] from the image means [fMin,fMax] as val to tool
// iChan is the color channel, 0=r,1=g,2=b,3=a
// sx,sy is the tool left,bottom coordinate, corresponds to left,bottom of the drawn area of surface
// iX,iY is the left,bottom of the drawn area of the surface ( watch out for y inverse !!! )
// iCX,iCY are the width,height of the drawn area of the surface
void	cTerrainPatchRaw::Decal	(int iTarget,int iTool,float fMin,float fMax,int sx,int sy,
								 SDL_Surface* pSurface,int iChan,int iX,int iY,int iCX,int iCY) {
	assert(pTer && "no terrain patch assigned");
	// calc size
	if (!iCX) iCX = pSurface->w;
	if (!iCY) iCY = pSurface->h;
	int iEndX = iX + iCX;
	int iEndY = iY + iCY;
	//if (iX < 0) iX = 0;
	//if (iY < 0) iY = 0;
	//if (iEndX > pSurface->w) iEndX = pSurface->w;
	//if (iEndY > pSurface->h) iEndY = pSurface->h;
	sx -= iX;
	sy -= iY;

	// render image
	//SDL_LockSurface(pSurface);
	float fDiff = (fMax-fMin) / (255.0);
	int x,y,rowy;
	uchar* base = (uchar*)pSurface->pixels;
	int bpp = pSurface->format->BytesPerPixel;
	for (y=iY;y<iEndY;++y) {
		rowy = iChan + pSurface->pitch * iclip(y,pSurface->h);
		for (x=iX;x<iEndX;++x) {
			Tool(iTarget,iTool,fMin + fDiff * base[rowy + iclip(x,pSurface->w) * bpp],sx+x,sy+y);
		}
	}
	//SDL_UnlockSurface(pSurface);

	// recalc AL or heights
	if (iTarget == kTarget_Terrain)
			pTer->Recalc();
	else	RenderAlphaLight();
}	

// height manipulation
void	cTerrainPatchRaw::HeightMap	(SDL_Surface* pSurface,int iX,int iY,float fMin,float fMax) {
	Decal(kTarget_Terrain,kTool_Set,fMin,fMax,0,0,pSurface,0,iX,iY,17,17);
}

// alpha manipulation
void	cTerrainPatchRaw::AlphaMap	(int iMat,SDL_Surface* pSurface,int iX,int iY) {
	Decal(kTarget_Alpha1+iMat,kTool_Set,0.0,1.0,0,0,pSurface,3,iX,iY,kALRes,kALRes);
}

// light manipulation
void	cTerrainPatchRaw::LightMap	(SDL_Surface* pSurface,int iX,int iY) {
	Decal(kTarget_Light,kTool_Set,0.0,1.0,0,0,pSurface,3,iX,iY,kALRes,kALRes);
}

// generate a diffuse light map, based on face normals (calculated)
void	cTerrainPatchRaw::GenLight			(vector3d dir,float famb) {
	float	fLight[17*17];
	// todo : speed me up ?
	int x,y,x2,rowy;
	vector3d vx,vy,n;
	dir = norm(-dir);
	// precalc normals -> luminance = dot(n=normal,dir=inverse normalized lightdirection)
	for (y=0;y<17;y++) {
		rowy = y*17;
		for (x=0;x<17;x++) {
			vx = vector3d(2,0,pTer->GetNH(x+1,y)-pTer->GetNH(x-1,y));
			vy = vector3d(0,2,pTer->GetNH(x,y+1)-pTer->GetNH(x,y-1));
			n = norm(cross(vx,vy));
			fLight[rowy+x] = famb + (1.0-famb) * max(0.0,min(1.0,dot(n,dir)));
		}
	}
	// interpolate light
	float fx,fy,fry;
	for (y=0;y<kALRes;y++) {
		fy = (y%4)/4.0;
		fry = 1.0-fy;
		rowy = (y>>2)*17;
		for (x=0;x<kALRes;x++) {
			x2 = x>>2;
			fx = (x%4)/4.0;
			light[kALRes*y+x] = (fry * fLight[rowy+x2]   + fy * fLight[rowy+17+x2]) * (1.0-fx) +
								(fry * fLight[rowy+x2+1] + fy * fLight[rowy+17+x2+1]) * fx;
		}
	}
}


// builds alphalight textures
void	cTerrainPatchRaw::RenderAlphaLight	() {
	static float pLA[kALRes*2*kALRes];
	assert(pTer && "no terrain patch assigned");
	int	x,y,m;

	// light
	for (y=0;y<kALRes;++y)
	for (x=0;x<kALRes;++x)
		pLA[kALRes*2*y+x*2] = light[kALRes*y+x];

	for (m=0;m<max(1,pTer->iMatCount-1);m++) {
		// alpha
		for (y=0;y<kALRes;++y)
		for (x=0;x<kALRes;++x)
			pLA[kALRes*2*y+x*2+1] = alpha[m][kALRes*y+x];

		// create texture if not existant
		if (pTer->pAlphaLight[m])
			glDeleteTextures(1,&pTer->pAlphaLight[m]);
		glGenTextures(1,&pTer->pAlphaLight[m]);
		glBindTexture(GL_TEXTURE_2D,(uint)pTer->pAlphaLight[m]);
		

		gluBuild2DMipmaps(GL_TEXTURE_2D,2,kALRes,kALRes,GL_LUMINANCE_ALPHA ,GL_FLOAT,pLA);
		glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MAG_FILTER,GL_LINEAR);
		glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MIN_FILTER,GL_LINEAR_MIPMAP_NEAREST);
		//glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MAG_FILTER,GL_NEAREST);
		//glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MIN_FILTER,GL_NEAREST);//GL_LINEAR
		glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_WRAP_S,GL_CLAMP);
		glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_WRAP_T,GL_CLAMP);

	}
}



// ****** ****** ****** END