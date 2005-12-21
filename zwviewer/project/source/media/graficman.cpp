// ****** ****** ****** graficman.cpp
#include <media.h>
#include <SDL/SDL_image.h>
#include <os.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library


// ****** ****** ****** cM_Grafik


void	cM_Grafik::Load	(const char* path)
{
	if (pData)
		glDeleteTextures(1,(unsigned int*)&pData);
	pData = 0;
	glGenTextures(1,(unsigned int*)&pData);

	pBase = cImageManager::getinstance()->get(path);
	pBase->lock("grafic",0);
	
	
	
	SDL_Surface* pMySurface = (SDL_Surface*)pBase->pData;
	if (!pMySurface) return;
	SDL_LockSurface(pMySurface);

	if (0)
	{
		// ancient code, not reliable, switch 4/3 bytes per pixel
		unsigned char*	pixel;
		int				x,y;

		// blue effekt -> alpha + turn black
		for (y=0;y<pMySurface->h;y++)
		for (x=0;x<pMySurface->w;x++)
		{
			pixel = (unsigned char*)pMySurface->pixels + x*pMySurface->h*4 + y*4;
			if (pixel[0] == 0 && pixel[1] == 0 && pixel[2] == 255)
			{
				//pixel[0] = 0;
				//pixel[1] = 0;
				pixel[2] = 0; // set to black
				pixel[3] = 0; // 0 visibility
			}
		}
	}

	glBindTexture(GL_TEXTURE_2D,(unsigned int)pData);

	
	if (pMySurface->format->BytesPerPixel == 3)
		gluBuild2DMipmaps(GL_TEXTURE_2D, 3, pMySurface->w, pMySurface->h, GL_RGB , GL_UNSIGNED_BYTE, pMySurface->pixels);
	
	if (pMySurface->format->BytesPerPixel == 4)
		gluBuild2DMipmaps(GL_TEXTURE_2D, 4, pMySurface->w, pMySurface->h, GL_RGBA, GL_UNSIGNED_BYTE, pMySurface->pixels);
	
	//glTexImage2D(GL_TEXTURE_2D, 0, 4, pMySurface->w, pMySurface->h, 0, GL_RGBA, GL_UNSIGNED_BYTE, pMySurface->pixels);
	glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MAG_FILTER,GL_NEAREST);
	glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MIN_FILTER,GL_NEAREST);//GL_LINEAR

	SDL_UnlockSurface(pMySurface);
	//SDL_FreeSurface(pMySurface);

	iDataSize = pBase->iDataSize;
}

void	cM_Grafik::Bind	()
{
	glBindTexture(GL_TEXTURE_2D,(unsigned int)pData);
}


// ****** ****** ****** cGraficManager


// singleton
cGraficManager*	cGraficManager::getinstance	()
{
	static cGraficManager* i = 0;
	if (!i) i = new cGraficManager();
	return i;
}


cM_Grafik*		cGraficManager::get		(const char* path)
{
	// use base mediaman to load misc files ?!?
	cM_Grafik* pMedia;
	pMedia = (cM_Grafik*)pMap->get(path);
	if (!pMedia)
	{
		pMedia = new cM_Grafik();
		pMedia->Load(path);
		pMap->insert(pMedia,path);
	}
	return pMedia;
}


// ****** ****** ****** END
