// ****** ****** ****** prefix.h
#ifndef _PREFIX_H_
#define _PREFIX_H_

#ifdef WIN32
	
	// Silence certain warnings
	#pragma warning(disable : 4244)		// int or float down-conversion
	#pragma warning(disable : 4305)		// int or float data truncation
	#pragma warning(disable : 4201)		// nameless struct/union
	#pragma warning(disable : 4514)		// unreferenced inline function removed
	#pragma warning(disable : 4100)		// unreferenced formal parameter

#endif

typedef unsigned int uint;
typedef unsigned char uchar;

#define kPi 3.141592654
#define kpi kPi
#define kPI kPi
#define kDegRad (2.0*kPi/180.0)
#define kRadDeg (180.0/2.0*kPi)

// stdlib 0 pointer definition
#ifndef NULL
#ifdef __cplusplus
#define NULL    0
#else
#define NULL    ((void *)0)
#endif
#endif


// Misc C-runtime library headers
//#include <stdarg.h>
//#include <stdio.h>
//#include <stdlib.h>
//#include <math.h>
//#include <string.h>
//#include <ctype.h>
//#include <limits.h>
//#include <direct.h>//ist für directory create unter win... ansi lösung ?
//#include <SDL/SDL.h>
//#include <assert.h>


// ansi stuff that is not quite equal on every os

#ifdef WIN32
	#ifndef snprintf
		#define snprintf _snprintf
	#endif
	#ifndef vsnprintf
	#define vsnprintf _vsnprintf
	#endif

#else
	#include <unistd.h>
	#include <sys/stat.h>

	#ifndef stricmp
		#define stricmp strcasecmp
	#endif

    #ifndef _strnicmp
		#define _strnicmp strncasecmp
    #endif
    #ifndef strnicmp
		#define strnicmp strncasecmp
    #endif
#endif

#if defined( _DEBUG )
extern bool CustomAssert (const bool bCond,const char* szText,const int iLine,const char* szFile,bool &bIgnoreAlways);
#define Assert(bCond,szText) \
{	static bool bIgnoreAlways = false; \
	if (!bIgnoreAlways) {\
		if (CustomAssert(bCond,szText,__LINE__,__FILE__,bIgnoreAlways)) \
		{ _asm { int 3 } } \
	} \
}
#else
#define Assert(bCond,szText)
#endif

#ifndef max
	inline int max(const int a,const int b)
	{
		return (a<b?b:a);
	}
	inline float max(const float a,const float b)
	{
		return (a<b?b:a);
	}
	//#define max(a,b) ((a)<(b)?(b):(a))
#endif

#ifndef min
	inline int min(const int a,const int b)
	{
		return (a>b?b:a);
	}
	inline float min(const float a,const float b)
	{
		return (a>b?b:a);
	}
	//#define min(a,b) ((a)>(b)?(b):(a))
#endif

#endif
// ****** ****** ****** end
