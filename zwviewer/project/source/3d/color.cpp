// ****** ****** ****** color.cpp
#include <color.h>
#include <math.h>


// ****** ****** ****** cColorRGBA

cColorRGBA::cColorRGBA (const float r,const float g,const float b,const float a = 1.0)
{
	fR = r;
	fG = g;
	fB = b;
	fA = a;
}

cColorRGBA::cColorRGBA (const short c16,const float a = 1.0)
{
	fB = (float)((c16 & (short)0x001F) >>  0) / (float)(short)(0x001F);
	fG = (float)((c16 & (short)0x03E0) >>  5) / (float)(short)(0x001F);
	fR = (float)((c16 & (short)0x7C00) >> 10) / (float)(short)(0x001F);
	fA = a;
}

// 24 bit ??

cColorRGBA::cColorRGBA (const int c32,const float a)
{
	fB = (float)((c32 & (long)0x000000FF) >>  0) / (float)(long)(0x000000FF);
	fG = (float)((c32 & (long)0x0000FF00) >>  8) / (float)(long)(0x000000FF);
	fR = (float)((c32 & (long)0x00FF0000) >> 16) / (float)(long)(0x000000FF);
	fA = a;
}

cColorRGBA::cColorRGBA (const int c32)
{
	fB = (float)((c32 & (long)0x000000FF) >>  0) / (float)(long)(0x000000FF);
	fG = (float)((c32 & (long)0x0000FF00) >>  8) / (float)(long)(0x000000FF);
	fR = (float)((c32 & (long)0x00FF0000) >> 16) / (float)(long)(0x000000FF);
	fA = (float)((c32 & (long)0xFF000000) >> 24) / (float)(long)(0x000000FF);
}

cColorRGBA::cColorRGBA ()
{
	fR = 0.0;
	fG = 0.0;
	fB = 0.0;
	fA = 0.0;
}

/*
// byte order found in SDL_bmp.c
// extract from win32 sdk, bitmap header description
The relative intensities of red, green, and blue are 
represented with 5 bits for each color component. 
The value for blue is in the least significant 5 bits, 
followed by 5 bits each for green and red, respectively. 
The most significant bit is not used.
*/


// ****** ****** ****** cColorRGBA rendering

short	cColorRGBA::c16bit		()
{
	return	(((short)(fB * (float)(short)(0x001F))) <<  0) +
			(((short)(fG * (float)(short)(0x001F))) <<  5) +
			(((short)(fR * (float)(short)(0x001F))) << 10) ;
}

int		cColorRGBA::c32bit		()
{
	return	(((long)(fB * (float)(long)(0x00FF))) <<  0) +
			(((long)(fG * (float)(long)(0x00FF))) <<  8) +
			(((long)(fR * (float)(long)(0x00FF))) << 16) ;
}

int		cColorRGBA::c32bita	(char a)
{
	return	(((long)(fB * (float)(long)(0x00FF))) <<  0) +
			(((long)(fG * (float)(long)(0x00FF))) <<  8) +
			(((long)(fR * (float)(long)(0x00FF))) << 16) +
			(((long)( a * (float)(long)(0x00FF))) << 24) ;
}
/*
int		cColorRGBA::c32bita	(float a)
{
	return	(((long)(fB * (float)(long)(0x00FF))) <<  0) +
			(((long)(fG * (float)(long)(0x00FF))) <<  8) +
			(((long)(fR * (float)(long)(0x00FF))) << 16) +
			(((long)( a * (float)(long)(0x00FF))) << 24) ;
}*/

// ****** ****** ****** cColorRGBA functions

cColorRGBA	blend		(const cColorRGBA& c1,const cColorRGBA& c2,const float f)
{
	if (f <= 0.0) return c1;
	if (f >= 1.0) return c2;
	return cColorRGBA(	c1.fR + (c2.fR - c1.fR) * f,
						c1.fG + (c2.fG - c1.fG) * f,
						c1.fB + (c2.fB - c1.fB) * f,
						c1.fA + (c2.fA - c1.fA) * f);
}

cColorRGBA	blend		(const cColorRGBA& c1,const cColorRGBA& c2)
{
	return cColorRGBA(	c1.fR * c1.fA + c2.fR * c2.fA,
						c1.fG * c1.fA + c2.fG * c2.fA,
						c1.fB * c1.fA + c2.fB * c2.fA,
						c1.fA * c1.fA + c2.fA * c2.fA);
}

float		sqmag		(const cColorRGBA& c)
{
	return c.fR * c.fR + c.fG * c.fG + c.fB * c.fB;
}

float		mag			(const cColorRGBA& c)
{
	return sqrt(sqmag(c));
}




// ****** ****** ****** cColorRGB


cColorRGB::cColorRGB (const float r,const float g,const float b)
{
	fR = r;
	fG = g;
	fB = b;
}

cColorRGB::cColorRGB ()
{
}

// ****** ****** ****** cColorRGB functions


cColorRGB	blend		(const cColorRGB& c1,const cColorRGB& c2,const float f)
{
	if (f <= 0.0) return c1;
	if (f >= 1.0) return c2;
	return cColorRGB(	c1.fR + (c2.fR - c1.fR) * f,
						c1.fG + (c2.fG - c1.fG) * f,
						c1.fB + (c2.fB - c1.fB) * f);
}

float		sqmag		(const cColorRGB& c)
{
	return c.fR * c.fR + c.fG * c.fG + c.fB * c.fB;
}

float		mag			(const cColorRGB& c)
{
	return sqrt(sqmag(c));
}


// ****** ****** ****** end