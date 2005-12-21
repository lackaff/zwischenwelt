// ****** ****** ****** input.cpp
#include <net.h>
#include <os.h>
#include <stdio.h>
#include <string.h>

#include <list.h>

#ifdef WIN32
	#ifndef socklen_t
	#define socklen_t int
	#endif
#endif

cNet gNet;


fd_set	sSelectSet_Read;
fd_set	sSelectSet_Write;

#define kRecvBufLen 1024
char	gpRecvBuf[kRecvBufLen];

// ****** ****** ****** constructor

cNet::cNet	()
{
	#ifdef WIN32
		WSADATA wsaData;
		WSAStartup( MAKEWORD( 2, 0 ), &wsaData );
	#endif

	aListener = new cLinkedList();
	aCons = new cLinkedList();
	aDeadCons = new cLinkedList();
	// init socketset for select
}

cNet::~cNet	()
{
	#ifdef WIN32
		WSACleanup();
	#endif

	cIterator* i;

	i = aListener->iterator();
	while (i->hasnext()) delete (cNetListener*)i->next();
	i->release();
	delete aListener;
	
	i = aCons->iterator();
	while (i->hasnext()) delete (cConnection*)i->next();
	i->release();
	delete aCons;
}

// ****** ****** ****** Step

void	cNet::Step	()
{
	int imax,res,mysocket;
	
	cIterator* i;
	i = aListener->iterator();
	while (i->hasnext()) ((cNetListener*)i->next())->Step();
	i->release();

	// monster select einsatz

	timeval	timeout;
	timeout.tv_sec = 0;
	timeout.tv_usec = 0;

	FD_ZERO(&sSelectSet_Read);
	FD_ZERO(&sSelectSet_Write);
	imax = 0;

	if (aCons->size() > 0)
	{
		i = aCons->iterator();
		while (i->hasnext())
		{
			mysocket = ((cConnection*)i->next())->iSocket;
			if (imax < mysocket)
				imax =mysocket;
			FD_SET((unsigned int)mysocket,&sSelectSet_Read);
			FD_SET((unsigned int)mysocket,&sSelectSet_Write);
		}
		i->release();

		res = select(imax+1,&sSelectSet_Read,&sSelectSet_Write,0,&timeout);

		//printf("cNetSelect %i\n",res);

		cConnection* mycon;
		i = aCons->iterator();
		while (i->hasnext())
		{
			mycon = (cConnection*)i->next();
			mycon->Step(FD_ISSET((unsigned int)mycon->iSocket,&sSelectSet_Read) != 0,
						FD_ISSET((unsigned int)mycon->iSocket,&sSelectSet_Write) != 0 );
		}
	}
}





// ****** ****** ****** cConnection

/*
class cConnection {
public:
	int			iSocket;
	int			iIP[4];
	cBuffer	bInBuffer;
	cBuffer	bOutBuffer;
};*/

cConnection::cConnection		(int iSocket)
{
	this->iIP[0] = 0;
	this->iIP[1] = 0;
	this->iIP[2] = 0;
	this->iIP[3] = 0;
	this->iSocket = iSocket;
	gNet.aCons->insert(this);
}

cConnection::cConnection		(int iSocket,unsigned long iIP)
{
	this->iIP[0] = ((char*)&iIP)[0];
	this->iIP[1] = ((char*)&iIP)[1];
	this->iIP[2] = ((char*)&iIP)[2];
	this->iIP[3] = ((char*)&iIP)[3];
	this->iSocket = iSocket;
	gNet.aCons->insert(this);
}


cConnection::cConnection		(const char* szHost,int iPort)
{
	iSocket = INVALID_SOCKET;
	if (!szHost) return;

	// host merken
	sHost = szHost;

	sockaddr_in		sAddr;
	hostent*		h;

	// resolving host

	sAddr.sin_family = AF_INET;
	sAddr.sin_port = htons(iPort);
		
	h = gethostbyname(szHost);
	if (h)
	{
		sAddr.sin_addr.s_addr = *((unsigned long *)h->h_addr);
		// success
	} else {

		sAddr.sin_addr.s_addr = inet_addr(szHost);
		if (sAddr.sin_addr.s_addr == INADDR_NONE)
		{
			// log error
			return;
		}
		// else success
	}

	//Log('L',"%s -> %i.%i.%i.%i:%i\n",szHost,
	//		sAddr.sin_addr.s_net,	sAddr.sin_addr.s_host,
	//		sAddr.sin_addr.s_lh,	sAddr.sin_addr.s_impno,iPort);

	// winsock2.h
	// AF_INET : internetwork: UDP, TCP, etc.
	// AF_IPX : IPX protocols: IPX, SPX, etc.
	// AF_IPX AF_INET6 AF_NETBIOS AF_APPLETALK 

	// SOCK_STREAM     // stream socket 
	// SOCK_DGRAM      // datagram socket 
	// SOCK_RAW        // raw-protocol interface 
	// SOCK_RDM        // reliably-delivered message 
	// SOCK_SEQPACKET  // sequenced packet stream 

	// IPPROTO_IP    // dummy for IP
	// IPPROTO_TCP   // tcp
	// IPPROTO_UDP   // user datagram protocol
	// IPPROTO_RAW   // raw IP packet

	// now connecting
	iSocket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	//iSocket = socket(AF_INET, SOCK_DGRAM, IPPROTO_TCP);
	if(iSocket == INVALID_SOCKET)
	{
		//Log('L',"Socket Creation Failed %i\n",netError);
		return;
	}
	if (connect(iSocket,(const sockaddr*)&sAddr,sizeof(sAddr)) == SOCKET_ERROR)
	{
		//Log('L',"Connection Failed %i\n",netError);
		iSocket = INVALID_SOCKET;
		return;
	}

	gNet.aCons->insert(this);
}




