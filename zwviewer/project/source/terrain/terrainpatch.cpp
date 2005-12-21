// ****** ****** ****** TerrainTest.cpp
#include <terrain.h>
#include <geometry.h>
#include <shell.h>
#include <math.h>
#include <input.h>
#include <media.h>
#include <assert.h>
#include <drawutils.h>

#include <SDL/SDL.h>
#include <os.h>
#include <GL/glew.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glext.h>
//#include <ARB_multitexture_extension.h>

int			cTerrainPatch::pLevelWidth[5];
vector3duv*	cTerrainPatch::pVertexBuffer[5];
int*		cTerrainPatch::pVertexIndices[5];
int			cTerrainPatch::pVertexBufferLen[5];
int			cTerrainPatch::pVertexIndicesLen[5];
vector3duv	cTerrainPatch::pAllVertexBuffers[17*17+9*9+5*5+3*3+2*2];
int			cTerrainPatch::pAllVertexIndices[16*(17*2+2)-2 + 8*(9*2+2)-2 + 4*(5*2+2)-2 + 2*(3*2+2)-2 + 1*(2*2+2)-2];
bool		cTerrainPatch::bVertexBuffersInited = 0;


// ****** ****** ****** terrainpatch

// 17*17 : orig data
// 17*17 : interpolation 1
// 9*9 : interpolation 2
// 5*5 : interpolation 3
// 3*3 : interpolation 4
// 17 , 9 , 5, 3
// 16 , 8 , 4, 2
// TODO : use unsigned ints !
// TODO : remove T-vertices !

// scans 17*17 pixels !!!
cTerrainPatch::cTerrainPatch	(vector3d vPos,SDL_Surface* pSurface,int iX,int iY) 
	: vPos(vPos),pL(0),pR(0),pT(0),pB(0),iMatCount(0) {
	int x;

	for (x=0;x<4;x++) pMaterial[x] = 0;
	for (x=0;x<3;x++) pAlphaLight[x] = 0;

	// iMatCount = 0 : waste !
	// iMatCount = 1 : pMaterial[0] & pAlphaLight[0] used
	// iMatCount = 2 : pMaterial[0,1] & pAlphaLight[0] used
	// iMatCount = 3 : pMaterial[0,1,2] & pAlphaLight[0,1] used
	// iMatCount = 4 : pMaterial[0,1,2,3] & pAlphaLight[0,1,2] used

	// initialize global terrain-vertex-buffers
	if (!bVertexBuffersInited)
		InitVertexBuffers();

	pRaw = new cTerrainPatchRaw(this,pSurface,iX,iY);
}
cTerrainPatch::~cTerrainPatch	() { 
	if (pRaw) delete pRaw; 
	for (int m=0;m<3;m++) {
		// delete texture if existant
		if (pAlphaLight[m])
			glDeleteTextures(1,&pAlphaLight[m]);
		pAlphaLight[m] = 0;
	}
}


// initialize static class vars, mainly vertex buffer
void cTerrainPatch::InitVertexBuffers ()
{
	int x,y;

	bVertexBuffersInited = 1;
	pLevelWidth[0] = 17;
	pLevelWidth[1] = 9;
	pLevelWidth[2] = 5;
	pLevelWidth[3] = 3;
	pLevelWidth[4] = 2;
	pVertexBufferLen[0] = 17*17;
	pVertexBufferLen[1] = 9*9;
	pVertexBufferLen[2] = 5*5;
	pVertexBufferLen[3] = 3*3;
	pVertexBufferLen[4] = 2*2;
	pVertexBuffer[0] = &pAllVertexBuffers[0];
	pVertexBuffer[1] = &pAllVertexBuffers[17*17];
	pVertexBuffer[2] = &pAllVertexBuffers[17*17+9*9];
	pVertexBuffer[3] = &pAllVertexBuffers[17*17+9*9+5*5];
	pVertexBuffer[4] = &pAllVertexBuffers[17*17+9*9+5*5+3*3];
	pVertexIndicesLen[0] = 16*(17*2+2)-2;
	pVertexIndicesLen[1] = 8*(9*2+2)-2;
	pVertexIndicesLen[2] = 4*(5*2+2)-2;
	pVertexIndicesLen[3] = 2*(3*2+2)-2;
	pVertexIndicesLen[4] = 1*(2*2+2)-2;
	pVertexIndices[0] = &pAllVertexIndices[0];
	pVertexIndices[1] = &pAllVertexIndices[16*(17*2+2)-2];
	pVertexIndices[2] = &pAllVertexIndices[16*(17*2+2)-2 + 8*(9*2+2)-2];
	pVertexIndices[3] = &pAllVertexIndices[16*(17*2+2)-2 + 8*(9*2+2)-2 + 4*(5*2+2)-2];
	pVertexIndices[4] = &pAllVertexIndices[16*(17*2+2)-2 + 8*(9*2+2)-2 + 4*(5*2+2)-2 + 2*(3*2+2)-2];
	for (x=0;x<17;++x)
	for (y=0;y<17;++y)
		pVertexBuffer[0][17*y+x] = vector3duv(x,y,0,x/16.0,y/16.0);
	for (x=0;x<9;++x)
	for (y=0;y<9;++y)
		pVertexBuffer[1][9*y+x] = vector3duv(x<<1,y<<1,0,x/8.0,y/8.0);
	for (x=0;x<5;++x)
	for (y=0;y<5;++y)
		pVertexBuffer[2][5*y+x] = vector3duv(x<<2,y<<2,0,x/4.0,y/4.0);
	for (x=0;x<3;++x)
	for (y=0;y<3;++y)
		pVertexBuffer[3][3*y+x] = vector3duv(x<<3,y<<3,0,x/2.0,y/2.0);
	for (x=0;x<2;++x)
	for (y=0;y<2;++y)
		pVertexBuffer[4][2*y+x] = vector3duv(x<<4,y<<4,0,x,y);
	int L;
	for (L=0;L<5;L++) {
		int* p = pVertexIndices[L]-1; // -1 -> ++p is the first one
		int width = pLevelWidth[L];
		for (y=0;y<width-1;++y) {
			for (x=0;x<width;++x) {
				*(++p) = y*width+x;
				*(++p) = (y+1)*width+x;
			}
			// the double vertex at the beginning of the next line
			// to produce an inisible face to continue the tri-strip at the next row
			// NOT at last row -> if (..)
			if (y < width-2) {
				*(++p) = (y+1)*width+width-1;
				*(++p) = (y+1)*width;
			}
		}
	}

	// make vertex indices absolute, so one vertex pointer set is enough
	for (x=0;x<pVertexIndicesLen[1];++x) 
		pVertexIndices[1][x] += 17*17;
	for (x=0;x<pVertexIndicesLen[2];++x) 
		pVertexIndices[2][x] += 17*17+9*9;
	for (x=0;x<pVertexIndicesLen[3];++x) 
		pVertexIndices[3][x] += 17*17+9*9+5*5;
	for (x=0;x<pVertexIndicesLen[4];++x) 
		pVertexIndices[4][x] += 17*17+9*9+5*5+3*3;
}


