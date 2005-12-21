// ****** ****** ****** renderengine.cpp
#include <renderengine.h>
#include <shell.h>
#include <sky.h>
#include <stdlib.h>

// for sdl main function
#include <SDL/SDL.h>
#include <os.h>

#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library

float	gfGLProjectionMatrix[16];
float	gfGLX,gfGLY,gfGLZ;
int iLastFrame;
int iLastFrameTime;
float fFPS;

void	CameraStep		();
float	gfLightPos[4] = {0,0,0,1};

cRenderEngine* cRenderEngine::pSingleton = 0;
cRenderEngine* cRenderEngine::instance ()
{
	if (!pSingleton) pSingleton = new cRenderEngine();
	return pSingleton;
}

cRenderEngine::cRenderEngine	()
{
	glDisable(GL_TEXTURE_2D);							// Enable Texture Mapping
	glHint(GL_PERSPECTIVE_CORRECTION_HINT, GL_FASTEST);	// Really Nice Perspective Calculations
	//glHint(GL_PERSPECTIVE_CORRECTION_HINT, GL_NICEST);	// Really Nice Perspective Calculations

	glDisable(GL_BLEND);
	glBlendFunc(GL_SRC_ALPHA,GL_ONE_MINUS_SRC_ALPHA);	// Set the blending function for Translucency
	
	glAlphaFunc(GL_GREATER,0.0);
	glEnable(GL_ALPHA_TEST);
	glEnable(GL_CULL_FACE);
	glCullFace(GL_FRONT);
	
	glDepthFunc(GL_LEQUAL);								// Depth Test Function
	glEnable(GL_DEPTH_TEST);							// Enables Depth Testing
	//glClearColor(1,1,1,1);
	//glClearColor(0.81,0.85,0.91,1);
	glClearColor(0,0,0,1);								// Clear to Black infinity
	glClearDepth(1.0);
	
	glEnable(GL_COLOR_MATERIAL);
	glEnable(GL_LIGHTING);								
	glEnable(GL_LIGHT1);
	glShadeModel(GL_SMOOTH);							// Enables Smooth Color Shading

	glLightfv(GL_LIGHT1,GL_POSITION,gfLightPos);
	float	lamb[] = {0.0,0.0,0.0,1.0};
	glLightfv(GL_LIGHT1,GL_AMBIENT,lamb);
	float	ldif[] = {0.8,0.8,0.8,1.0};
	glLightfv(GL_LIGHT1,GL_DIFFUSE,ldif);
	float	lspe[] = {1.0,1.0,1.0,1.0};
	glLightfv(GL_LIGHT1,GL_SPECULAR,lspe);
	float	lsceneamb[] = {0.6,0.6,0.6,1.0};
	glLightModelfv(GL_LIGHT_MODEL_AMBIENT,lsceneamb);
	
	// arrays are never used for anythign excep   color and vertex combos in this app
	glEnableClientState(GL_COLOR_ARRAY);
	glEnableClientState(GL_VERTEX_ARRAY);

	glViewport(0,0,gShell.iXRes,gShell.iYRes);

	glMatrixMode(GL_PROJECTION);
	glLoadIdentity();
	// gluOrtho2D(0,gShell.iXRes,gShell.iYRes,0); // left,top = 0,0
	gluPerspective(45,(float)gShell.iXRes/(float)gShell.iYRes,1,16*50);
	
	glGetFloatv(GL_PROJECTION_MATRIX,gfGLProjectionMatrix);

	gfGLX = 1.0/gfGLProjectionMatrix[0];
	gfGLY = 1.0/gfGLProjectionMatrix[5];
	gfGLZ = gfGLProjectionMatrix[10];

	glMatrixMode(GL_MODELVIEW);
	glLoadIdentity();

	iLastFrame = gShell.iClock;
	iLastFrameTime = 0;

	pLists = 0;
	iLists = 0;
	iMaxLists = 0;
}

cRenderEngine::~cRenderEngine	()
{
	if (pLists) free(pLists);
	pLists = 0;
	iLists = 0;
	iMaxLists = 0;
}

void	cRenderEngine::Swap	()
{
	// calcfps
	iLastFrameTime = gShell.iClock - iLastFrame;
	iLastFrame = gShell.iClock;
	if (iLastFrameTime == 0)
			fFPS = 0;
	else	fFPS = 1000.0 / iLastFrameTime;

	glFinish();
	SDL_GL_SwapBuffers();
} 


void	cRenderEngine::StartDrawing	()
{
	CameraStep();
	
	glClear(GL_COLOR_BUFFER_BIT|GL_DEPTH_BUFFER_BIT);

	cSky::instance()->Draw();

	//glClear(GL_DEPTH_BUFFER_BIT);
	//glClear(GL_COLOR_BUFFER_BIT|GL_DEPTH_BUFFER_BIT);
} 

void	cRenderEngine::DrawLists	()
{
	for (int j=0;j<kRenderListStages;j++)
	for (int i=0;i<iLists;i++)
		pLists[i]->Draw(j);
} 

void	cRenderEngine::AddList	(cRenderList* pList,vector3d vOff)
{
	// only growing structure (blockwise)
	if (++iLists > iMaxLists)
	{
		iMaxLists = ((iLists / 8) + 1) * 8;
		pLists = (cRenderList**)realloc(pLists,iMaxLists*sizeof(cRenderList*));
	}
	pList->vOff = vOff;
	pLists[iLists-1] = pList;
}

// ****** ****** ****** END