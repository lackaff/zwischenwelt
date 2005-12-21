// ****** ****** ****** buffer.cpp
#include <buffer.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <assert.h>


char	gBufferOut[kPopBufferSize];

// ****** ****** ****** constructors


cBuffer::cBuffer	()
{
	pData = (char*)malloc(1);
	pData[0] = 0;
	iLength = 0;
}


cBuffer::cBuffer	(const void* pNewData,int iNewLen)
{
	pData = (char*)malloc(1);
	pData[0] = 0;
	iLength = 0;
	Set(pNewData,iNewLen);
}


cBuffer::~cBuffer	()
{
	if (pData) free(pData);
	pData = 0;
	iLength = 0;
}


// ****** ****** ****** shortcuts


void	cBuffer::Empty		()
{ CutTail(iLength); }

void	cBuffer::Set			(cBuffer* pBuf)
{ Empty(); AddTail(pBuf); }

void	cBuffer::Set		(const void* pNewData,int iNewLen)
{ Empty(); AddTail(pNewData,iNewLen); }

void	cBuffer::AddHead		(cBuffer* pBuf)
{
	if (!pBuf) return;
	AddHead(pBuf->pData,pBuf->iLength);
}

void	cBuffer::AddTail		(cBuffer* pBuf)
{
	if (!pBuf) return;
	AddTail(pBuf->pData,pBuf->iLength);
}


// ****** ****** ****** add


void	cBuffer::AddHead		(const void* pNewData,int iNewLen)
{
	if (!pNewData || iNewLen == 0) return;

	// -1 = strlen(newdata)
	if (iNewLen < 0) iNewLen = strlen((char*)pNewData);

	pData = (char*)realloc(pData,iLength+iNewLen+1);
	memmove(pData+iNewLen,pData,iLength);
	memcpy(pData,pNewData,iNewLen);
	iLength += iNewLen;
	pData[iLength] = 0;
}


void	cBuffer::AddTail		(const void* pNewData,int iNewLen)
{
	if (!pNewData || iNewLen == 0) return;

	// -1 = strlen(newdata)
	if (iNewLen < 0) iNewLen = strlen((char*)pNewData);
	
	pData = (char*)realloc(pData,iLength+iNewLen+1);
	memcpy(pData+iLength,pNewData,iNewLen);
	iLength += iNewLen;
	pData[iLength] = 0;
}


// ****** ****** ****** cut


void	cBuffer::CutHead		(const int iCutLen)
{
	assert(iCutLen <= iLength && "cBuffer underrun");
	if (iCutLen <= 0) return;

	memmove(pData,pData+iCutLen,iLength-iCutLen);
	pData = (char*)realloc(pData,iLength-iCutLen+1);
	iLength -= iCutLen;
	pData[iLength] = 0;
}


void	cBuffer::CutTail		(const int iCutLen)
{
	assert(iCutLen <= iLength && "cBuffer underrun");
	if (iCutLen <= 0) return;

	pData = (char*)realloc(pData,iLength-iCutLen+1);
	iLength -= iCutLen;
	pData[iLength] = 0;
}


void	cBuffer::CutHead		(cBuffer* pOut,const int iCutLen)
{
	assert(iCutLen <= iLength && "cBuffer underrun");
	if (pOut)
		pOut->Set(pData,iCutLen);
	CutHead(iCutLen);
}


void	cBuffer::CutTail		(cBuffer* pOut,const int iCutLen)
{
	assert(iCutLen <= iLength && "cBuffer underrun");
	if (pOut)
		pOut->Set(pData+iLength-iCutLen,iCutLen);
	CutTail(iCutLen);
}


void	cBuffer::CutHead		(void* pOut,const int iCutLen)
{
	assert(iCutLen <= iLength && "cBuffer underrun");
	if (pOut)
		memcpy(pOut,pData,iCutLen);
	CutHead(iCutLen);	
}


void	cBuffer::CutTail		(void* pOut,const int iCutLen)
{
	assert(iCutLen <= iLength && "cBuffer underrun");
	if (pOut)
		memcpy(pOut,pData+iLength-iCutLen,iCutLen);
	CutTail(iCutLen);
}


// ****** ****** ****** pop
// pop up to 1k of data into temp buffer

void*	cBuffer::PopHead		(const int iCutLen)
{
	assert(iCutLen < kPopBufferSize && "cBuffer popbuffer overflow");
	CutHead(gBufferOut,iCutLen);
	return (void*)gBufferOut;
}

void*	cBuffer::PopTail		(const int iCutLen)
{
	assert(iCutLen < kPopBufferSize && "cBuffer popbuffer overflow");
	CutTail(gBufferOut,iCutLen);
	return (void*)gBufferOut;
}


// ****** ****** ****** end
#if 0
	// example
	cBuffer myBuffer;
	myBuffer.AddTail(*cString("kopf"),-1);
	myBuffer.AddTail(*cString("#fuss1#"),-1);
	myBuffer.AddTail(*cString("#fuss2#"),-1);
	printf("buffer0 : %s\n",myBuffer.pData);
	myBuffer.CutTail(3);
	printf("buffer1 : %s\n",myBuffer.pData);
	myBuffer.CutHead(1);
	printf("buffer2 : %s\n",myBuffer.pData);
	myBuffer.AddHead(*cString("Helm"),-1);
	printf("buffer3 : %s\n",myBuffer.pData);

	// push pop example , w/o macro

	cBuffer myStack;
	int myint = 4;
	myStack.AddTail(&myint,sizeof(int));
	myint = 0;
	myint = *(int*)myStack.PopTail(sizeof(int));
	printf("stack : %i\n",myint);

	// push pop example , with macro
	
	cBuffer myStack;
	int myint = 4;
	PushInt(&myStack,myint);
	myint = 0;
	myint = PopInt(&myStack);
	printf("stack : %i\n",myint);
#endif
// ****** ****** ****** end