// recalc geometry, precalc interpolations and normals
void	cTerrainPatch::Recalc	() {
	int x,y;
	// generate interpolation precalc and fMaxError for every level

	pInterpol[0] = &pData[0]; // 17�
	pInterpol[1] = &pData[17*17]; // 17�
	pInterpol[2] = &pData[17*17+17*17]; // 9�
	pInterpol[3] = &pData[17*17+17*17+9*9]; // 5�
	pInterpol[4] = &pData[17*17+17*17+9*9+5*5]; // 3�
	float*	inter1 = pInterpol[0];
	float*	inter2 = pInterpol[1];
	float h1,h2,h3,h4;
	int		L,width,widthb,widthb2,rowy,rowyb,xb,rowy0;
	float	error;

	// precalc normals
	for (y=0;y<16;y++) {
		rowy = 17*y;
		rowyb = 16*2*y;
		for (x=0;x<16;x++) {
			h1 = pData[rowy+x];
			h2 = pData[rowy+x+1];
			h3 = pData[rowy+17+x];
			h4 = pData[rowy+17+x+1];
			pNorm[rowyb + 2*x + 0] = norm(cross(vector3d(1,0,h2-h1),vector3d(0,1,h3-h1)));
			pNorm[rowyb + 2*x + 1] = norm(cross(vector3d(-1,0,h3-h4),vector3d(0,-1,h2-h4)));
		}
	}

	fMinH = pData[0];
	fMaxH = pData[0];

	fMaxError[0] = 0;

	// level 1 is special because the next finer level is base data
	fMaxError[1] = 0;
	width = 17;
	for (y=0;y<width;++y) {
		rowy = width*y;
		for (x=0;x<width;++x) {
			if (x&1) {
				if (y&1)
						inter2[rowy+x] = 0.5*(inter1[rowy+width+x-1] + inter1[rowy-width+x+1]);
				else	inter2[rowy+x] = 0.5*(inter1[rowy+x-1] + inter1[rowy+x+1]);
			} else {
				if (y&1)
						inter2[rowy+x] = 0.5*(inter1[rowy-width+x] + inter1[rowy+width+x]);
				else	inter2[rowy+x] = inter1[rowy+x];
			}

			error = fabs(pData[rowy+x] - inter2[rowy+x]);
			if (fMaxError[1] < error)
				fMaxError[1] = error;

			if (fMinH > pData[rowy+x])
				fMinH = pData[rowy+x];
			if (fMaxH < pData[rowy+x])
				fMaxH = pData[rowy+x];
		}
	}
	
	// level 2,3,4 are very similar
	for (L=1;L<4;L++) {
		fMaxError[L+1] = fMaxError[L];
		width = pLevelWidth[L];
		widthb = pLevelWidth[L-1];
		inter1 = pInterpol[L];
		inter2 = pInterpol[L+1];
		widthb2 = widthb*2;
		for (y=0;y<width;++y) {
			rowy = width*y;
			rowyb = widthb*(y<<1);
			rowy0 = 17*(y<<L);
			for (x=0;x<width;++x) {
				xb = (x<<1);
				if (x&1) {
					if (y&1)
							inter2[rowy+x] = 0.5*(inter1[rowyb+widthb2+xb-2] + inter1[rowyb-widthb2+xb+2]);
					else	inter2[rowy+x] = 0.5*(inter1[rowyb+xb-2] + inter1[rowyb+xb+2]);
				} else {
					if (y&1)
							inter2[rowy+x] = 0.5*(inter1[rowyb-widthb2+xb] + inter1[rowyb+widthb2+xb]);
					else	inter2[rowy+x] = inter1[rowyb+xb];
				}
				
				error = fabs(pData[rowy0+(x<<L)] - inter2[rowy+x]);
				if (fMaxError[L+1] < error)
					fMaxError[L+1] = error;
			}
		}
	}

	// calc boundingsphere
	fMidH = 0.5 * (fMinH + fMaxH);
	fRad = mag(vector3d(8,8,fMidH - fMinH));
}


// clip camera along z axis above
void	cTerrainPatch::HClip	(vector3d &vCamPos,float delta) {
	vector3d v = vCamPos-vPos;
	if (v.x < 0.0 || v.x >= 16.0 || v.y < 0.0 || v.y >= 16.0) return;
	float minh = delta + GetH(v.x,v.y);
	if (vCamPos.z < minh)
		vCamPos.z = minh;
}

// calc LOD from camera position, biased using maxerror
void	cTerrainPatch::CalcLOD	(vector3d &vCamPos) {
	if (gInput.bKeys[cInput::kkey_y]) return;
		
	float mylod = mag(vCamPos-vPos)/(24.0) - 0.3;
	mylod -= fMaxError[4] / 24.0; // maxerror bias
	if (mylod > 4.0) mylod = 4.0;
	if (mylod < 0.0) mylod = 0.0;
	//mylod = 0.0;
	iLevel = floor(mylod);
	fTween = mylod - (float)iLevel;	
}


