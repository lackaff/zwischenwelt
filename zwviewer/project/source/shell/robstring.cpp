// ****** ****** ****** robstring.cpp
#include <robstring.h>
#include <stdarg.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
//#include <utils.h>


#ifndef WIN32
	#include <ctype.h>
#endif



// global buffer
#define kStringBufferSize 1024*64 // supports up to 64kb length for one printf result
char	cString::pStringBuffer[kStringBufferSize] = "";

char*	gszDummyEmpty = "";

// ****** ****** ****** constructors


cString::cString		()
{
	szText = 0;
	iLength = 0;
	iBlockSize = 64;
	iBlockNum = 0;
}

cString::cString		(const char* szFormat,...)
{
	szText = 0;
	iLength = 0;
	iBlockSize = 64;
	iBlockNum = 0;

	va_list ap;	
	va_start(ap,szFormat);
	AppendVf(szFormat,ap);
	va_end(ap);
}

cString::cString		(const cString& pString)
{
	szText = 0;
	iLength = 0;
	iBlockSize = 64;
	iBlockNum = 0;
	Append(pString.szText);
}

cString::~cString		()
{
	Flush();
}

void	cString::Flush	()
{
	if (szText) free(szText);
	szText = 0;
	iLength = 0;
	iBlockNum = 0;
}


// ****** ****** ****** operators


cString&	cString::operator += (const cString& pString)
{
	if (&pString == this) return *this;// cant do that
	Append(pString.szText);
	return *this;
}

cString&	cString::operator += (const char* szText)
{
	Append(szText);
	return *this;
}

cString&	cString::operator = (const cString& pString)
{
	if (&pString == this) return *this;
	Flush();
	Append(pString.szText);
	return *this;
}

cString&	cString::operator = (const char* szText)
{
	Flush();
	Append(szText);
	return *this;
}

cString		cString::operator + (const cString& pString)
{
	cString newString;
	newString.Append(this->szText);
	newString.Append(pString.szText);
	return newString;
}

char*		cString::operator * ()
{
	// prefix * (like pointer)
	// *myCString  returns char pointer
	if (!szText) return gszDummyEmpty;
	return szText;
}


// ****** ****** ****** functions

  
void	cString::Append	(const char* szText)
{
	Append(szText,-1);
}

void	cString::Append	(const char* szText,const int iLength)
{
	if (!szText) return;
	if (iLength == 0) return;
	int len = strlen(szText);
	if (len == 0) return;
	if (iLength < len && iLength >= 0) len = iLength;
	SetBufLength(this->iLength+len);
	memcpy(this->szText+this->iLength,szText,len);
	this->iLength += len;
	this->szText[this->iLength] = 0;
}

void	cString::Appendf	(const char* szFormat,...)
{
	va_list ap;	
	va_start(ap,szFormat);
	AppendVf(szFormat,ap);
	va_end(ap);
}

void		cString::AppendVf	(const char* szFormat,void* arglist)
{
	pStringBuffer[0] = 0;
	vsnprintf(pStringBuffer,kStringBufferSize-1,szFormat,(va_list)arglist);
	Append(pStringBuffer);
}

cString		cString::SubStr	(const int iStart,const int iLength)
{
	cString newString;
	if (iStart < this->iLength)
		newString.Append(this->szText + iStart,iLength);
	return newString;
}

// \n is seperator, returns string without \n
cString		cString::GetLine	(int iLine)
{
	char *str;
	for (str=szText;iLine>0 && (str=strchr(str,'\n'));iLine--) str++;
	if (!str) return cString();

	cString newString;
	newString.Append(str,strcspn(str,"\n"));
	return newString;

	//res.Append(str,strcspn(str,"\n"));
}

int			cString::CountLines	()
{
	int c = 0;
	for (char* str=szText;str=strchr(str,'\n');c++) str++;
	return c;
}

// set buffer length excluding zero
void		cString::SetBufLength	(const int iBufLength)
{
	// length + 1 zero terminator
	int iNewBlocks = (iBufLength + 1 + iBlockSize - 1) / iBlockSize;
	if (iBlockNum == iNewBlocks) return; // no resize neccessary
	iBlockNum = iNewBlocks;
	szText = (char*)realloc(szText,iNewBlocks*iBlockSize);
	szText[iBufLength] = 0;

	// limit text to buffer
	if (iLength > iBufLength)
		iLength = iBufLength;
}

// ****** ****** ****** escape


bool charmatchrange (const char c,const char* r)
{
	for (;*r;r++)
		if (*r == '\\') // escaped char
			if (c == r[1]) return true;
			else r += 1; // skip escape char					
		else if (c == *r) return true; // also valid in case of range match with start
		else if (r[1] == '-') // range detected
			if (c >= *r && c <= r[2]) return true;
			else r += 2; // skip range
	return false;
}

int cinrange (const char* str,const char* range)
{
	int c = 0;
	for (;*str && charmatchrange(*str,range);str++) c++;
	return c;
}

int coutrange (const char* str,const char* range)
{
	int c = 0;
	for (;*str && !charmatchrange(*str,range);str++) c++;
	return c;
}


unsigned int stringhash (const char* str)
{
	if (!str) return 0;
	int res = 0;
	for (;*str;str++)
		res = (res + *str)*31;
	return res;
}

void	strtoupper	(char* str)
{
	for (;*str;str++) *str = toupper(*str);
}

void	strtolower	(char* str)
{
	for (;*str;str++) *str = tolower(*str);
}

cString AddSlashes (const char* str)
{
	cString myStr;
	const char* a;

	for (a=str;*a;a++)
	{
		if (charmatchrange(*a,"\\\"'"))
				myStr.Appendf("\\%c",*a);
		else	myStr.Appendf("%c",*a);
	}
	return cString(myStr);
}





// ****** ****** ****** example

#if 0

	cString myText("blabla %i %s %%",1,"baaahl");
	cString myText2(" DAS ENDE");
	cString myText3 = myText + myText2;
	myText3 += " blaaa";
	myText3 = "NOCHN ENDE";
	cString myText4 = myText3.SubStr(5,-1);

	printf("myText : '%s'\n",myText.szText);
	printf("myText2 : '%s'\n",myText2.szText);
	printf("myText3 : '%s'\n",myText3.szText);
	printf("myText4 : '%s'\n",myText4.szText);
	printf("myText4 : '%s'\n",*myText4); // same as row bevore
	printf("TEST : %s#%i\n",*cString("Mein %iter Test",4),6); // right
	//printf("TEST : %s#%i\n",cString("Mein %iter Test",4),6); // wrong
	//but might sometimes seem to work. following parameters are disturbed however

#endif

// ****** ****** ****** END
