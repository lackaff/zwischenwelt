// ****** ****** ****** robstring.h
#ifndef _ROBSTRING_H_
#define _ROBSTRING_H_

#include <prefix.h>

class cString {
public:
	int			iLength;
	char*		szText;

	// constructors
	cString		();
	cString		(const char* szFormat,...);
	cString		(const cString& pString);
	~cString	();
	void	Flush	();

	// operators
	cString&	operator += (const cString& pString);
	cString&	operator += (const char* szText);
	cString&	operator = (const cString& pString);
	cString&	operator = (const char* szText);
	cString		operator + (const cString& pString);
	char*		operator * (); // *myCString returns the char pointer

	// functions
	void		Append		(const char* szText);
	void		Append		(const char* szText,const int iLength);
	void		Appendf		(const char* szFormat,...);
	void		AppendVf	(const char* szFormat,void* arglist);
	cString		SubStr		(const int iStart,const int iLength);
	cString		GetLine		(int iLine);
	int			CountLines	();

protected:
	int			iBlockSize;
	int			iBlockNum;
	static char	pStringBuffer[];
	void		SetBufLength	(const int iBufLength);
};

bool	charmatchrange	(const char c,const char* r); // \ to escape, a-z as range
int		cinrange	(const char* str,const char* range); // count chars in range
int		coutrange	(const char* str,const char* range); // count chars out of range
cString	AddSlashes	(const char* str);
unsigned int	stringhash	(const char* str);
void	strtoupper	(char* str);
void	strtolower	(char* str);

#endif
// ****** ****** ****** END