// ****** ****** ****** textureman.cpp
#include <media.h>
#include <SDL/SDL_image.h>
#include <os.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library


// ****** ****** ****** cM_Grafik


void	cM_Texture::Load	(const char* path)
{
	if (pData)
		glDeleteTextures(1,(unsigned int*)&pData);
	pData = 0;
	glGenTextures(1,(unsigned int*)&pData);

	pBase = cImageManager::getinstance()->get(path);
	pBase->lock("texture",0);
	
	SDL_Surface* pMySurface = (SDL_Surface*)pBase->pData;
	if (!pMySurface) return;

	SDL_LockSurface(pMySurface);

	glBindTexture(GL_TEXTURE_2D,(unsigned int)pData);

	if (pMySurface->format->BytesPerPixel == 1)
		gluBuild2DMipmaps(GL_TEXTURE_2D, 1, pMySurface->w, pMySurface->h, GL_LUMINANCE , GL_UNSIGNED_BYTE, pMySurface->pixels);
	
	if (pMySurface->format->BytesPerPixel == 2)
		gluBuild2DMipmaps(GL_TEXTURE_2D, 2, pMySurface->w, pMySurface->h, GL_LUMINANCE_ALPHA , GL_UNSIGNED_BYTE, pMySurface->pixels);
	
	if (pMySurface->format->BytesPerPixel == 3)
		gluBuild2DMipmaps(GL_TEXTURE_2D, 3, pMySurface->w, pMySurface->h, GL_RGB , GL_UNSIGNED_BYTE, pMySurface->pixels);
	
	if (pMySurface->format->BytesPerPixel == 4)
		gluBuild2DMipmaps(GL_TEXTURE_2D, 4, pMySurface->w, pMySurface->h, GL_RGBA, GL_UNSIGNED_BYTE, pMySurface->pixels);

	//glTexImage2D(GL_TEXTURE_2D, 0, 4, textureImage->sizeX, textureImage->sizeY, 0, GL_RGBA, GL_UNSIGNED_BYTE, textureImage->data);
	glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MAG_FILTER,GL_LINEAR);
	glTexParameteri(GL_TEXTURE_2D,GL_TEXTURE_MIN_FILTER,GL_LINEAR_MIPMAP_NEAREST);

	SDL_UnlockSurface(pMySurface);
	//SDL_FreeSurface(pMySurface);

	iDataSize = pBase->iDataSize;
}

void	cM_Texture::Bind	()
{
	glBindTexture(GL_TEXTURE_2D,(unsigned int)pData);
}

// ****** ****** ****** cGraficManager


// singleton
cTextureManager*	cTextureManager::getinstance	()
{
	static cTextureManager* i = 0;
	if (!i) i = new cTextureManager();
	return i;
}


cM_Texture*		cTextureManager::get		(const char* path)
{
	// use base mediaman to load misc files ?!?
	cM_Texture* pMedia;
	pMedia = (cM_Texture*)pMap->get(path);
	if (!pMedia)
	{
		pMedia = new cM_Texture();
		pMedia->Load(path);
		pMap->insert(pMedia,path);
	}
	return pMedia;
}


// ****** ****** ****** END
