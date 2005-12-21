// ****** ****** ****** media.h
#ifndef _MEDIA_H_
#define _MEDIA_H_

#include <prefix.h>
#include <list.h>


// ****** ****** ****** cMedia


class cMedia {
public:
	cMedia	();
	~cMedia	();

	// locking // what & who are only for debug
	void	lock	(const char* what,const int who);
	void	unlock	(const char* what,const int who);
	void	Load	(const char* path); // make virtual ?
	void	UnLoad	(); // make virtual ?
	
	// serves as proxy
	void*	pData;
	int		iLockCount;
	int		iType;
	int		iLastUse;
	int		iDataSize;
};


// ****** ****** ****** cMediaManager


class cMediaManager {
public :
	cMediaManager	();
	~cMediaManager	();

	cStringHashMap*	pMap;

	// singleton
	static cMediaManager* getinstance	();
	cMedia*	get		(const char* path);
};


// ****** ****** ****** cImageManager


class cM_Image : public cMedia {
public :
	void	Load				(const char* path);
	void	GetAverageRGBA		(char* rgba);
	void	GetAverageRGB		(char* rgb);
};

class cImageManager : public cMediaManager {
public :
	// singleton
	static cImageManager*	getinstance();
	cM_Image*	get	(const char* path);
};


// ****** ****** ****** cTextureManager


class cM_Texture : public cMedia {
public :
	cM_Image*	pBase;
	void	Load	(const char* path);
	void	Bind	();
};

class cTextureManager : public cMediaManager {
public :
	// singleton
	static cTextureManager*	getinstance();
	cM_Texture*	get	(const char* path);
};


// ****** ****** ****** cGraficManager


class cM_Grafik : public cMedia {
public :
	cM_Image*	pBase;
	void	Load	(const char* path);
	void	Bind	();
};

class cGraficManager : public cMediaManager {
public :
	// singleton
	static cGraficManager*	getinstance();
	cM_Grafik*	get	(const char* path);
};


#endif
// ****** ****** ****** END