/*
// todo : implement me =)
bool	cHeightField::IntersectCheck	(vector3d p,float fRad,bool debug,vector3d v)
{
	// checks if a sphere starting at p with radius fRad intersects anything.
	int minx = floor(p.x - fRad);
	int miny = floor(p.y - fRad);
	int maxx = ceil(p.x + fRad);
	int maxy = ceil(p.y + fRad);

	int i,j;
	
	if (debug)
	for (j=miny;j<=maxy;j++)
	for (i=minx;i<=maxx;i++)
		if (IntersectCheck(p,fRad,i,j,v)) DrawTile(i,j);

	for (j=miny;j<=maxy;j++)
	for (i=minx;i<=maxx;i++)
		if (IntersectCheck(p,fRad,i,j,v)) return true;
	return false;
}

bool	cHeightField::IntersectCheck	(vector3d p,float fRad,int x,int y,vector3d v)
{
	if (x < 0 || y < 0 || x >= iCX-1 || y >= iCY-1) return false;
	// checks if a sphere starting at p with radius fRad intersects field x,y.
	float h1 = pHeight[(y)*iCX+x];
	float h2 = pHeight[(y)*iCX+x+1];
	float h3 = pHeight[(y+1)*iCX+x];
	float h4 = pHeight[(y+1)*iCX+x+1];

	float hmin = (h1 < h2) ? h1 : h2;
	hmin = (hmin < h3) ? hmin : h3;
	hmin = (hmin < h4) ? hmin : h4;
	if (p.z + fRad < hmin) return false;

	float hmax = (h1 > h2) ? h1 : h2;
	hmax = (hmax > h3) ? hmax : h3;
	hmax = (hmax > h4) ? hmax : h4;
	if (p.z - fRad > hmax) return false;

	vector3d o = vector3d(x+1,y,h2);
	vector3d n1 = norm(cross(vector3d(1,0,h2-h1),vector3d(0,1,h3-h1)));
	vector3d n2 = norm(cross(vector3d(0,-1,h2-h4),vector3d(-1,0,h3-h4)));
	float dist1 = distancePlane(o,n1,p);
	float dist2 = distancePlane(o,n2,p);
	bool oob1 = dist1 > fRad || dist1 < -fRad; // total out of bounds
	bool oob2 = dist2 > fRad || dist2 < -fRad; // total out of bounds
	if (oob1 && oob2) return false;
	
	if (!oob1 && (0 || dot(v,n1) < 0.0))
	{
		// check upper left tri
		vector3d in = closestPointOnTriangle(vector3d(x,y,h1),o,vector3d(x,y+1,h3),p);
		if (mag(in-p) < fRad) return true;
	}
	if (!oob2 && (0 || dot(v,n2) < 0.0))
	{
		// check upper left tri
		vector3d in = closestPointOnTriangle(vector3d(x+1,y+1,h2),o,vector3d(x,y+1,h3),p);
		if (mag(in-p) < fRad) return true;
	}

	return false;
}
*/

// get exact height for float coords, for objectplacement/camclip
float cTerrainPatch::GetH (float x,float y) {
	if (x < 0.0 || y < 0.0) return 0.0;
	int ix = (int)x;
	int iy = (int)y;
	int irowy = iy * 17;
	if (ix >= 16 || iy >= 16) return 0.0;
	x -= (float)ix;
	y -= (float)iy;
	float rx = 1.0 - x;
	float f;

	//return pData[irowy+ix];
	/* x,y+1____x+1,y+1
	 * |\    RT|
	 * |  \  . |
	 * |    \| |
	 * |LB____\|
	 * x,y	    x+1,y
	 */

	// TODO : division by zero possible ? rx == 0 OR x == 0.0
	if (x + y < 1.0) {
		// left bottom
		f = y / rx;
		float aux = pData[irowy+ix+1] * x; // ecke, x+1,y
		return	(pData[irowy+ix] * rx + aux) * (1.0 - f) + 
				(pData[irowy+17+ix] * rx + aux) * (f);
	} else {
		// right top
		if (x == 0.0) return pData[irowy+17+ix];
		f = (1.0 - y) / x;
		float aux = pData[irowy+17+ix] * rx; // ecke, x,y+1
		return	(pData[irowy+ix+1] * x + aux) * (f) + 
				(pData[irowy+17+ix+1] * x + aux) * (1.0 - f);
	}
}


// float coords from vector3d
float	cTerrainPatch::GetH (vector3d &v) {
	return GetH(v.x-vPos.x,v.y-vPos.y);
}



// can access neighbour patches, used for normals in lighting
float	cTerrainPatch::GetNH	(int x,int y) {
	cTerrainPatch* pCur = this;
	if (x < 0) {
		if (pCur->pL) {
			pCur = pCur->pL;
			x += 16;
		} else {
			x = 0;
		}
	} else if (x > 16) {
		if (pCur->pR) {
			pCur = pCur->pR;
			x -= 16;
		} else {
			x = 16;
		}
	}
	if (y < 0) {
		if (pCur->pB) {
			pCur = pCur->pB;
			y += 16;
		} else {
			y = 0;
		}
	} else if (y > 16) {
		if (pCur->pT) {
			pCur = pCur->pT;
			y -= 16;
		} else {
			y = 16;
		}
	}
	return pCur->pData[y*17+x];
}



