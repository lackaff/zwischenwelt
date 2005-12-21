// ****** ****** ****** imageman.cpp
#include <media.h>
#include <shell.h>
#include <robstring.h>
#include <SDL/SDL_image.h>


// ****** ****** ****** cM_Image


void	cM_Image::Load	(const char* path)
{
	if (pData)
		SDL_FreeSurface((SDL_Surface*)pData);
	pData = 0;

	SDL_Surface*	pMySurface;
	SDL_Surface*	pMySurface2;
	SDL_PixelFormat	fmt;

	// LOAD the image

	pMySurface = IMG_Load(path);

	if (pMySurface == NULL)
	{
		printf("%s !!! FAILED, couldn't load !!! (cM_Image)\n",path);
		return;
	}

	if (pMySurface->format->BytesPerPixel != 1 &&
		pMySurface->format->BytesPerPixel != 3 &&
		pMySurface->format->BytesPerPixel != 4)
	{
		printf("%s !!! FAILED, wrong format !!! (cM_Image)\n",path);
		return;
	}

	// adjust format to either RGB 3 byte 24bit, or RGBA 4 byte 32 bit

	fmt.palette = NULL;

	fmt.Rmask = 0x000000ff;
	fmt.Gmask = 0x0000ff00;
	fmt.Bmask = 0x00ff0000;

	fmt.Rshift = 0;
	fmt.Gshift = 8;
	fmt.Bshift = 16;

	fmt.Rloss = 0;
	fmt.Gloss = 0;
	fmt.Bloss = 0;

	fmt.colorkey = 0;
	fmt.alpha = 0;

	if (pMySurface->format->BytesPerPixel <= 3)
	{
		fmt.BytesPerPixel = 3;
		fmt.BitsPerPixel = 24;
		fmt.Amask = 0x00000000;
		fmt.Ashift = 0;
		fmt.Aloss = 8;
	}

	if (pMySurface->format->BytesPerPixel == 4)
	{
		fmt.BytesPerPixel = 4;
		fmt.BitsPerPixel = 32;
		fmt.Amask = 0xff000000;
		fmt.Ashift = 24;
		fmt.Aloss = 0;
	}

	// convert only if neccessary
	if (fmt.Rmask != pMySurface->format->Rmask ||
		fmt.Gmask != pMySurface->format->Gmask ||
		fmt.Bmask != pMySurface->format->Bmask ||
		fmt.Amask != pMySurface->format->Amask ||

		fmt.Rshift != pMySurface->format->Rshift ||
		fmt.Gshift != pMySurface->format->Gshift ||
		fmt.Bshift != pMySurface->format->Bshift ||
		fmt.Ashift != pMySurface->format->Ashift ||

		fmt.Rloss != pMySurface->format->Rloss ||
		fmt.Gloss != pMySurface->format->Gloss ||
		fmt.Bloss != pMySurface->format->Bloss ||
		fmt.Aloss != pMySurface->format->Aloss ||
		
		fmt.BytesPerPixel != pMySurface->format->BytesPerPixel ||
		fmt.BitsPerPixel != pMySurface->format->BitsPerPixel
		)
	{
		cString myout = "image converted\n";

		if (fmt.Rmask != pMySurface->format->Rmask) myout.Appendf(" Rmask");
		if (fmt.Gmask != pMySurface->format->Gmask) myout.Appendf(" Gmask");
		if (fmt.Bmask != pMySurface->format->Bmask) myout.Appendf(" Bmask");
		if (fmt.Amask != pMySurface->format->Amask) myout.Appendf(" Amask");

		if (fmt.Rshift != pMySurface->format->Rshift) myout.Appendf(" Rshift");
		if (fmt.Gshift != pMySurface->format->Gshift) myout.Appendf(" Gshift");
		if (fmt.Bshift != pMySurface->format->Bshift) myout.Appendf(" Bshift");
		if (fmt.Ashift != pMySurface->format->Ashift) myout.Appendf(" Ashift");

		if (fmt.Rloss != pMySurface->format->Rloss) myout.Appendf(" Rloss");
		if (fmt.Gloss != pMySurface->format->Gloss) myout.Appendf(" Gloss");
		if (fmt.Bloss != pMySurface->format->Bloss) myout.Appendf(" Bloss");
		if (fmt.Aloss != pMySurface->format->Aloss) myout.Appendf(" Aloss");
			
		if (fmt.BytesPerPixel != pMySurface->format->BytesPerPixel) myout.Appendf(" BytesPerPixel");
		if (fmt.BitsPerPixel != pMySurface->format->BitsPerPixel) myout.Appendf(" BitsPerPixel");

		gShell.Write("media.txt",*myout);

		pMySurface2 = pMySurface;
		pMySurface = SDL_ConvertSurface(pMySurface2,&fmt,SDL_SWSURFACE);
		SDL_FreeSurface(pMySurface2);

		if (pMySurface == NULL)
		{
			printf("%s !!! FAILED, couldn't convert !!! (cM_Image)\n",path);
			return;
		}
	}

	iDataSize = pMySurface->format->BytesPerPixel * pMySurface->w * pMySurface->h;
	pData = pMySurface;
}


