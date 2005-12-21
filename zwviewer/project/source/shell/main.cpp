// ****** ****** ****** main.cpp
//#include <wx/wx.h>
#include <shell.h>
#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <input.h>
#include <buffer.h>
#include <net.h>
#include <list.h>
#include <map.h>
#include <renderengine.h>
#include <roblib.h>

// for sdl main function
#include <SDL/SDL.h>
#include <os.h>

//#include <SDL/SDL_opengl.h>
#include <GL/glew.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library

//#include <ARB_multitexture_extension.h>
//#include <ARB_texture_cube_map_extension.h>
//#include <ARB_texture_env_combine_extension.h>
//#include <ARB_texture_env_dot3_extension.h>

// lib for wx : wxmswd.lib wxmsw.lib 

//#include <boost/regex.hpp>

#include "audio.h"

//extern wxTextCtrl *m_pTextCtrl;



extern float	gfGLX,gfGLY,gfGLZ; 
extern vector3d	gvCampos;
extern vector3d	gvX,gvY,gvZ;
extern float fFPS; 
extern vector3d	gCamFrustum[6]; 

// ****** ****** ****** mainproc



int		main	(int argc,char *argv[]) {
	gShell.Init();
	//gShell.iArgC = 0;
	//gShell.szArgV = 0;
	gShell.iArgC = argc;
	gShell.szArgV = argv;
	
	
	gShell.SetProgramCaption("ZWViewer");
	gShell.SetVideo(640,480,32,false);

	
	//Check for and set up extensions
	GLenum err = glewInit();
	if (GLEW_OK != err) {
		/* problem: glewInit failed, something is seriously wrong */
		fprintf(stderr, "Error: %s\n", glewGetErrorString(err));
		return -1;
	}

	int mytexstages;
	const unsigned char* glext = glGetString(GL_EXTENSIONS);
	glGetIntegerv(GL_MAX_TEXTURE_UNITS_ARB,&mytexstages);
	gShell.Write("glext.txt",*cString("%s\n%i",glext,mytexstages));
	
	cRenderEngine* pRenderEngine = cRenderEngine::instance();
	cMap* gMap = cMap::instance();
	//gMap->Init("../map.dat","../gfx","data/smooth_hills.png",-62,165);
	gMap->Init("../map.dat","../gfx","data/smooth_hills.png",-28,210);
	
	vector3d v,o,u;
	float mx,my;
	glCullFace(GL_BACK); // TODO : remove

	while (gShell.bAlive) {
		
		gShell.EventLoopStep();
		
		// be a little cooperative while not in fullscreen
		usleep(10*1000);
		
		// TODO : camera setzen
		pRenderEngine->StartDrawing();
		
		// 3dmouse
		mx = (((float)gInput.iMouse[0]/(float)gShell.iXRes) * 2.0 - 1.0)*gfGLX;
		my = (((float)gInput.iMouse[1]/(float)gShell.iYRes) * 2.0 - 1.0)*gfGLY;
		u =	- gvZ + gvX*mx - gvY*my;
		o = gvCampos + u;
		u = norm(u)*512.0;
		
		if (1) {
			// draw 3d mouse
			glBegin(GL_LINES);
			glColor3f(1,1,0);
			v = o + gvX;glVertex3fv(&v.x);
			v = o + u + gvX;glVertex3fv(&v.x);
			v = o + gvY;glVertex3fv(&v.x);
			v = o + u + gvY;glVertex3fv(&v.x);
			glEnd();
		}

		if (!gInput.bKeys[cInput::kkey_x]) {
			// calc frustum
			gCamFrustum[0] = gvZ; // normal for near plane is z vector
			// top bot left right
			gCamFrustum[1] = norm( cross( + gvX , - gvZ + gvY*gfGLY ) );
			gCamFrustum[2] = norm( cross( - gvX , - gvZ - gvY*gfGLY ) );
			gCamFrustum[3] = norm( cross( + gvY , - gvZ - gvX*gfGLX ) );
			gCamFrustum[4] = norm( cross( - gvY , - gvZ + gvX*gfGLX ) );
			gCamFrustum[5] = gvCampos;
		}

		if (1) {
			// draw 3d mouse
			glBegin(GL_LINES);
			glColor3f(1,1,0);
			v = o + gvX;glVertex3fv(&v.x);
			v = o + u + gvX;glVertex3fv(&v.x);
			v = o + gvY;glVertex3fv(&v.x);
			v = o + u + gvY;glVertex3fv(&v.x);
			glEnd();
		}

		gMap->Draw(o,u,gvCampos);

		pRenderEngine->Swap();
	}

	return 1;
}





// ****** ****** ****** end
