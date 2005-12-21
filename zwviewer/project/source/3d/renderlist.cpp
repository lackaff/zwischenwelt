// ****** ****** ****** renderlist.cpp
#include <renderengine.h>
#include <list.h>

cRenderList::cRenderList	(cRoom* pRoom) : pRoom(pRoom)
{
	vOff = vector3d(0.0);
	pList[0] = new cLinkedList();
	pList[1] = new cLinkedList();
}

cRenderList::~cRenderList	()
{
	if (pList[0]) delete pList[0];
	pList[0] = 0;
	if (pList[1]) delete pList[1];
	pList[1] = 0;
}

void	cRenderList::Draw	(const int iStage)
{
	if (iStage < 0 || iStage >= kRenderListStages) return;
	cIterator* i = pList[iStage]->iterator();
	while (i->hasnext())
		((cRenderModel*)i->next())->Draw();
	i->release();
}

void	cRenderList::reg	(cRenderModel* pModel)
{
	pList[pModel->GetStage()]->insert(pModel);
}

void	cRenderList::unreg	(cRenderModel* pModel)
{
	pList[pModel->GetStage()]->remove(pModel);
}


// ****** ****** ****** END