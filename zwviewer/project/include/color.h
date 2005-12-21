/** color.h
 * da wird die color class definiert blubber
 */
// ****** ****** ****** color.h
#ifndef _COLOR_H_
#define _COLOR_H_


#include <prefix.h>


/** 
 * die tolle colorclass
 */
class cColorRGBA {
public:
	float fR;
	float fG;
	float fB;
	float fA;
	// no virtual functions !
	// the size of this class must be exactly sizeof(float)*4

	cColorRGBA (const float r,const float g,const float b,const float a);
	cColorRGBA (const short c16,const float a);
	cColorRGBA (const int c32,const float a);
	cColorRGBA (const int c32);
	cColorRGBA ();

	// rendering

/** 
 * da gibts was mit 16bit
 */
	short	c16bit		();
	int		c32bit		();
	int		c32bita	(char a); // replace the a value by this

	// functions

	friend cColorRGBA	blend		(const cColorRGBA& c1,const cColorRGBA& c2,const float f);
	friend cColorRGBA	blend		(const cColorRGBA& c1,const cColorRGBA& c2); // use alpha
	friend float		sqmag		(const cColorRGBA& c);
	friend float		mag			(const cColorRGBA& c);
};


class cColorRGB {
public:
	float fR;
	float fG;
	float fB;
	// no virtual functions !
	// the size of this class must be exactly sizeof(float)*3

	cColorRGB (const float r,const float g,const float b);
	cColorRGB ();

	// functions

	friend cColorRGB	blend		(const cColorRGB& c1,const cColorRGB& c2,const float f);
	friend float		sqmag		(const cColorRGB& c);
	friend float		mag			(const cColorRGB& c);
};


#endif
// ****** ****** ****** END