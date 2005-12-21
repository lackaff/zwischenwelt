// ****** ****** ****** timer.h
#ifndef _TIMER_H_
#define _TIMER_H_

class cLinkedList;
class cObject;

// ****** ****** ****** cObjectManager

class cTimer {
public:
	int				iCurGameTick;
	cLinkedList*	aIntervals[12];
	int				iIntervals[12];

	cTimer	();

	void	Step		();
	void	SetInterval	(cObject* pObj,const int iInterval);

	static cTimer*	pSingleton;
	static cTimer*	instance ();
};

#endif
// ****** ****** ****** end