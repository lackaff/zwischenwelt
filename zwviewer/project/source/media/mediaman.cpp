// ****** ****** ****** mediaman.cpp
#include <media.h>
#include <stdio.h>
#include <stdlib.h>


// ****** ****** ****** cMedia


cMedia::cMedia	()
{
	pData = 0;
	iLockCount = 0;
	iType = 0;
	iLastUse = 0;
	iDataSize = 0;
}

cMedia::~cMedia	()
{
	UnLoad();
}

// locking // what & who are only for debug
void	cMedia::lock	(const char* what,const int who)
{
	iLockCount++;
}

void	cMedia::unlock	(const char* what,const int who)
{
	iLockCount--;
}

void	cMedia::Load	(const char* path)
{
	if (pData) free(pData);
	pData = 0;

	FILE* fp = fopen(path,"rb");
	if (!fp) return;

	fseek(fp,0,SEEK_END);
	iDataSize = ftell(fp);
	pData = malloc(iDataSize);
	fseek(fp,0,SEEK_SET);
	fread(pData,1,iDataSize,fp);
	fclose(fp);
}

void	cMedia::UnLoad	()
{
	if (pData) free(pData);
	pData = 0;
	iDataSize = 0;
}


// ****** ****** ****** cMediaManager


cMediaManager::cMediaManager	()
{
	pMap = new cStringHashMap(16);
}

cMediaManager::~cMediaManager	()
{
	// bug ! never called... (because of singleton)
	// delete all content
	delete pMap;
	pMap = 0;
}

// singleton
cMediaManager*	cMediaManager::getinstance	()
{
	static cMediaManager* i = 0;
	if (!i) i = new cMediaManager();
	return i;
}

cMedia*			cMediaManager::get		(const char* path)
{
	// use base mediaman to load misc files ?!?
	cMedia* pMedia;
	pMedia = (cMedia*)pMap->get(path);
	if (!pMedia)
	{
		pMedia = new cMedia();
		pMedia->Load(path);
		pMap->insert(pMedia,path);
	}
	return pMedia;
}


// ****** ****** ****** END
