// ****** ****** ****** shell.cpp
#include <time.h>
#include <math.h>
#include <shell.h>
#include <input.h>
#include <stdlib.h>
#include <string.h>
#include <SDL/SDL.h>
#include <SDL/SDL_image.h>
#include <robstring.h>
#include <os.h>
#include <assert.h>
#include <list.h>

#ifndef WIN32
	#include <sys/types.h>
	#include <dirent.h>
#endif

cShell gShell;



// ****** ****** ****** cShell

bool CustomAssert (const bool bCond,const char* szText,const int iLine,const char* szFile,bool &bIgnoreAlways)
{
	assert(bCond && "CustomAssert()");
	return !bCond;
}

// Init : shell initilization
int		cShell::Init			()
{
	bAlive = true;

	// init random
	srand(time(NULL));

    // init SDL library
    if (SDL_Init(SDL_INIT_VIDEO | SDL_INIT_TIMER | 0*SDL_INIT_AUDIO) < 0)
	{
		printf("Couldn't initialize SDL: %s",SDL_GetError());
        return 0;
    }

	// init SDL OpenGL  todo: check if this is perhaps only for 16bpp?
    SDL_GL_SetAttribute( SDL_GL_RED_SIZE, 5 );
    SDL_GL_SetAttribute( SDL_GL_GREEN_SIZE, 5 );
    SDL_GL_SetAttribute( SDL_GL_BLUE_SIZE, 5 );
    SDL_GL_SetAttribute( SDL_GL_DEPTH_SIZE, 16 );
    SDL_GL_SetAttribute( SDL_GL_DOUBLEBUFFER, 1 );

	// init SDL Keys
	SDL_EnableUNICODE(1);
	SDL_EnableKeyRepeat(200,30); // SDL_DEFAULT_REPEAT_DELAY,SDL_DEFAULT_REPEAT_INTERVAL

    // Clean up on exit
    atexit(SDL_Quit);

	// initialize clock
	iClock0 = SDL_GetTicks();
	iClock = 0;

    return 1;
}



// Kill : shutdown tasks
void	cShell::Kill			()
{

}




// EventLoopStep : one step in the eventloop, process all system events in queue
void	cShell::EventLoopStep	()
{
    SDL_Event event;

	gInput.iMouseWheel = 0; // reset every event step

	int i;
	for (i=0;i<256;i++)
		if (gInput.bKeys[i] && gInput.bKeyPress[i] < 100)
			gInput.bKeyPress[i]++;

	// get mousepos
	SDL_GetMouseState((int*)&gInput.iMouse[0],(int*)&gInput.iMouse[1]);

	// get time
	iClock = SDL_GetTicks() - iClock0;

	// process events
	while (SDL_PollEvent(&event) >= 1)
    switch (event.type)
	{
		break;case SDL_ACTIVEEVENT:
			if ( event.active.state & SDL_APPACTIVE )
			{
				if ( event.active.gain )
				{
					//BoxMessage("ERROR","App activated\n");
					gInput.Reset();
				} else {
					//BoxMessage("ERROR","App iconified\n");
				}
			}
    
            
		break;case SDL_KEYDOWN:
			if (event.key.keysym.sym == SDLK_ESCAPE) 
			{
				printf("Quit requested by escape, quitting.");
				//exit(1); // todo : replace me by gentle shutdown
				gShell.bAlive = 0;
			}
			
			gInput.KeyDown(gInput.KeyConvertSDL(event.key.keysym.sym));



		break;case SDL_KEYUP:
			gInput.KeyUp(gInput.KeyConvertSDL(event.key.keysym.sym));


		break;case SDL_MOUSEBUTTONDOWN:
			switch (event.button.button)
			{
				break;case SDL_BUTTON_LEFT:		gInput.KeyDown(cInput::kkey_mouse1);
				break;case SDL_BUTTON_RIGHT:	gInput.KeyDown(cInput::kkey_mouse2);
				break;case SDL_BUTTON_MIDDLE:	gInput.KeyDown(cInput::kkey_mouse3);
				break;case 4:	gInput.KeyDown(cInput::kkey_wheelup);
				break;case 5:	gInput.KeyDown(cInput::kkey_wheeldown);
				break;default: printf("SDL_MOUSEBUTTONDOWN : %i\n",event.button.button);
			}

		break;case SDL_MOUSEBUTTONUP:
			switch (event.button.button)
			{
				break;case SDL_BUTTON_LEFT:		gInput.KeyUp(cInput::kkey_mouse1);
				break;case SDL_BUTTON_RIGHT:	gInput.KeyUp(cInput::kkey_mouse2);
				break;case SDL_BUTTON_MIDDLE:	gInput.KeyUp(cInput::kkey_mouse3);
				break;case 4:	gInput.KeyUp(cInput::kkey_wheelup);
				break;case 5:	gInput.KeyUp(cInput::kkey_wheeldown);
				break;default: printf("SDL_MOUSEBUTTONUP : %i\n",event.button.button);
			}

		break;case SDL_QUIT:
			printf("Quit requested, quitting.");
			exit(0); // todo : replace me by gentle shutdown
	}
}