void	cM_Image::GetAverageRGBA	(char* rgba)
{
	if (!pData) return;

	SDL_Surface*	pMySurface = (SDL_Surface*)pData;
	SDL_LockSurface(pMySurface);

	// sdl surface is complete
	// now load get the average

	int	x,y;
	float sig,tsig;
	float res[4] = {0,0,0,0};
	int interval = max(1,pMySurface->w / 32);
	unsigned char* pix;

	//fpx = 1.0 / (pMySurface->w * pMySurface->h);

	tsig = 0.0;

	if (pMySurface->format->BytesPerPixel == 3)
	{
		for (y=0;y<pMySurface->h;y+=interval)
		for (x=0;x<pMySurface->w;x+=interval)
		{
			pix = (unsigned char*)pMySurface->pixels + y*pMySurface->w*3 + x*3;
			sig = pix[0] * 2.0 + pix[1] * 3.0 + pix[2] * 0.5;
			/*
			bright = pix[0]*pix[0] + pix[1]*pix[1] + pix[2]*pix[2];
			bright /= 256*256 + 256*256 + 256*256;
			bright = 1.0 - abs(bright - 0.5) / 0.5;
			sig *= bright*bright*bright;
			*/
			tsig += sig;

			res[0] += (float)pix[0] * sig;
			res[1] += (float)pix[1] * sig;
			res[2] += (float)pix[2] * sig;
		}
	}

	if (pMySurface->format->BytesPerPixel == 4)
	{
		for (y=0;y<pMySurface->h;y+=interval)
		for (x=0;x<pMySurface->w;x+=interval)
		{
			pix = (unsigned char*)pMySurface->pixels + y*pMySurface->w*4 + x*4;
			sig = pix[0] * 2.0 + pix[1] * 3.0 + pix[2] * 0.5;
			/*
			bright = pix[0]*pix[0] + pix[1]*pix[1] + pix[2]*pix[2];
			bright /= 256*256 + 256*256 + 256*256;
			bright = 1.0 - abs(bright - 0.5) / 0.5;
			sig *= bright*bright*bright;
			*/
			tsig += sig;

			res[0] += (float)pix[0] * sig;
			res[1] += (float)pix[1] * sig;
			res[2] += (float)pix[2] * sig;
			res[3] += (float)pix[3] * sig;
		}
	}

	rgba[0] = (unsigned char)(res[0] / tsig);
	rgba[1] = (unsigned char)(res[1] / tsig);
	rgba[2] = (unsigned char)(res[2] / tsig);
	rgba[3] = (unsigned char)(res[3] / tsig);

	SDL_UnlockSurface(pMySurface);
}


void	cM_Image::GetAverageRGB		(char* rgb)
{
	char rgba[4] = {0,0,0,0};
	GetAverageRGBA(rgba);

	rgb[0] = (unsigned char)rgba[0];
	rgb[1] = (unsigned char)rgba[1];
	rgb[2] = (unsigned char)rgba[2];
}


// ****** ****** ****** cImageManager


// singleton
cImageManager*	cImageManager::getinstance	()
{
	static cImageManager* i = 0;
	if (!i) i = new cImageManager();
	return i;
}


cM_Image*		cImageManager::get		(const char* path)
{
	cM_Image* pMedia;
	pMedia = (cM_Image*)pMap->get(path);
	if (!pMedia)
	{
		pMedia = new cM_Image();
		pMedia->Load(path);
		pMap->insert(pMedia,path);
	}
	return pMedia;
}


// ****** ****** ****** END
