// ****** ****** ****** net.h
#ifndef _NET_H_
#define _NET_H_

#include <prefix.h>
#include <buffer.h>
#include <robstring.h>


// SOCKET = int


class cConnection;
class cNetAdress;
class cNetListener;


class cLinkedList;

// ****** ****** ****** cNet


class cNet {
public:
	cLinkedList*	aListener;//cNetListener
	cLinkedList*	aCons;//cConnection
	cLinkedList*	aDeadCons;//cConnection
	//DynArray<cNetListener*>	aListener;
	//DynArray<cConnection*>	aCons;
	//DynArray<cConnection*>	aDeadCons;

	cNet	();
	~cNet	();
	void	Step	();
};

extern cNet gNet;


// ****** ****** ****** cNetListener


class cNetListener {
public:
	cLinkedList*	aCons;//cConnection
	//DynArray<cConnection*>	aCons;
	int			iPort;
	int			iListenSocket;

	cNetListener	(int iPort);
	~cNetListener	();
	void	Step	();
};


// ****** ****** ****** cConnection


class cConnection {
public:
	int			iSocket;
	cString		sHost;
	cBuffer	bInBuffer;
	cBuffer	bOutBuffer;
	char		iIP[4];

	cConnection		(const char* szHost,int iPort); // connect out
	cConnection		(int iSocket); // connection coming in
	cConnection		(int iSocket,unsigned long iIP); // connection coming in
	~cConnection	();

	// only for cNet
	void	Step	(bool bRead,bool bWrite);
	bool	canread		();
	bool	canwrite	();
};


#endif
// ****** ****** ****** end