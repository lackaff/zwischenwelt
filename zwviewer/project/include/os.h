// ****** ****** ****** os.h
// try to precompile this.. will double compile speed  ;)
#ifndef _OS_H_
#define _OS_H_

#include <prefix.h>

// Prevent tons of unused windows definitions

#ifdef WIN32
	#define WIN32_LEAN_AND_MEAN
	#define NOWINRES
	#define NOSERVICE
	#define NOMCX
	#define NOIME
	
	#include <windows.h>

	// required by 
	//#include <GL/gl.h>	// OpenGL32 Library
	//#include <GL/glu.h>	// GLu32 Library
	
	#ifdef GetObject 
	#undef GetObject
	#endif

	//winstuff for ie directory work
	#include <shellapi.h>
	#pragma comment(lib,"shell32.lib")

	#ifndef close
		#define close(socket)	closesocket(socket)
	#endif

	#include <winsock2.h>
	#pragma comment(lib,"WS2_32.LIB")
#else
	#ifndef SOCKET_ERROR
		#define SOCKET_ERROR	-1
	#endif
	
	#include <errno.h>
	#include <netdb.h>
	#include <sys/types.h>
	#include <netinet/in.h>
	#include <sys/socket.h>
	#include <arpa/inet.h>
#endif //WIN32


//winsock workaround
#ifndef INVALID_SOCKET
#define INVALID_SOCKET  (SOCKET)(~0)
#endif

#ifndef SOCKET
#define SOCKET int
#endif



// post system defs
#ifndef MAX_PATH
	#define MAX_PATH 256
#endif
#ifndef TRUE
	#define TRUE true
#endif
#ifndef FALSE
	#define FALSE false
#endif

#endif
// ****** ****** ****** END