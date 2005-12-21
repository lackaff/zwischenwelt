// ****** ****** ****** hashmap.cpp
#include <list.h>
#include <robstring.h>
#include <string.h>
#include <stdlib.h>
#include <assert.h>
#include <stdio.h>


// ****** ****** ****** cHashMapNode


// copies string
cStringHashMapNode::cStringHashMapNode	(void* pData,const char* pKey)
{
	this->pData = pData;
	this->pKey = strdup(pKey);
}

// releases string
cStringHashMapNode::~cStringHashMapNode	()
{
	free(pKey);
	pKey = 0;
}

// copies string
cIntegerHashMapNode::cIntegerHashMapNode	(void* pData,const unsigned int iKey)
{
	this->pData = pData;
	this->iKey = iKey;
}


// ****** ****** ****** cHashMap



// fields are the resolution of the map
cHashMap::cHashMap		(int iFields)
{
	this->iFields = iFields;
	iSize = 0;
	pArr = (cLinkedList**)malloc(sizeof(cLinkedList*)*iFields);
	for (int i=0;i<iFields;i++)
		pArr[i] = new cLinkedList();
}

cHashMap::~cHashMap		()
{
	// TODO : release hashmapnodes !
	if (pArr)
	{
		for (unsigned int i=0;i<iFields;i++)
			if (pArr[i]) { delete pArr[i]; pArr[i] = 0; }
			free(pArr);
	}
	pArr = 0;
	iSize = 0;
}

void			cHashMap::release	()
{ delete this; }

cListIterator*	cHashMap::iterator	()
{ return new cHashMapIterator(this); }

unsigned int	cHashMap::size		()
{ return iSize; }


// ****** ****** ****** cStringHashMap


cStringHashMap::cStringHashMap (int iFields) : cHashMap(iFields) {}

void			cStringHashMap::insert	(void* pData)
{
	insert(pData,"");
}

// copies string
void			cStringHashMap::insert	(void* pData,const char* pKey)
{
	unsigned int hashcode = stringhash(pKey);
	pArr[hashcode % iFields]->insert(new cStringHashMapNode(pData,pKey));
	iSize++;
}

void*			cStringHashMap::get		(const char* pKey)
{
	unsigned int hashcode = stringhash(pKey);

	cListIterator* i = pArr[hashcode % iFields]->iterator();
	cStringHashMapNode* cur;
	while (i->hasnext())
	{
		cur = (cStringHashMapNode*)i->next();
		if (strcmp(cur->pKey,pKey) == 0)
		{
			i->release();
			return cur->pData;
		}
	}
	i->release();
	return 0;
}

void*			cStringHashMap::remove		(const char* pKey)
{
	unsigned int hashcode = stringhash(pKey);

	cListIterator* i = pArr[hashcode % iFields]->iterator();
	cStringHashMapNode* cur;
	void* res;
	while (i->hasnext())
	{
		cur = (cStringHashMapNode*)i->next();
		if (strcmp(cur->pKey,pKey) == 0)
		{
			res = cur->pData;
			delete cur;
			i->remove();
			i->release();
			return res;
		}
	}
	i->release();
	return 0;
}


// ****** ****** ****** cIntegerHashMap


cIntegerHashMap::cIntegerHashMap (int iFields) : cHashMap(iFields) {}

void			cIntegerHashMap::insert	(void* pData)
{
	insert(pData,0);
}

// copies string
void			cIntegerHashMap::insert	(void* pData,const unsigned int iKey)
{
	pArr[iKey % iFields]->insert(new cIntegerHashMapNode(pData,iKey));
	iSize++;
}

void*			cIntegerHashMap::get		(const unsigned int iKey)
{
	cListIterator* i = pArr[iKey % iFields]->iterator();
	cIntegerHashMapNode* cur;
	while (i->hasnext())
	{
		cur = (cIntegerHashMapNode*)i->next();
		if (cur->iKey == iKey)
		{
			i->release();
			return cur->pData;
		}
	}
	i->release();
	return 0;
}

void*			cIntegerHashMap::remove		(const unsigned int iKey)
{
	cListIterator* i = pArr[iKey % iFields]->iterator();
	cIntegerHashMapNode* cur;
	void* res;
	while (i->hasnext())
	{
		cur = (cIntegerHashMapNode*)i->next();
		if (cur->iKey == iKey)
		{
			res = cur->pData;
			delete cur;
			i->remove();
			i->release();
			return res;
		}
	}
	i->release();
	return 0;
}


// ****** ****** ****** cHashMapIterator



cHashMapIterator::cHashMapIterator(cHashMap* pHashMap)
{
	this->pHashMap = pHashMap;
	pIterator = 0;
	for (iField = 0;iField<pHashMap->iFields;iField++)
		if (pHashMap->pArr[iField]->size() > 0)
			pIterator = pHashMap->pArr[iField]->iterator();
}

cHashMapIterator::~cHashMapIterator()
{
	if (pIterator) pIterator->release();
	pHashMap = 0;
	pIterator = 0;
	iField = 0;
}

void	cHashMapIterator::release		()
{ delete this; }

bool	cHashMapIterator::hasnext		()
{
	if (pIterator && pIterator->hasnext()) 
		return true;
	for (unsigned int i = iField+1;i<pHashMap->iFields;i++)
		if (pHashMap->pArr[i]->size() > 0) return true;
	return false;
}

void*	cHashMapIterator::next			()
{
	if (pIterator && pIterator->hasnext()) 
		return ((cHashMapNode*)pIterator->next())->pData;
	// else iterator ended
	pIterator->release();
	pIterator = 0;

	for (iField++;iField<pHashMap->iFields;iField++)
		if (pHashMap->pArr[iField]->size() > 0)
		{
			pIterator = pHashMap->pArr[iField]->iterator();
			cHashMapNode* cur = (cHashMapNode*)pIterator->next();
			return cur->pData;
		}
	return 0;
}

// calls HashMap insert !
void	cHashMapIterator::insert		(void* pData)
{ pHashMap->insert(pData); }

void* 	cHashMapIterator::remove		()
{
	cHashMapNode* cur = (cHashMapNode*)pIterator->remove();
	pHashMap->iSize--;
	void* pData = cur->pData;
	delete cur;
	return pData;
}

// useless
void	cHashMapIterator::set			(void* pData)
{
	assert(0 && "hashmap set is useless");
}


// ****** ****** ****** END