// guess what..  (rather big piece of beauty)
void	cTerrainPatch::Draw	() {
	//if (fTween < 0.0 || fTween > 1.0) return;
	//if (iLevel > 4) iLevel = 4; // check outside is better in this case !
	//if (iLevel < 0) iLevel = 0; // check outside is better in this case !

	if (!SphereInFrustum(vPos+vector3d(8,8,fMidH),fRad))
		return;

	// prepare access for the current level
	vector3duv*	vb = pVertexBuffer[iLevel];
	int			vblen = pVertexBufferLen[iLevel];
	int			x,y,lastrowy,lastrowy3,rowy1,rowy2,rowy3,len1,len2,len3;
	float		fRTween = 1.0 - fTween;
	float*		inter1 = pInterpol[iLevel];
	float*		inter2 = pInterpol[iLevel+1];
	float*		inter3; // used for adaption
	float		fTweenb,fRTweenb;

	// 0,0 is BOTTOM left !!!
	
	if (iLevel <= 0) {
		// normal tween
		len2 = 17;//pLevelWidth[iLevel];
		for (y=0;y<len2;++y) {
			rowy2 = len2*y;
			for (x=0;x<len2;++x)
				vb[rowy2+x].z = fRTween * inter1[rowy2+x] + fTween * inter2[rowy2+x];
		}

		// LEFT equal level : left and bottom neighbors dictate the tween of the border
		if (pL && pL->iLevel == iLevel && pL->fTween > fTween) {
			// left neighbor -> x = 0
			fTweenb = pL->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (y=0;y<len2;++y) {
				rowy2 = len2*y;
				vb[rowy2].z = fRTweenb * inter1[rowy2] + fTweenb * inter2[rowy2];
			}
		}
		if (pR && pR->iLevel == iLevel && pR->fTween > fTween) {
			// left neighbor -> x = 0
			fTweenb = pR->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (y=0;y<len2;++y) {
				rowy2 = len2*y;
				vb[rowy2+len2-1].z = fRTweenb * inter1[rowy2+len2-1] + fTweenb * inter2[rowy2+len2-1];
			}
		}
		// BOTTOM equal level : left and bottom neighbors dictate the tween of the border
		if (pB && pB->iLevel == iLevel && pB->fTween > fTween) {
			// bottom neighbor -> y = 0
			fTweenb = pB->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (x=0;x<len2;++x)
				vb[x].z = fRTweenb * inter1[x] + fTweenb * inter2[x];
		}
		if (pT && pT->iLevel == iLevel && pT->fTween > fTween) {
			// bottom neighbor -> y = 0
			fTweenb = pT->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (x=0;x<len2;++x)
				vb[x+len2*(len2-1)].z = fRTweenb * inter1[x+len2*(len2-1)] + fTweenb * inter2[x+len2*(len2-1)];
		}
	} else if (iLevel >= 4) {
		// no tween
		len1 = pLevelWidth[iLevel-1];
		len2 = pLevelWidth[iLevel];
		for (y=0;y<len2;++y) {
			rowy2 = len2*y;
			rowy1 = len1*(y<<1);
			for (x=0;x<len2;++x)
				vb[rowy2+x].z = inter1[rowy1+(x<<1)];
		}
	} else {
		// normal tween
		len1 = pLevelWidth[iLevel-1];
		len2 = pLevelWidth[iLevel];
		for (y=0;y<len2;++y) {
			rowy2 = len2*y;
			rowy1 = len1*(y<<1);
			for (x=0;x<len2;++x)
				vb[rowy2+x].z = fRTween * inter1[rowy1+(x<<1)] + fTween * inter2[rowy2+x];
		}

		// LEFT equal level : left and bottom neighbors dictate the tween of the border
		if (pL && pL->iLevel == iLevel && pL->fTween > fTween) {
			// left neighbor -> x = 0
			fTweenb = pL->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (y=0;y<len2;++y) {
				rowy2 = len2*y;
				rowy1 = len1*(y<<1);
				vb[rowy2].z = fRTweenb * inter1[rowy1] + fTweenb * inter2[rowy2];
			}
		}
		if (pR && pR->iLevel == iLevel && pR->fTween > fTween) {
			// right neighbor -> x = len-1
			fTweenb = pR->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (y=0;y<len2;++y) {
				rowy2 = len2*y;
				rowy1 = len1*(y<<1);
				vb[rowy2+len2-1].z = fRTweenb * inter1[rowy1+len1-1] + fTweenb * inter2[rowy2+len2-1];
			}
		}
		// BOTTOM equal level : left and bottom neighbors dictate the tween of the border
		if (pB && pB->iLevel == iLevel && pB->fTween > fTween) {
			// bottom neighbor -> y = 0
			fTweenb = pB->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (x=0;x<len2;++x)
				vb[x].z = fRTweenb * inter1[(x<<1)] + fTweenb * inter2[x];
		}
		if (pT && pT->iLevel == iLevel && pT->fTween > fTween) {
			// top neighbor -> y = len-1
			fTweenb = pT->fTween;
			fRTweenb = 1.0 - fTweenb;
			for (x=0;x<len2;++x)
				vb[x+len2*(len2-1)].z = fRTweenb * inter1[(x<<1)+len1*(len1-1)] + fTweenb * inter2[x+len2*(len2-1)];
		
		}
	}
	
	if (iLevel < 4) {
		inter3 = pInterpol[iLevel+2];
		len3 = pLevelWidth[iLevel+1];

		// LEFT if i have a finer tessalation level, i adapt every odd vertex
		if (pL && pL->iLevel > iLevel) {
			// left neighbor -> x = 0... at most one level adaption
			if (iLevel >= 3) {
				// neighbor is at max
				for (y=0;y<len2;++y) {
					rowy2 = len2*y;
					vb[rowy2].z = inter2[rowy2];
				}
			} else {
				// neighbor is tweening
				fTweenb = (pL->iLevel > iLevel + 1)?1.0:(pL->fTween);
				fRTweenb = 1.0 - fTweenb;
				for (y=0;y<len2;++y) {
					rowy3 = len3*(y>>1);
					rowy2 = len2*y;
					if (y&1)
							vb[rowy2].z = fRTweenb * inter2[rowy2] + fTweenb * 0.5 * (inter3[rowy3] + inter3[rowy3+len3]);
					else	vb[rowy2].z = fRTweenb * inter2[rowy2] + fTweenb * inter3[rowy3];
				}
			}
		}
		
		// RIGHT if i have a finer tessalation level, i adapt every odd vertex
		if (pR && pR->iLevel > iLevel) {
			// right neighbor -> x = len2-1... at most one level adaption
			x = len2-1;
			if (iLevel >= 3) {
				// neighbor is at max
				for (y=0;y<len2;++y) {
					rowy2 = len2*y+x;
					vb[rowy2].z = inter2[rowy2];
				}
			} else {
				// neighbor is tweening
				fTweenb = (pR->iLevel > iLevel + 1)?1.0:(pR->fTween);
				fRTweenb = 1.0 - fTweenb;
				for (y=0;y<len2;++y) {
					rowy3 = len3*(y>>1)+len3-1;
					rowy2 = len2*y+x;
					if (y&1)
							vb[rowy2].z = fRTweenb * inter2[rowy2] + fTweenb * 0.5 * (inter3[rowy3] + inter3[rowy3+len3]);
					else	vb[rowy2].z = fRTweenb * inter2[rowy2] + fTweenb * inter3[rowy3];
				}
			}
		}
		
		// BOTTOM if i have a finer tessalation level, i adapt every odd vertex
		if (pB && pB->iLevel > iLevel) {
			// bottom neighbor -> y = 0... at most one level adaption
			if (iLevel >= 3) {
				// neighbor is at max
				for (x=0;x<len2;++x) {
					vb[x].z = inter2[x];
				}
			} else {
				// neighbor is tweening
				fTweenb = (pB->iLevel > iLevel + 1)?1.0:(pB->fTween);
				fRTweenb = 1.0 - fTweenb;
				for (x=0;x<len2;++x) {
					rowy3 = (x>>1);
					if (x&1)
							vb[x].z = fRTweenb * inter2[x] + fTweenb * 0.5 * (inter3[rowy3] + inter3[rowy3+1]);
					else	vb[x].z = fRTweenb * inter2[x] + fTweenb * inter3[rowy3];
				}
			}
		}
		
		// TOP if i have a finer tessalation level, i adapt every odd vertex
		if (pT && pT->iLevel > iLevel) {
			// right neighbor -> y = len2-1... at most one level adaption
			lastrowy = len2*(len2-1);
			lastrowy3 = len3*(len3-1);
			if (iLevel >= 3) {
				// neighbor is at max
				for (x=0;x<len2;++x) {
					rowy2 = x+lastrowy;
					vb[rowy2].z = inter2[rowy2];
				}
			} else {
				// neighbor is tweening
				fTweenb = (pT->iLevel > iLevel + 1)?1.0:(pT->fTween);
				fRTweenb = 1.0 - fTweenb;
				for (x=0;x<len2;++x) {
					rowy3 = (x>>1)+lastrowy3;
					rowy2 = x+lastrowy;
					if (x&1)
							vb[rowy2].z = fRTweenb * inter2[rowy2] + fTweenb * 0.5 * (inter3[rowy3] + inter3[rowy3+1]);
					else	vb[rowy2].z = fRTweenb * inter2[rowy2] + fTweenb * inter3[rowy3];
				}
			}
		}
	}

	// DONE : das war die komplexe kacke mit den nachbarn =)

	// TODO : die noch komplexere kacke mit den 
		// inneren nachbar-vertices der border-vertices, es PLOPPT wieder =( 

	
	glPushMatrix();
	glTranslatef(vPos.x,vPos.y,vPos.z);


	//if (1) 
	{
		// C(color) and A(alpha) indices :
		// f:fragment(glColor3f value)
		// s:source:the texture being drawn
		// c:texenv color
		// p;previous
		// v:value, end result

		// GL_TEXTURE_ENV_MODE = 
		// GL_REPLACE	Cv = Cs  , Av = As
		// GL_MODULATE	Cv = Cf * Cs  , Av = Af * As
		// GL_DECAL		Cv = Cf * (1-As) + Cs * As  , Av = Af
		// GL_BLEND		Cv = Cf * (1-Cs) + Cc * Cs  , Av = Af * As  // (texenvcol)
		// GL_ADD		Cv = Cf + Cs  , Av = Af + As
		// GL_COMBINE : more combos possible, see below

		// GL_COMBINE_RGB/GL_COMBINE_ALPHA = 
		// GL_REPLACE		V = Arg0
		// GL_MODULATE		V = Arg0 * Arg1
		// GL_ADD			V = Arg0 + Arg1
		// GL_ADD_SIGNED	V = Arg0 + Arg1 - 0.5
		// GL_INTERPOLATE	V = Arg0 * Arg2 + Arg1 * (1 - Arg2)
		// GL_SUBTRACT		V = Arg0 - Arg1
		// GL_DOT3_RGB		V = dot(Arg0-0.5,Arg1-0.5)
		// GL_DOT3_RGBA		V = dot(Arg0-0.5,Arg1-0.5)
		//   result from dot placed in all 3 or 4 channels of output
		
		// GL_OPERANDn_RGB = GL_SRC_COLOR 
		// GL_OPERANDn_RGB = GL_ONE_MINUS_SRC_COLOR 
		// GL_OPERANDn_RGB = GL_SRC_ALPHA
		// GL_OPERANDn_RGB = GL_ONE_MINUS_SRC_ALPHA
		// GL_SRCn_RGB = GL_TEXTURE
		// GL_SRCn_RGB = GL_TEXTUREn !!! -> alle BISHER genutzten stages beliebig combinierbar !
		// GL_SRCn_RGB = GL_CONSTANT(texenvcol)
		// GL_SRCn_RGB = GL_PRIMARY_COLOR(glColor3f value)
		// GL_SRCn_RGB = PREVIOUS

		// default rgb source : 0=tex 1=prev 2=const(texenvcolor)
		// default rgb operand : 0=color 1=color 2=alpha
		// default alpha source : 0=tex 1=prev 2=const(texenvcolor)
		// default alpha operand : 0=alpha 1=alpha 2=alpha
		// default texenv color = 0,0,0,0
		// glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_RGB, GL_TEXTURE);
		// glTexEnvi(GL_TEXTURE_ENV, GL_OPERAND0_RGB, GL_SRC_COLOR);// or GL_SRC_ALPHA or ...
		
			
		// ++++ ++++ FIRST PASS
		if (iMatCount == 1) {
			// FIRST PASS
			// GL_TEXTURE0 : matA
			// GL_TEXTURE2 : matB
			// GL_TEXTURE3 ; light&alphaB

			glActiveTextureARB(GL_TEXTURE0);
			glEnable(GL_TEXTURE_2D);
			pMaterial[0]->Bind();
			// stage1 : unused, passtrough
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_REPLACE);
			
			glActiveTextureARB(GL_TEXTURE1);
			glEnable(GL_TEXTURE_2D);
			glBindTexture(GL_TEXTURE_2D,pAlphaLight[0]);
			// stage2 : apply lightmap in GL_TEXTURE1 to prev
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_COMBINE);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_RGB, GL_MODULATE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_RGB, GL_TEXTURE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE1_RGB, GL_PREVIOUS);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_ALPHA, GL_REPLACE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_ALPHA, GL_PREVIOUS);

			glDrawElements(GL_TRIANGLE_STRIP,pVertexIndicesLen[iLevel],GL_UNSIGNED_INT,pVertexIndices[iLevel]);
		}
		if (iMatCount >= 2) {
			// FIRST PASS
			// GL_TEXTURE0 : matA
			// GL_TEXTURE2 : matB
			// GL_TEXTURE3 ; light&alphaB

			glActiveTextureARB(GL_TEXTURE0);
			glEnable(GL_TEXTURE_2D);
			pMaterial[0]->Bind();
			// stage1 : unused, passtrough
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_REPLACE);

			glActiveTextureARB(GL_TEXTURE1);
			glEnable(GL_TEXTURE_2D);
			pMaterial[1]->Bind();
			// stage2 : blend GL_TEXTURE1 onto GL_TEXTURE0 
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_COMBINE);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_RGB, GL_INTERPOLATE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_RGB, GL_TEXTURE1);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE1_RGB, GL_TEXTURE0);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE2_RGB, GL_TEXTURE2); // use alpha here for interpolation
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_ALPHA, GL_REPLACE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_ALPHA, GL_TEXTURE0);
			
			glActiveTextureARB(GL_TEXTURE2);
			glEnable(GL_TEXTURE_2D);
			glBindTexture(GL_TEXTURE_2D,pAlphaLight[0]);
			// stage3 : apply lightmap in GL_TEXTURE2 to prev
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_COMBINE);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_RGB, GL_MODULATE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_RGB, GL_PREVIOUS);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE1_RGB, GL_TEXTURE2);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_ALPHA, GL_REPLACE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_ALPHA, GL_PREVIOUS);

			glDrawElements(GL_TRIANGLE_STRIP,pVertexIndicesLen[iLevel],GL_UNSIGNED_INT,pVertexIndices[iLevel]);
		}





		// ++++ ++++ SECOND PASS

		if (iMatCount == 3) {
			// SECOND PASS
			// GL_TEXTURE0 : matA
			// GL_TEXTURE1 ; light&alphaA
			// GL_TEXTURE2 : matB
			// GL_TEXTURE3 ; light&alphaB

			glActiveTextureARB(GL_TEXTURE0);
			pMaterial[2]->Bind();
			// stage1 : unused, passtrough
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_REPLACE);

			glActiveTextureARB(GL_TEXTURE1);
			glBindTexture(GL_TEXTURE_2D,pAlphaLight[1]);
			// stage2 : apply lightmap in GL_TEXTURE1 to prev
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_COMBINE);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_RGB, GL_MODULATE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_RGB, GL_PREVIOUS);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE1_RGB, GL_TEXTURE);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_ALPHA, GL_REPLACE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_ALPHA, GL_TEXTURE);

			glActiveTextureARB(GL_TEXTURE2);
			glDisable(GL_TEXTURE_2D);

			glEnable(GL_BLEND);
			glDrawElements(GL_TRIANGLE_STRIP,pVertexIndicesLen[iLevel],GL_UNSIGNED_INT,pVertexIndices[iLevel]);
			glDisable(GL_BLEND);
		}
		if (iMatCount >= 4) {
			// SECOND PASS
			// GL_TEXTURE0 : matA
			// GL_TEXTURE1 ; light&alphaA
			// GL_TEXTURE2 : matB
			// GL_TEXTURE3 ; light&alphaB

			glActiveTextureARB(GL_TEXTURE0);
			pMaterial[2]->Bind();
			// stage1 : unused, passtrough
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_REPLACE);

			glActiveTextureARB(GL_TEXTURE1);
			glBindTexture(GL_TEXTURE_2D,pAlphaLight[1]);
			// stage2 : unused, passtrough
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_REPLACE);

			glActiveTextureARB(GL_TEXTURE2);
			pMaterial[3]->Bind();
			// stage3 : blend GL_TEXTURE2 onto GL_TEXTURE0 
			// produce the final alpha channel by adding GL_TEXTURE1 and GL_TEXTURE3
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_COMBINE);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_RGB, GL_INTERPOLATE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_RGB, GL_TEXTURE2);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE1_RGB, GL_TEXTURE0);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE2_RGB, GL_TEXTURE3); // use alpha here for interpolation
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_ALPHA, GL_ADD);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_ALPHA, GL_TEXTURE1);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE1_ALPHA, GL_TEXTURE3);
			
			glActiveTextureARB(GL_TEXTURE3);
			glEnable(GL_TEXTURE_2D);
			glBindTexture(GL_TEXTURE_2D,pAlphaLight[2]);
			// stage4 : apply lightmap in GL_TEXTURE1 to prev
			glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_COMBINE);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_RGB, GL_MODULATE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_RGB, GL_PREVIOUS);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE1_RGB, GL_TEXTURE1);
			glTexEnvi(GL_TEXTURE_ENV, GL_COMBINE_ALPHA, GL_REPLACE);
			glTexEnvi(GL_TEXTURE_ENV, GL_SOURCE0_ALPHA, GL_PREVIOUS);
			
			glEnable(GL_BLEND);
			glDrawElements(GL_TRIANGLE_STRIP,pVertexIndicesLen[iLevel],GL_UNSIGNED_INT,pVertexIndices[iLevel]);
			glDisable(GL_BLEND);
		}
		
		
		glActiveTextureARB(GL_TEXTURE3_ARB);
		glDisable(GL_TEXTURE_2D);
		glActiveTextureARB(GL_TEXTURE2_ARB);
		glDisable(GL_TEXTURE_2D);
		glActiveTextureARB(GL_TEXTURE1_ARB);
		glDisable(GL_TEXTURE_2D);
		glActiveTextureARB(GL_TEXTURE0_ARB);
		glDisable(GL_TEXTURE_2D);
	}

	// DEBUG : LOD-grid
	if (gInput.bKeys[cInput::kkey_g]) {
		len1 = pLevelWidth[iLevel];

		for (int y=0;y<len1;++y) {
			if (y == 0 || y == len1-1)
					glColor3f(0,1,0);
			else	glColor3f(1,0,0);
			glBegin(GL_LINE_STRIP);
			for (int x=0;x<len1;++x) {
				glVertex3fv(&vb[len1*y+x].x);
			}
			glEnd();
		}
		
		for (int x=0;x<len1;++x) {
			if (x == 0 || x == len1-1)
					glColor3f(0,1,0);
			else	glColor3f(1,0,0);
			glBegin(GL_LINE_STRIP);
			for (int y=0;y<len1;++y) {
				glVertex3fv(&vb[len1*y+x].x);
			}
			glEnd();
		}
	}

	// DEBUG : normals
	if (gInput.bKeys[cInput::kkey_n]) {
		glBegin(GL_LINES);
		glColor3f(0,0,1);
		for (y=0;y<16;y++)
		for (x=0;x<16;x++) {
			vector3d v = vector3d(x+0.5,y+0.5,GetH(x+0.5,y+0.5));
			vector3d vx = vector3d(2,0,GetNH(x+1,y)-GetNH(x-1,y));
			vector3d vy = vector3d(0,2,GetNH(x,y+1)-GetNH(x,y-1));
			vector3d n = norm(cross(vx,vy));
			if (gInput.bKeys[cInput::kkey_lshift]) n = pNorm[16*2*y+2*x+0];
			if (gInput.bKeys[cInput::kkey_lcontrol]) n = pNorm[16*2*y+2*x+1];
			//v += vector3d(0,0,1);
			glVertex3fv(&v.x);
			v += n;
			glVertex3fv(&v.x);
		}
		
		glEnd();
	}	
	
	// DEBUG : x,y axes, maxerror	
	if (gInput.bKeys[cInput::kkey_h]) {
		float z=15;
		
		
		if (0) {
			glBegin(GL_LINES);
				glColor3f(1,0,0);
				
				int timer = 5500;
				float rot = (gShell.iClock % timer) / (float)timer;
				rot = 0.5 + 0.5 * sin(rot * 2.0 * kPi);
				
				class cBla { public:
					static void myfunc (float fx,float fy,cTerrainPatch* t) {
						vector3d g = vector3d(fx,fy,t->GetH(fx,fy));
						vector3d v;
						float size = 0.01;
						v = g + vector3d(+size,0,0);glVertex3fv(&v.x);
						v = g + vector3d(-size,0,0);glVertex3fv(&v.x);
						v = g + vector3d(0,+size,0);glVertex3fv(&v.x);
						v = g + vector3d(0,-size,0);glVertex3fv(&v.x);
						v = g + vector3d(0,0,+size);glVertex3fv(&v.x);
						v = g + vector3d(0,0,-size);glVertex3fv(&v.x);
						//v = vector3d(g.x,g.y,0);glVertex3fv(&v.x);
					}
				};
				for (y=0;y<3;y++)
				for (x=0;x<3;x++)
					cBla::myfunc(x,y,this);
				glColor3f(1,0.5,0);
				for (y=0;y<=1*20;y++)
				for (x=0;x<=1*20;x++) {
					cBla::myfunc(x*0.05,y*0.05,this);
					cBla::myfunc(x*0.05,y*0.05,this);
					cBla::myfunc(x*0.05,y*0.05,this);
					cBla::myfunc(x*0.05,y*0.05,this);
				}
			glEnd();
		}

		// material count
		for (x=0;x<iMatCount;x++) {
			DrawSphere(vector3d(9,8,z+0.5*x),vector3d(x*0.33,0,0),0.2);
		}
		
		// maxerror
		glBegin(GL_LINES);
			for (x=4;x>=1;x--) {
				glColor3f(x/4.0,x/4.0,0);
				glVertex3f(8+x/8.0,8,z);
				glVertex3f(8+x/8.0,8,z+fMaxError[x]);
			}
		glEnd();

		// x,y axes
		glBegin(GL_LINES);
			glColor3f(1,0,0);
			glVertex3f(8,8,z);
			glVertex3f(16,8,z);
			glColor3f(0,1,0);
			glVertex3f(8,8,z);
			glVertex3f(8,16,z);
			z = 13.1;
			glColor3f(0.5,0,0);
			if (pR) {
				glVertex3f(8,8,z);
				glVertex3f(8+0.2*(pR->vPos.x-vPos.x),8+0.2*(pR->vPos.y-vPos.y),z);
			}
			glColor3f(1,0,1);
			if (pL) {
				glVertex3f(8,8,z);
				glVertex3f(8+0.2*(pL->vPos.x-vPos.x),8+0.2*(pL->vPos.y-vPos.y),z);
			}
			glColor3f(0,0.5,0);
			if (pT) {
				glVertex3f(8,8,z);
				glVertex3f(8+0.2*(pT->vPos.x-vPos.x),8+0.2*(pT->vPos.y-vPos.y),z);
			}
			glColor3f(0,0,1);
			if (pB) {
				glVertex3f(8,8,z);
				glVertex3f(8+0.2*(pB->vPos.x-vPos.x),8+0.2*(pB->vPos.y-vPos.y),z);
			}
		glEnd();
	}

	glPopMatrix();
}


