// ****** ****** ****** doublelinkedlist.cpp
#include <list.h>
#include <assert.h>




// ****** ****** ****** cDoubleLinkedListNode

	cDoubleLinkedListNode::cDoubleLinkedListNode	(void* pData,cDoubleLinkedListNode* pPrev,cDoubleLinkedListNode* pNext)
	{
		this->pData = pData;
		this->pPrev = pPrev;
		this->pNext = pNext;
	}

	cDoubleLinkedListNode::~cDoubleLinkedListNode	()
	{
		pData = pNext = pPrev = 0;
	}

// ****** ****** ****** cDoubleLinkedList

	cDoubleLinkedList::cDoubleLinkedList	()
	{
		pFirst = 0;
		pLast = 0;
		iSize = 0;
	}

	cDoubleLinkedList::~cDoubleLinkedList	()
	{
		// TODO : improve me
		while (pFirst) removefirst();
		iSize = 0;
	}

	void	cDoubleLinkedList::release		()
	{
		delete this;
	}

	unsigned int	cDoubleLinkedList::size		()
	{
		return iSize;
	}

	void	cDoubleLinkedList::insert		(void* pData)
	{
		insertfirst(pData);
	}

	void	cDoubleLinkedList::insertfirst	(void* pData)
	{
		// am anfang anhängen
		pFirst = new cDoubleLinkedListNode(pData,0,pFirst);
		if (pFirst->pNext) pFirst->pNext->pPrev = pFirst;
		if (!pLast) pLast = pFirst;
		++iSize;
	}

	void	cDoubleLinkedList::insertlast	(void* pData)
	{
		// am ende anhängen
		pLast = new cDoubleLinkedListNode(pData,pLast,0);
		if (pLast->pPrev) pLast->pPrev->pNext = pLast;
		if (!pFirst) pFirst = pLast;
		++iSize;
	}

	void*	cDoubleLinkedList::removefirst	()
	{
		// erstes killen
		if (!pFirst) return 0;
		--iSize;
		void* ret = pFirst->pData;


		if (pFirst == pLast)
		{
			delete pFirst;
			pFirst = 0;
			pLast = 0;
			return ret;
		} else {
			pFirst = pFirst->pNext;
			delete pFirst->pPrev;
			pFirst->pPrev = 0;
			return ret;
		}	
	}

	void*	cDoubleLinkedList::removelast	()
	{
		// letztes killen
		if (!pLast) return 0;
		--iSize;
		void* ret = pLast->pData;
		
		if (pFirst == pLast)
		{
			delete pFirst;
			pFirst = 0;
			pLast = 0;
			return ret;
		} else {
			pLast = pLast->pPrev;
			delete pLast->pNext;
			pLast->pNext = 0;
			return ret;
		}
	}

	cListIterator*	cDoubleLinkedList::iterator	()
	{
		return (cListIterator*)new cDoubleLinkedListIterator(this);
	}


// ****** ****** ******  cDoubleLinkedListIterator



	cDoubleLinkedListIterator::cDoubleLinkedListIterator(cDoubleLinkedList* pList)
	{
		this->pList = pList;
		pNext = pList->pFirst;
		pPrev = 0;
		pLast = 0;
	}

	cDoubleLinkedListIterator::~cDoubleLinkedListIterator()
	{
		pList = 0;
		pNext = 0;
		pPrev = 0;
		pLast = 0;
	}
	
	void 	cDoubleLinkedListIterator::release	()
	{
		delete this;
	}

	bool	cDoubleLinkedListIterator::hasnext	()
	{ 
		return pNext != 0; 
	}

	void*	cDoubleLinkedListIterator::next		()
	{  
		// nächstes zurückgeben, und einen schritt weitergehen
		pLast = pNext;
		pPrev = pLast->pPrev;
		pNext = pNext->pNext;
		return pLast->pData;
	}

	bool	cDoubleLinkedListIterator::hasprev	()
	{
		return pPrev != 0;
	}

	void*	cDoubleLinkedListIterator::prev		()
	{
		// voriges zurückgeben, und einen schritt zurückgeben
		pLast = pPrev;
		pNext = pPrev;
		pPrev = pPrev->pPrev;
		return pLast->pData;
	}

	void	cDoubleLinkedListIterator::insert	(void* pData)
	{
		// einfügen vor dem element das als nächstes zurückgegeben wird.

		if (!pPrev) {
			pList->insertfirst(pData);
			pPrev = pList->pFirst;
			pLast = 0;
		} else if (!pNext) {
			pList->insertlast(pData);
			pPrev = pPrev->pNext;
			pLast = 0;
		} else {
			
			++pList->iSize;

			if (pLast == pPrev)
			{
				cDoubleLinkedListNode* newnode = new cDoubleLinkedListNode(pData,pNext->pPrev,pNext);
				if (pNext->pPrev) pNext->pPrev->pNext = newnode;
				pNext->pPrev = newnode;
				pPrev = newnode;
			} else {
				cDoubleLinkedListNode* newnode = new cDoubleLinkedListNode(pData,pPrev,pPrev->pNext);
				if (pPrev->pNext) pPrev->pNext->pPrev = newnode;
				pPrev->pNext = newnode;
				pNext = newnode;
			}

			pLast = 0;
		}
	}

	void* 	cDoubleLinkedListIterator::remove	()
	{
		// das zuletzt zurückgegebene wird gelöscht, und dessen data zurückgegeben
		assert(pLast && "cDoubleLinkedListIterator : last = 0");
		if (!pLast) return 0;
		if (pNext == 0) return pList->removelast();
		if (pPrev == 0) return pList->removefirst();

		void* res = pLast->pData;


		if (pLast->pPrev) pLast->pPrev->pNext = pLast->pNext; else pList->pFirst = 0;
		if (pLast->pNext) pLast->pNext->pPrev = pLast->pPrev; else pList->pLast = 0;
		if (pLast == pPrev)
				pPrev = pLast->pPrev;
		else	pNext = pLast->pNext;
		delete pLast;
		pLast = 0;
		--pList->iSize;
		return res;
	}

	void	cDoubleLinkedListIterator::set		(void* pData)
	{
		// data vom zuletzt zurückgegebenen verändern
		pLast->pData = pData;
	}

// ****** ****** ****** END