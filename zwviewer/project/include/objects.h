// ****** ****** ****** objects.h
#ifndef _OBJECTS_H_
#define _OBJECTS_H_

#include <geometry.h>

class cIntegerHashMap;
class cObject;
class cRoom;

// ****** ****** ****** cObjectManager

class cObjectManager {
public:
	int					iLastID;
	cIntegerHashMap*	pMap;

	cObjectManager		();
	cObject*	get		(const int iID);
	void		reg		(cObject* pObj);
	void		unreg	(cObject* pObj);
	void		unreg	(const int iID);

	static cObjectManager*	pSingleton;
	static cObjectManager*	instance ();
};

// ****** ****** ****** cObject

class cObject {
public:
	vector3d		vPos;
	quaternion	qRot;
	float		fVisRad;
	int			iID;
	cRoom*		pRoom;
	int			iInterval; // -1 : inaktive, 0-11, see game.h

	cObject		(vector3d vPos,cRoom* pRoom);
	~cObject	();
	virtual void	release	();

	virtual void	Step	();
};

class cBall : public cObject {
public:
	float	fRad;
	cBall	(vector3d vPos,cRoom* pRoom,float fRad);
	virtual void	release	();
};

#endif
// ****** ****** ****** end