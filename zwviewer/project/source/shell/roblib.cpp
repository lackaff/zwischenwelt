// ****** ****** ****** roblib.cpp
#include <vector> 
#include <map> 
#include <string> 
#include <roblib.h> 


void replace (const char* search,const char* replace,std::string& subject) {
	int pos = 0;
	while ((pos = subject.find(search,pos)) >= 0) {
		subject.replace(pos,strlen(search),replace);
		pos += strlen(replace);
	}
}

// ****** ****** ****** END