// ****** ****** ****** camera.cpp
#include <geometry.h>
#include <shell.h>
#include <os.h>
#include <input.h>
#include <math.h>
#include <shell.h>
#include <robstring.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library



float	gCamSpeed = 0.8;

vector3d	gvDesiredCam = vector3d(0.0);
vector3d	gCamFrustum[6];

//vector3d	gvCampos = vector3d(0,-40,0);
//vector3d	gvCamang = vector3d(90,0,0);
//vector3d	gvCampos = vector3d(22.776520,16.125042,22.479088);
//vector3d	gvCamang = vector3d(62.099918,-31.499989,0.000000);
//vector3d	gvCampos = vector3d(39.375950,43.667969,16.595348);
//vector3d	gvCamang = vector3d(54.899853,-88.200157,0.000000);
//vector3d	gvCampos = vector3d(8.458716,0.883164,19.689291);
//vector3d	gvCamang = vector3d(54.299824,-269.700104,0.000000);
vector3d	gvCampos = vector3d(-9.570819,9.020008,36.730099);
vector3d	gvCamang = vector3d(38.399700,-1162.799072,0.000000);

float	gModelMat[16];
vector3d	gvX = vector3d(1,0,0);
vector3d	gvY = vector3d(0,1,0);
vector3d	gvZ = vector3d(0,0,1);

int		giInvertMouse = 0;


bool	gbFreecam = 1;

// ****** ****** ****** Init , Kill



float	gfCamdelay = 0.25;

// CameraStep
// desc : Captures keys to move cam, <return> to return to center,
//		  arrow keys for planar movement
//		  pageup and down for vertical movement
void	CameraStep		()
{
	float	speed;
	
	if (gInput.bKeys[cInput::kkey_f8])
	gShell.Append("cam.txt",*cString("%f,%f,%f | %f,%f,%f\n",
			gvCampos.x,gvCampos.y,gvCampos.z,gvCamang.x,gvCamang.y,gvCamang.z));

	// 22.776520,16.125042,22.479088 | 62.099918,-31.499989,0.000000
	// 39.375950,43.667969,16.595348 | 54.899853,-88.200157,0.000000

	/*
	// freecam toggle key
	static bool kp1 = 0;
	if (!keys['H']) kp1 = 0;
	if (keys['H'] && !kp1)
	{
		kp1 = 1;
		gbFreecam = !gbFreecam;
	}

	// autocam
	if (!gbFreecam)
	{
		gvCampos.x += 128*giCamSector[0];
		gvCampos.y += 128*giCamSector[1];
		giCamSector[0] = 0;
		giCamSector[1] = 0;
		gvCampos += (gvDesiredCam - gvCampos)*gfCamdelay;
	}
	*/

	// keys

	if (gInput.bKeys[cInput::kkey_f1]) gCamSpeed = 0.1;
	if (gInput.bKeys[cInput::kkey_f2]) gCamSpeed = 0.3;
	if (gInput.bKeys[cInput::kkey_f3]) gCamSpeed = 1.0;
	if (gInput.bKeys[cInput::kkey_f4]) gCamSpeed = 3.0;
	if (gInput.bKeys[cInput::kkey_f5]) gCamSpeed = 9.0;
	if (gInput.bKeys[cInput::kkey_f6]) gCamSpeed = 20.0;
	if (gInput.bKeys[cInput::kkey_f7]) gCamSpeed = 60.0;

	if (gInput.bKeys[cInput::kkey_return])
	{
		gCamSpeed = 1.0;
		gvCamang = vector3d(90,0,0);
		gvCampos = vector3d(0,-40,0);
	}


	// cam rotate , mouse steering

	static bool	cam_mp = 0;
	static int	cam_offset[2] = {0,0};

	if (gbFreecam)
	{
		int		pos[2];
		if (0) // fullscreen
		{
			pos[0] = gInput.iMouse[0] - gShell.iXRes/2;
			pos[1] = gInput.iMouse[1] - gShell.iYRes/2;
			// invert mouse y
			//if (giInvertMouse) pos[1] = -pos[1];
			gvCamang[2] -= pos[0] / 100.0 * 30.0;
			gvCamang[0] -= pos[1] / 100.0 * 30.0;
			// recenter cursor
			#ifdef WIN32
			SetCursorPos(gShell.iXRes/2,gShell.iYRes/2);
			#endif
		} else {
			if (!gInput.bButton[0]) cam_mp = 0;
			if (gInput.bButton[0] && !cam_mp)
			{	// mouse just down
				cam_mp = 1;
				cam_offset[0] = gInput.iMouse[0];
				cam_offset[1] = gInput.iMouse[1];
			}

			//if (gInput.bButton[0] && gMouseTracker == kMouseTracker_Free)
			if (gInput.bButton[0])
			{
				pos[0] = gInput.iMouse[0] - cam_offset[0];
				pos[1] = gInput.iMouse[1] - cam_offset[1];
				cam_offset[0] = gInput.iMouse[0];
				cam_offset[1] = gInput.iMouse[1];
				// invert mouse y
				//if (giInvertMouse) pos[1] = -pos[1];

				gvCamang[1] -= pos[0] / 100.0 * 30.0;
				gvCamang[0] -= pos[1] / 100.0 * 30.0;
			}
		}
	}
//*/

	if (gInput.bKeys[cInput::kkey_lshift] || gInput.bKeys[cInput::kkey_rshift])
			speed = gCamSpeed * 0.3;
	else	speed = gCamSpeed;


	// cam move
	if (1)
	{
		if (gInput.bKeys['D'] || gInput.bKeys[cInput::kkey_right])	gvCampos += gvX*speed;
		if (gInput.bKeys['A'] || gInput.bKeys[cInput::kkey_left])	gvCampos -= gvX*speed;
		if (gInput.bKeys['W'] || gInput.bKeys[cInput::kkey_up])		gvCampos -= gvZ*speed;
		if (gInput.bKeys['S'] || gInput.bKeys[cInput::kkey_down])	gvCampos += gvZ*speed;
		if (gInput.bKeys['F'] || gInput.bKeys[cInput::kkey_next])	gvCampos -= gvY*speed;
		if (gInput.bKeys['R'] || gInput.bKeys[cInput::kkey_prior])	gvCampos += gvY*speed;
	}


	// adjust sector



	// transform
	glLoadIdentity();
	glRotatef(-gvCamang.x , 1,0,0);
	glRotatef(-gvCamang.y , 0,0,1);
	glTranslatef(-gvCampos[0],-gvCampos[1],-gvCampos[2]);


	// get axis
	glGetFloatv(GL_MODELVIEW_MATRIX,gModelMat);
	gvX.x = gModelMat[ 0];
	gvX.y = gModelMat[ 4];
	gvX.z = gModelMat[ 8];

	gvY.x = gModelMat[ 1];
	gvY.y = gModelMat[ 5];
	gvY.z = gModelMat[ 9];

	gvZ.x = gModelMat[ 2];
	gvZ.y = gModelMat[ 6];
	gvZ.z = gModelMat[10];
}





// ****** ****** ****** END