// SetVideo : set resoulution and fullscreen/windowed mode
int		cShell::SetVideo		(int iCX,int iCY,int iBPP,bool bFullscreen)
{
	SDL_Surface* screen;
	int iFlags;

	// if iBPP is 0, use current colordepth
	if (iBPP == 0)
	{
		const SDL_VideoInfo* info;
		info = SDL_GetVideoInfo();
		if (!info)
		{
			printf("Video query failed: %s",SDL_GetError());
			iBPP = 16;
		} else iBPP = info->vfmt->BitsPerPixel;
	}

	// fullscreen or windowed mode
	if (bFullscreen)
			iFlags = SDL_OPENGL | SDL_FULLSCREEN;
	else	iFlags = SDL_OPENGL | SDL_SWSURFACE;

	// set video mode
    screen = SDL_SetVideoMode(iCX, iCY, iBPP, iFlags);
    if (screen == NULL)
	{
        printf("Couldn't set %ix%ix%i video mode: %s",iCX,iCY,iBPP,SDL_GetError());
        return 0;
    }

	// store the current resolution in shell object
	iXRes = iCX;
	iYRes = iCY;

	return 1;
}



// SetProgramIcon : load an icon for window and taskbar
void	cShell::SetProgramIcon		(char* szPath)
{
	SDL_Surface* icon = IMG_Load(szPath);
	SDL_WM_SetIcon(icon,NULL);
	SDL_FreeSurface(icon);
}



// SetProgramCaption : set window title
void	cShell::SetProgramCaption	(char* szCaption)
{
	SDL_WM_SetCaption(szCaption,NULL);
}



// ShowCursor : show/hide cursor
void	cShell::ShowCursor	(bool bVisible)
{
	if (bVisible)
			SDL_ShowCursor(SDL_ENABLE);
	else	SDL_ShowCursor(SDL_DISABLE);
}



// GetTicks : Get time value milliseconds (Counts from programmstart ?)
int		cShell::GetTicks	()
{
	return SDL_GetTicks();
}



// returns a \n seperated list of all files in path
// "camouflage.png\ndata.txt\ndryforestground.jpg\ndryground.jpg\ndryleaves.jpg\n"
char*	cShell::ListFiles	(const char* path)
{
	cString pattern = path;
	cString result = "";
	pattern.Appendf("/*");
	// path without ending / : "data/maps"

	#ifdef WIN32

		WIN32_FIND_DATA finddata;
		HANDLE search = FindFirstFile(*pattern,&finddata);

		if (search == INVALID_HANDLE_VALUE) return 0;

		if ((finddata.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY) == 0)
			result.Appendf("%s\n",finddata.cFileName);

		while (FindNextFile(search,&finddata))
		{
			if ((finddata.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY) == 0)
				result.Appendf("%s\n",finddata.cFileName);
		}

		return strdup(*result);
	#else
		DIR *d;
		struct dirent *e;
	
		d = opendir(path);
		if (!d) return 0;
		e = readdir(d);
		if (!e) return 0;
		while ( e != NULL ) {
			if(e->d_type != DT_DIR) result.Appendf("%s\n",e->d_name);
			e = readdir(d);
		}
		closedir(d);
		
		if (strlen(*result) == 0) return 0;
		return strdup(*result);
	#endif
	
}

