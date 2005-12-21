// ****** ****** ****** renderengine.h
#ifndef _RENDERENGINE_H_
#define _RENDERENGINE_H_

#include <geometry.h>
#include <color.h>

#define kRenderListStages	2

class cRoom;
class cObject;
class cRenderModel;
class cRenderList;
class cLinkedList;

// ****** ****** ****** cRenderEngine

class cRenderEngine {
public:
	static cRenderEngine* pSingleton;
	static cRenderEngine* instance ();

	cRenderEngine	();
	~cRenderEngine	();

	void	Swap			();
	void	StartDrawing	();

	cRenderList**	pLists;// only growing structure (blockwise)
	int				iLists;
	int				iMaxLists;
	void	AddList		(cRenderList* pList,vector3d vOff);
	void	DrawLists	();
};

// ****** ****** ****** cRenderList

class cRenderList {
public:
	cRoom*	pRoom;
	vector3d	vOff; // offset from current cam-room

	cLinkedList*	pList[kRenderListStages];//cModel

	cRenderList		(cRoom* pRoom);
	~cRenderList	();

	void	Draw	(const int iStage);
	void	reg		(cRenderModel* pModel);
	void	unreg	(cRenderModel* pModel);
};

// ****** ****** ****** cRenderModel

class cRenderModel {
public:
	cObject*	pObject;
	vector3d		vPos;
	bool		bBlending;
	
	cRenderModel	(cObject* pObject,vector3d vPos);
	~cRenderModel	();
	virtual void	release		();
	virtual void	Draw		();

	int		GetStage	();
};




#endif
// ****** ****** ****** end