// set up vertex pointer and optimizations
void	cTerrainPatch::PrepareDraw() {
	glColor3f(1,1,1);
	glDisable(GL_LIGHTING);
	glDisable(GL_COLOR_MATERIAL);
	glDisable(GL_BLEND);
	glCullFace(GL_FRONT); // TODO : remove
	glBlendFunc(GL_SRC_ALPHA,GL_ONE_MINUS_SRC_ALPHA);
	//glEnable(GL_TEXTURE_2D);
	//glDisable(GL_ALPHA_TEST);

	glDisableClientState(GL_COLOR_ARRAY);	// ARGH! this was ON by default! 
	glEnableClientState(GL_VERTEX_ARRAY);		// glVertexPointer
	glEnableClientState(GL_TEXTURE_COORD_ARRAY);// glTexCoordPointer.

	glVertexPointer(3,GL_FLOAT,(char*)&pAllVertexBuffers[1].x - (char*)&pAllVertexBuffers[0].x,&pAllVertexBuffers[0].x);
	glTexCoordPointer(2,GL_FLOAT,(char*)&pAllVertexBuffers[1].u - (char*)&pAllVertexBuffers[0].u,&pAllVertexBuffers[0].u);
	
	// TODO : texcoords specified 4 times the same... reduce ?
	glClientActiveTextureARB(GL_TEXTURE1);
	glEnableClientState(GL_TEXTURE_COORD_ARRAY);// glTexCoordPointer.
	glTexCoordPointer(2,GL_FLOAT,(char*)&pAllVertexBuffers[1].u - (char*)&pAllVertexBuffers[0].u,&pAllVertexBuffers[0].u);
	
	glClientActiveTextureARB(GL_TEXTURE2);
	glEnableClientState(GL_TEXTURE_COORD_ARRAY);// glTexCoordPointer.
	glTexCoordPointer(2,GL_FLOAT,(char*)&pAllVertexBuffers[1].u - (char*)&pAllVertexBuffers[0].u,&pAllVertexBuffers[0].u);
	
	glClientActiveTextureARB(GL_TEXTURE3);
	glEnableClientState(GL_TEXTURE_COORD_ARRAY);// glTexCoordPointer.
	glTexCoordPointer(2,GL_FLOAT,(char*)&pAllVertexBuffers[1].u - (char*)&pAllVertexBuffers[0].u,&pAllVertexBuffers[0].u);
	
	glClientActiveTextureARB(GL_TEXTURE0);
}


