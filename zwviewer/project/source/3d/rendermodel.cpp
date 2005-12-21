// ****** ****** ****** rendermodel.cpp
#include <renderengine.h>
#include <drawutils.h>
#include <objects.h>

	
cRenderModel::cRenderModel	(cObject* pObject,vector3d vPos)
: pObject(pObject) , vPos(vPos)
{
	bBlending = 0;
}

cRenderModel::~cRenderModel	()
{
	pObject = 0;
}

void	cRenderModel::release	()
{ delete this; }

void	cRenderModel::Draw		()
{
	// this renderobject is just a base class, should not be used
	DrawSphere(pObject->vPos + vPos,vector3d(1,0,0),pObject->fVisRad);
}

int		cRenderModel::GetStage	()
{
	if (bBlending) return 1;
	return 0;
}

// ****** ****** ****** END