// returns a \n seperated list of all dirs in path
// WARNING! also returns ../ and ./
// "..\n.\nmydir\nmydir2\n"
char*	cShell::ListDirs	(const char* path)
{
	cString pattern = path;
	cString result = "";
	pattern.Appendf("/*");
	// path without ending / : "data/maps"

	#ifdef WIN32

		WIN32_FIND_DATA finddata;
		HANDLE search = FindFirstFile(*pattern,&finddata);

		if (search == INVALID_HANDLE_VALUE) return 0;

		if ((finddata.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY) != 0)
			result.Appendf("%s\n",finddata.cFileName);

		while (FindNextFile(search,&finddata))
		{
			if ((finddata.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY) != 0)
				result.Appendf("%s\n",finddata.cFileName);
		}

		return strdup(*result);

	#endif// WIN32

	// TODO : implement me for linux !
}


// does not write the zero terminator !
void	cShell::Write		(const char* file,const char* str)
{
	Write(file,str,strlen(str));
}

void	cShell::Write		(const char* file,const char* str,const int len)
{
	FILE* fp = fopen(file,"wb");
	if (!fp) return;
	fwrite(str,1,len,fp);
	fclose(fp);
}


// does not write the zero terminator !
void	cShell::Append		(const char* file,const char* str)
{
	Append(file,str,strlen(str));
}

void	cShell::Append		(const char* file,const char* str,const int len)
{
	FILE* fp = fopen(file,"ab");
	if (!fp) return;
	fwrite(str,1,len,fp);
	fclose(fp);
}

// returns zeroterminated string containing the complete file content
char*	cShell::Read		(const char* file)
{
	int len;
	return Read(file,len);
}

// returns zeroterminated string containing the complete file content
// and length (without zero terminator) in len param
char*	cShell::Read		(const char* file,int &len)
{
	if (!file) return 0;

	FILE* fp = fopen(file,"rb");
	if (!fp) return 0;

	fseek(fp,0,SEEK_END);
	len = ftell(fp);
	char* res = (char*)malloc(len+1);
	fseek(fp,0,SEEK_SET);
	fread(res,1,len,fp);
	res[len] = 0;
	fclose(fp);

	return res;
}

void	cShell::ParseText	(const char* text,cStringHashMap* pMap)
{
	if (!pMap || !text) return;

	// no escape of quotes in value possible !!!
	const char* white = " \t\r\n";
	const char* namerange = "a-zA-Z0-9_";
	const char* valuerange = "a-zA-Z0-9_/\\\\.,;:()[]{}<>|~#!§$%&*+-@öäüÖÄÜ";
	const char* quotes = "\"'";
	const char* quote = 0;
	int	a;
	cString sKey;
	cString sValue;
	
	while (*text)
	{
		// skip whitespace
		text += cinrange(text,white);
		// read key
		a = cinrange(text,namerange);
		sKey = "";
		sKey.Append(text,a);
		text += a;
		// skip whitespace
		text += cinrange(text,white);
		// check for =
		assert(*text == '=' && "cShell::ParseText = expected");
		if (*text) ++text;
		// skip whitespace
		text += cinrange(text,white);
		// read value
		if (charmatchrange(*text,quotes))
		{
			// quoted value
			quote = text;
			for (++text;*text && *text != *quote;++text) ;
			sValue = "";
			sValue.Append(text,text - quote - 1);
			if (*text == *quote) ++text;
		} else {
			// direct value
			a = cinrange(text,valuerange);
			sValue = "";
			sValue.Append(text,a);
			text += a;
		}
		pMap->insert(strdup(*sValue),*sKey);
	}
}

void	cShell::ParseFile	(const char* file,cStringHashMap* pMap)
{
	if (!pMap) return;

	char* pData = Read(file);
	if (pData) 
	{
		ParseText(pData,pMap);
		free(pData);
	}
}

// ****** ****** ****** end

