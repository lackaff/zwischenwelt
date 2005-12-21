// ****** ****** ****** list.cpp
#include <list.h>
#include <assert.h>

bool	cList::remove	(void* pData)
{
	cListIterator* i = (cListIterator*)iterator();
	while (i->hasnext())
		if (i->next() == pData)
		{
			i->remove(); 
			i->release(); 
			return 1;
		}
	i->release(); 
	return 0;
}

int		cList::seek		(void* pData)
{
	int pos = 0;
	cListIterator* i = (cListIterator*)iterator();
	while (i->hasnext())
		if (i->next() == pData)
		{
			i->release(); 
			return pos;
		} else pos++;
	i->release();
	return -1;
}
bool	cList::has			(void* pData)
{
	return seek(pData) != -1;
}

// ****** ****** ****** END