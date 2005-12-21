#include "list.h"


cLinkedListNode::cLinkedListNode(void* pData,cLinkedListNode* pNext)
{
	this->pData = pData;
	this->pNext = pNext;
}

cLinkedListNode::~cLinkedListNode()
{
	pData = pNext = 0;
}


cLinkedList::cLinkedList()
{
	pFirst = 0;
	iSize = 0;
}

cLinkedList::~cLinkedList()
{
	cLinkedListNode *p = pFirst, *next = 0;
	while(p != 0)
	{
		next = p->pNext;
		delete(p);
		p = next;
	}
}

void cLinkedList::insert(void* pData)
{
	insertfirst(pData);
}

void cLinkedList::insertfirst(void* pData)
{
	cLinkedListNode *p = new cLinkedListNode(pData,0);
	
	if(pFirst == 0)pFirst = p;
	else
	{
		p->pNext = pFirst;
		pFirst = p;
	}

	++iSize;
}

void* cLinkedList::removefirst()
{
	if(pFirst != 0)
	{
		void *p = pFirst->pData;
		cLinkedListNode *first = pFirst;
		
		pFirst = pFirst->pNext;
		
		delete(first);		
		--iSize;
		return p;		
	}
	else return 0;
}

cListIterator* cLinkedList::iterator()
{
	return (cListIterator*) new cLinkedListIterator(this);
}

unsigned int cLinkedList::size()
{
	return iSize;
}

void cLinkedList::release()
{
	delete(this);
}


cLinkedListIterator::cLinkedListIterator(cLinkedList* pList)
{
	this->pList = pList;
	this->pCur = 0;
	this->pPrev = 0;
}

bool cLinkedListIterator::hasnext()
{
	return (pCur == 0 && pList->pFirst != 0) || (pCur != 0 && pCur->pNext != 0);
}

void* cLinkedListIterator::next()
{
	if(pCur == 0)
	{
		pCur = pList->pFirst;
		return pCur->pData;
	}
	else
	{
		if(hasnext())
		{
			pPrev = pCur;
			pCur = pCur->pNext;
			return pCur->pData;
		}
		else return 0;
	}
}

void cLinkedListIterator::insert(void* pData)
{
	if(pList->iSize == 0 || pCur == 0)
	{
		cLinkedListNode *node = new cLinkedListNode(pData,0);
		node->pNext = pList->pFirst;
		pList->pFirst = node;
		pCur = node;
		++(pList->iSize);
	}
	else
	{
		cLinkedListNode *node = new cLinkedListNode(pData,0);
		node->pNext = pCur->pNext;
		pCur->pNext = node;
		pCur = node;

		++(pList->iSize);
	}
}

void* cLinkedListIterator::remove()
{
	if(pList->iSize == 0 || pCur == 0) return 0;

	if(pPrev == 0) //erstes element entfernen
	{
		cLinkedListNode *node = pList->pFirst;
		void *data = node->pData;

		pList->removefirst();
		pCur = 0;

		return data;
	}
	else if(pCur->pNext == 0) //ende enfernen
	{
		cLinkedListNode *node = pCur;
		void *data = node->pData;
		delete(node);

		--(pList->iSize);
		pPrev->pNext = 0;
		pCur = pPrev;
		
		return data;	
	}
	else //aus der mitte entfernen
	{
		cLinkedListNode *node = pCur;
		void *data = node->pData;
		
		pPrev->pNext = pCur->pNext;
		delete(node);
		
		--(pList->iSize);
		pCur = pPrev;

		return data;
	}
}


void	cLinkedListIterator::set		(void* pData)
{
	// data vom zuletzt zurückgegebenen verändern
	pCur->pData = pData;
}

void cLinkedListIterator::release()
{
	delete(this);
}