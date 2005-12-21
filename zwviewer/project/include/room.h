// ****** ****** ****** room.h
#ifndef _ROOM_H_
#define _ROOM_H_

#include <geometry.h>

class cLinkedList;
class cRenderList;
class cString;
class cPortal;
class cObject;
class cRoom;


// ****** ****** ****** cRoom

class cRoom {
public:
	cRenderList*	pRenderList;
	cLinkedList*	pObjects;
	cLinkedList*	pPortals;
	bool			bSky;
	int				iLastDraw;
	int				iLastOffsetSearch;
	cString*		pName;
	vector			vMin;
	vector			vMax;

	cRoom	(const char* name = 0);
	~cRoom	();
	virtual void	release	();

	void	reg		(cObject* pObj);
	void	unreg	(cObject* pObj);
	void	Draw	();
	void	Draw	(vector vOff);

	virtual vector	GetOffset	(cRoom* pRoom);
	
	static int iGlobalOffsetSearch;
	static int iGlobalDraw;
	bool	FindOffset	(cRoom* pRoom,vector& vOff);
};

// ****** ****** ****** cPortal

class cPortal : public cRoom {
public:
	cRoom*	pRoom1;
	cRoom*	pRoom2;
	vector	vPos1;
	vector	vPos2;
	bool	bOpen;
	
	cPortal		(cRoom* pRoom1,cRoom* pRoom2,vector vPos1,vector vPos2);
	~cPortal	();
	virtual void	release	();

	void	Draw	(cRoom* pSource,vector vOff);
	virtual vector	GetOffset	(cRoom* pRoom);
};

#endif
// ****** ****** ****** end