/*
protected:
int		iListenSocket;
int		Accept	();
*/




cConnection::~cConnection		()
{
	if (iSocket != INVALID_SOCKET)
	{

	}

	// remove from 
	gNet.aCons->remove(this);
}


void	cConnection::Step		(bool bRead,bool bWrite)
{
	if (iSocket == INVALID_SOCKET) return;

	int res;

	// write and read buffer
	if (bRead)
	//if (canread())
	{
		res = recv(iSocket,gpRecvBuf,kRecvBufLen,0);	
		if (res > 0)
		{
			bInBuffer.AddTail(gpRecvBuf,res);	
		}
		else if(res < 0)
		{
			if(gNet.aCons->remove(this))
			{
				gNet.aDeadCons->insert(this);
				printf("dead connection found\n");
			}
		}
	}

	if (bWrite && bOutBuffer.iLength > 0)
	//if (canwrite() && bOutBuffer.iLength > 0)
	{
		res = send(iSocket,bOutBuffer.pData,bOutBuffer.iLength,0);
		if (res > 0)
		{
			bOutBuffer.CutHead(res);
		}
		else if (res < 0)
		{
			if(gNet.aCons->remove(this))
			{
				gNet.aDeadCons->insert(this);
				printf("dead connection found\n");
			}
		}
	}
}

bool	canread		();
bool	canread		();



// ****** ****** ****** cNetListener




cNetListener::cNetListener	(int iPort)
{
	aCons = new cLinkedList();

	this->iPort = iPort;
	iListenSocket = INVALID_SOCKET;

	int		err;

	// create socket
	iListenSocket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if(iListenSocket == INVALID_SOCKET)
	{
		//Log('L',"Socket Creation Failed %i\n",netError);
		return;
	}

	// bind the socket
	sockaddr_in sa;
	memset(&sa,0,sizeof(sa));

	sa.sin_family = AF_INET;
    sa.sin_port = htons(iPort);
	sa.sin_addr.s_addr = (unsigned long)0x00000000;

	err = bind(iListenSocket,(sockaddr*)&sa,sizeof(sa));
	if (err != 0)
	{
		//Log('L',"BIND ERROR %i\n",netError);//WSAECONNREFUSED
		close(iListenSocket);
		iListenSocket = INVALID_SOCKET;
		return;
	}

	// start listening
	err = listen(iListenSocket,SOMAXCONN);
	if (err != 0)
	{
		// Log('L',"LISTEN ERROR %i\n",netError);
		close(iListenSocket);
		iListenSocket = INVALID_SOCKET;
		return;
	}

	// success
	gNet.aListener->insert(this);

}

cNetListener::~cNetListener	()
{
	delete aCons;

	if (iListenSocket != INVALID_SOCKET)
		close(iListenSocket);
	iListenSocket = INVALID_SOCKET;

	gNet.aListener->remove(this);
}

void	cNetListener::Step	()
{
	if (iListenSocket == INVALID_SOCKET)
		return;
	
	// aaaaccept
	
	int		selres;
	timeval	timeout;
	fd_set	conn;

	timeout.tv_sec = 0;
	timeout.tv_usec = 0;

	FD_ZERO(&conn); // Set the data in conn to nothing
	FD_SET((unsigned int)iListenSocket, &conn); // Tell it to get the data from the Listening Socket

	selres = select(iListenSocket+1, &conn, NULL, NULL, &timeout); // Is there any data coming in?

	// KEINER DA !
	if (selres <= 0)
		return;

	// host schaut ob wer joint
	int		consocket;
	struct	sockaddr_in their_addr;
	socklen_t	sin_size;

	sin_size = sizeof(struct sockaddr_in);
	consocket = accept(iListenSocket,(struct sockaddr *)&their_addr, &sin_size);
	
	if (consocket != INVALID_SOCKET)
		aCons->insert(new cConnection(consocket,their_addr.sin_addr.s_addr));
}


// ****** ****** ****** end
