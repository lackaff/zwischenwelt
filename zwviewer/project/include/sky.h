// ****** ****** ****** sky.h
#ifndef _SKY_H_
#define _SKY_H_

class cM_Texture;

class cSky {
public:
	static cSky*	pSingleton;
	static cSky*	instance ();

	cM_Texture*		pSkyTex;

	cSky	();
	~cSky	();

	void	Draw	();
};

#endif
// ****** ****** ****** end