// cleanup
void	cTerrainPatch::CleanupDraw() {
	glCullFace(GL_BACK); // TODO : remove
	glEnable(GL_LIGHTING);
	glEnable(GL_COLOR_MATERIAL);
	glTexEnvi(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_MODULATE);

	glDisableClientState(GL_VERTEX_ARRAY);		// glVertexPointer
	glDisableClientState(GL_TEXTURE_COORD_ARRAY);// glTexCoordPointer.
}


bool	cTerrainPatch::RayIntersect	(vector3d o,vector3d dir,float &res,int &foundx,int &foundy) {
	o -= vPos;
	float fminx = min(o.x,o.x+dir.x);
	float fmaxx = max(o.x,o.x+dir.x);
	float fminy = min(o.y,o.y+dir.y);
	float fmaxy = max(o.y,o.y+dir.y);
	int xstart = max(0,floor(fminx));
	int xend = min(15,ceil(fmaxx));
	int ystart = max(0,floor(fminy));
	int yend = min(15,ceil(fmaxy));

	if (xstart >= 16 || ystart >= 16 || xend < 0 || yend < 0) return false;

	int x,y,rowy,k;
	vector3d n,p1,hit;
	float h1,h2,h3,h4;
	float f1,f2,fx1,fx2,fy1,fy2,fz1,fz2;
	float fminz,fmaxz,t,c,d,dx,dy;

	bool bfound = 0;
	float founddist;

	if (fabs(dir.y) > fabs(dir.x)) {
		for (y=ystart;y<=yend;y++) {
			rowy = 17*y;
			f1 = (y - o.y)/dir.y;
			f2 = (y + 1.0 - o.y)/dir.y;
			fx1 = o.x + f1 * dir.x;
			fx2 = o.x + f2 * dir.x;
			fz1 = o.z + f1 * dir.z;
			fz2 = o.z + f2 * dir.z;
			fminz = min(fz1,fz2);
			fmaxz = max(fz1,fz2);
			xstart = max(0,floor(min(fx1,fx2)));
			xend = min(15,ceil(max(fx1,fx2)));
			if (xstart >= 16 || xend < 0) continue;
			for (x=xstart;x<=xend;x++) {
				h1 = pData[rowy+x];
				h2 = pData[rowy+x+1];
				h3 = pData[rowy+17+x];
				h4 = pData[rowy+17+x+1];
				if (h1 < fminz && h2 < fminz && h3 < fminz && h4 < fminz) continue;
				if (h1 > fmaxz && h2 > fmaxz && h3 > fmaxz && h4 > fmaxz) continue;
				
				for (k=0;k<2;k++)
				{
					// time for tile scan
					if (k == 0)
							n = pNorm[16*2*y+2*x+0];
					else	n = pNorm[16*2*y+2*x+1];

					if (k == 0)	p1 = vector3d(x+0,y+0,h1);
					else		p1 = vector3d(x+1,y+1,h4);
					
					c = dot(dir,n);
					d = dot(o - p1,n);
					// Parallel
					if (c == 0.0) continue;

					//  n � (ox - p1) = 0	// plane equation
					//  ox = t * u + o		// raypoint

					// => n � (t * u + o - p1) = 0
					// => n � (t * u) + n � (o - p1) = 0
					// => t * (n � u) + n � (o - p1) = 0
					// => t * c + d = 0

					t = - d / c;
					
					// plane intersection point
					hit = o + t * dir;
					
					dx = hit.x - (float)x;
					dy = hit.y - (float)y;

					// Point must be in triangle
					if (k == 0 && dx + dy > 1.0) continue;
					if (k == 1 && dx + dy < 1.0) continue;

					// Just check if point is in square and DONE =)

					if (floor(hit.x) != x) continue;
					if (floor(hit.y) != y) continue;
					//if (hit.z+0.001 < fminz) continue;
					//if (hit.z-0.001 > fmaxz) continue;

					if (!bfound || t <= founddist)
					{
						bfound = 1;
						founddist = t;
						//foundhit = hit;
						foundx = x;
						foundy = y;
					}
				} 

				if (0) {
					pVertexBuffer[0][rowy+x].z = h1 + 0.5;
					pVertexBuffer[0][rowy+x+1].z = h2 + 0.5;
					pVertexBuffer[0][rowy+17+x].z = h3 + 0.5;
					pVertexBuffer[0][rowy+17+x+1].z = h4 + 0.5;
					glColor3f(1,0,0);
					glBegin(GL_TRIANGLE_STRIP);
						glVertex3fv(&pVertexBuffer[0][rowy+x+1].x);
						glVertex3fv(&pVertexBuffer[0][rowy+x].x);
						glVertex3fv(&pVertexBuffer[0][rowy+17+x+1].x);
						glVertex3fv(&pVertexBuffer[0][rowy+17+x].x);
					glEnd();
				}
			}
		}

		res = founddist;
		return bfound;
	} else {
		for (x=xstart;x<=xend;x++) {
			f1 = (x - o.x)/dir.x;
			f2 = (x + 1.0 - o.x)/dir.x;
			fy1 = o.y + f1 * dir.y;
			fy2 = o.y + f2 * dir.y;
			fz1 = o.z + f1 * dir.z;
			fz2 = o.z + f2 * dir.z;
			fminz = min(fz1,fz2);
			fmaxz = max(fz1,fz2);
			ystart = max(0,floor(min(fy1,fy2)));
			yend = min(15,ceil(max(fy1,fy2)));
			if (ystart >= 16 || yend < 0) continue;
			for (y=ystart;y<=yend;y++) {
				rowy = 17*y;
				h1 = pData[rowy+x];
				h2 = pData[rowy+x+1];
				h3 = pData[rowy+17+x];
				h4 = pData[rowy+17+x+1];
				if (h1 < fminz && h2 < fminz && h3 < fminz && h4 < fminz) continue;
				if (h1 > fmaxz && h2 > fmaxz && h3 > fmaxz && h4 > fmaxz) continue;

				for (k=0;k<2;k++)
				{
					// time for tile scan
					if (k == 0)
							n = pNorm[16*2*y+2*x+0];
					else	n = pNorm[16*2*y+2*x+1];

					if (k == 0)	p1 = vector3d(x+0,y+0,h1);
					else		p1 = vector3d(x+1,y+1,h4);
					
					c = dot(dir,n);
					d = dot(o - p1,n);
					// Parallel
					if (c == 0.0) continue;

					//  n � (ox - p1) = 0	// plane equation
					//  ox = t * u + o		// raypoint

					// => n � (t * u + o - p1) = 0
					// => n � (t * u) + n � (o - p1) = 0
					// => t * (n � u) + n � (o - p1) = 0
					// => t * c + d = 0

					t = - d / c;
					
					// plane intersection point
					hit = o + t * dir;
					
					dx = hit.x - (float)x;
					dy = hit.y - (float)y;

					// Point must be in triangle
					if (k == 0 && dx + dy > 1.0) continue;
					if (k == 1 && dx + dy < 1.0) continue;

					// Just check if point is in square and DONE =)

					if (floor(hit.x) != x) continue;
					if (floor(hit.y) != y) continue;
					//if (hit.z+0.001 < fminz) continue;
					//if (hit.z-0.001 > fmaxz) continue;

					if (!bfound || t <= founddist)
					{
						bfound = 1;
						founddist = t;
						//foundhit = hit;
						foundx = x;
						foundy = y;
					}
				} 
			}
		}
		res = founddist;
		return bfound;
	}

	
	return false;
}

// ****** ****** ****** END
