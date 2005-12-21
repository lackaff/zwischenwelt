// ****** ****** ****** buffer.h
#ifndef _BUFFER_H_
#define _BUFFER_H_

#include <prefix.h>


#define kPopBufferSize 1024

// ****** ****** ****** cBuffer

class cBuffer {
public:
	char*	pData;
	int		iLength;
	
	cBuffer		();
	cBuffer		(const void* pNewData,int iNewLen);
	~cBuffer	();
	void	Empty		();
	void	Set			(cBuffer* pBuf);
	void	Set			(const void* pNewData,int iNewLen);
	void	AddHead		(cBuffer* pBuf);
	void	AddTail		(cBuffer* pBuf);
	void	AddHead		(const void* pNewData,int iNewLen); // -1 = strlen(newdata)
	void	AddTail		(const void* pNewData,int iNewLen); // -1 = strlen(newdata)
	void	CutHead		(const int iCutLen);
	void	CutTail		(const int iCutLen);
	void	CutHead		(cBuffer* pOut,const int iCutLen);
	void	CutTail		(cBuffer* pOut,const int iCutLen);
	void	CutHead		(void* pOut,const int iCutLen);
	void	CutTail		(void* pOut,const int iCutLen);
	void*	PopHead		(const int iCutLen); // pop up to 1k of data into temp buffer
	void*	PopTail		(const int iCutLen); // pop up to 1k of data into temp buffer

	// string, vector, color,
};

// push,pop,shift,head macros

// WARNING ! DO NOT USE ON DATA CONTAINING POINTERS !
#define	PushType(buffer,a,t)	((buffer)->AddTail(&(a),sizeof(t)))
#define	ShiftType(buffer,a,t)	((buffer)->AddHead(&(a),sizeof(t)))
#define	PopType(buffer,t)		(*(t*)(buffer)->PopTail(sizeof(t)))
#define	HeadType(buffer,t)		(*(t*)(buffer)->PopHead(sizeof(t)))

#define	PushChar(buffer,a)		PushType(buffer,a,char)
#define	ShiftChar(buffer,a)		ShiftType(buffer,a,char)
#define	PopChar(buffer)			PopType(buffer,char)
#define	HeadChar(buffer)		HeadType(buffer,char)

#define	PushShort(buffer,a)		PushType(buffer,a,short)
#define	ShiftShort(buffer,a)	ShiftType(buffer,a,short)
#define	PopShort(buffer)		PopType(buffer,short)
#define	HeadShort(buffer)		HeadType(buffer,short)

#define	PushInt(buffer,a)		PushType(buffer,a,int)
#define	ShiftInt(buffer,a)		ShiftType(buffer,a,int)
#define	PopInt(buffer)			PopType(buffer,int)
#define	HeadInt(buffer)			HeadType(buffer,int)

#define	PushFloat(buffer,a)		PushType(buffer,a,float)
#define	ShiftFloat(buffer,a)	ShiftType(buffer,a,float)
#define	PopFloat(buffer)		PopType(buffer,float)
#define	HeadFloat(buffer)		HeadType(buffer,float)

#define	PushVector2d(buffer,a)	PushType(buffer,a,cVector2d)
#define	ShiftVector2d(buffer,a)	ShiftType(buffer,a,cVector2d)
#define	PopVector2d(buffer)		PopType(buffer,cVector2d)
#define	HeadVector2d(buffer)	HeadType(buffer,cVector2d)

#define	PushColorRGBA(buffer,a)		PushType(buffer,a,cColorRGBA)
#define	ShiftColorRGBA(buffer,a)	ShiftType(buffer,a,cColorRGBA)
#define	PopColorRGBA(buffer)		PopType(buffer,cColorRGBA)
#define	HeadColorRGBA(buffer)		HeadType(buffer,cColorRGBA)

#define	PushColorRGB(buffer,a)		PushType(buffer,a,cColorRGB)
#define	ShiftColorRGB(buffer,a)		ShiftType(buffer,a,cColorRGB)
#define	PopColorRGB(buffer)			PopType(buffer,cColorRGB)
#define	HeadColorRGB(buffer)		HeadType(buffer,cColorRGB)


#endif
// ****** ****** ****** end