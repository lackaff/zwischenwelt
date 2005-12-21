// ****** ****** ****** shell.h
#ifndef _SHELL_H_
#define _SHELL_H_ 

#include <prefix.h>

class cDoubleLinkedList;
class cStringHashMap;

class cShell {
public :
	int		iXRes;
	int		iYRes;
	bool	bAlive;
	int		iClock0;
	int		iClock; // updated in EventLoopStep()
	int		iArgC;
	char**	szArgV;

	int		Init			();
	void	Kill			();
	void	EventLoopStep	();

	int		SetVideo			(int iCX,int iCY,int iBPP,bool bFullscreen);
	void	SetProgramIcon		(char* szPath);
	void	SetProgramCaption	(char* szCaption);
	void	ShowCursor			(bool bVisible);
	int		GetTicks			();

	char*	ListFiles	(const char* path);
	char*	ListDirs	(const char* path);
	void	Write		(const char* file,const char* str);
	void	Write		(const char* file,const char* str,const int len);
	void	Append		(const char* file,const char* str);
	void	Append		(const char* file,const char* str,const int len);
	char*	Read		(const char* file);
	char*	Read		(const char* file,int &len);
	void	ParseText	(const char* text,cStringHashMap* pMap);
	void	ParseFile	(const char* file,cStringHashMap* pMap);
};

extern cShell gShell;

#endif
// ****** ****** ****** end