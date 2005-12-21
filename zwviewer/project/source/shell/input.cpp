// ****** ****** ****** input.cpp
#include <string.h>
#include <input.h>
#include <SDL/SDL.h>


// key definition in accordance with win32 virtual keycodes,
// partially overlapps with SDL keycodes
const char * cInput::szKeyNames[256] = 
{
	"",// 0x00
	"mouse1",
	"mouse2",
	"mouse3",
	"mouse4",
	"mouse5",
	"wheelup",
	"wheeldown",
	"backspace",
	"tab",
	"",// 0x0A
	"",
	"stopclear",
	"return",
	"np_enter",
	"",


	"lshift",// 0x10
	"lcontrol",
	"lalt",
	"pause",
	"capslock",
	"","","","","",
	"",// 0x1a
	"escape",
	"","","","",


	"space",// 0x20
	"pgup",
	"pgdn",
	"end",
	"home",
	"left",
	"up",
	"right",
	"down",
	"",
	"",// 0x2a
	"",
	"screen",
	"ins",
	"del",
	"",


	"0",// 0x30
	"1",
	"2",
	"3",
	"4",
	"5",
	"6",
	"7",
	"8",
	"9",
	"",// 0x3a
	"","","","","",


	"",// 0x40
	"a",
	"b",
	"c",
	"d",
	"e",
	"f",
	"g",
	"h",
	"i",
	"j",
	"k",// 0x4a
	"l",
	"m",
	"n",
	"o",


	"p",// 0x50
	"q",
	"r",
	"s",
	"t",
	"u",
	"v",
	"w",
	"x",
	"y",
	"z",// 0x5a
	"lwin",
	"rwin",
	"menu",
	"",
	"",



	"np0",// 0x60
	"np1",
	"np2",
	"np3",
	"np4",
	"np5",
	"np6",
	"np7",
	"np8",
	"np9",
	"npmult",// 0x6a
	"npadd",
	"",
	"npsub",
	"npkomma",
	"npdiv",


	"f1",// 0x70
	"f2",
	"f3",
	"f4",
	"f5",
	"f6",
	"f7",
	"f8",
	"f9",
	"f10",
	"f11",// 0x7a
	"f12",
	"f13",
	"f14",
	"f15",
	"",


	// 0x80
	"","","","","","","","","","","","","","","","",

	// 0x90
	"numlock","scroll",
	"","","","","","","","","","","","","","",

	// 0xA0
	"","rshift",
	"","rcontrol",
	"","ralt",
	"","","","","","","","","","",

	// 0xB0
	"","","","","","","","","","",
	"ue",
	"plus",
	"komma",
	"minus",
	"point",
	"grid",

	// 0xC0
	"oe","","","","","","","","","","","","","","","",

	// 0xD0
	"","","","","","","","","","","",
	"bslash",
	"console",
	"accent",
	"ae",
	"",

	// 0xE0
	"","","greater","","","","","","","","","","","","","",

	// 0xF0
	"","","","","","","","","","","","","","","",""
};



// ****** ****** ****** Keyboard and Mouse



cInput gInput;



// Constructor
// desc :		init all to zero
// params :		none
cInput::cInput	()
{
	Reset();
}




// KeyConvertWin
// desc :		convert a win32 virtual keycode to platform independant representation
// params :
//	- iVKey		virtual keycode
//	- bRight	bRight extended key -> right control differentiation
unsigned char		cInput::KeyConvertWin	(const int iVKey,const bool bRight)
{
	if (bRight && iVKey == kkey_lshift)		return kkey_rshift;
	if (bRight && iVKey == kkey_lcontrol)	return kkey_rcontrol;
	if (bRight && iVKey == kkey_lalt)		return kkey_ralt;
	if (bRight && iVKey == kkey_return)		return kkey_np_enter;
	
	return iVKey;
}





// KeyConvertSDL
// desc :		convert a sdl virtual keycode to platform independant representation
// params :
//	- iVKey		sdlkeysym or something
unsigned char		cInput::KeyConvertSDL	(const int iVKey)
{
	if (iVKey == SDLK_BACKQUOTE)		return kkey_console;
	if (iVKey >= SDLK_a && iVKey <= SDLK_z)		return kkey_a + iVKey - 'a';
	if (iVKey >= SDLK_0 && iVKey <= SDLK_9)		return kkey_0 + iVKey - SDLK_0;
	if (iVKey >= SDLK_KP0 && iVKey <= SDLK_KP9)	return kkey_numpad0 + iVKey - SDLK_KP0;
	if (iVKey >= SDLK_F1 && iVKey <= SDLK_F15)	return kkey_f1 + iVKey - SDLK_F1;

	switch (iVKey)
	{
		break;case SDLK_ESCAPE:		return	kkey_escape;
		break;case SDLK_SYSREQ:		return	kkey_screen;
		break;case SDLK_SCROLLOCK:	return	kkey_scroll;
		break;case SDLK_PAUSE:		return	kkey_pause;
		break;case SDLK_BACKQUOTE:	return	kkey_console;
		
		//break;case SDLK_BACKQUOTE:	return	kkey_bslash;
		//break;case SDLK_BACKQUOTE:	return	kkey_accent;
		break;case SDLK_BACKSPACE:	return	kkey_back;

		break;case SDLK_PAGEUP:		return	kkey_prior;
		break;case SDLK_PAGEDOWN:	return	kkey_next;
		break;case SDLK_END:		return	kkey_end;
		break;case SDLK_HOME:		return	kkey_home;
		break;case SDLK_INSERT:		return	kkey_ins;
		break;case SDLK_DELETE:		return	kkey_del;

		break;case SDLK_LEFT:		return	kkey_left;
		break;case SDLK_UP:			return	kkey_up;
		break;case SDLK_RIGHT:		return	kkey_right;
		break;case SDLK_DOWN:		return	kkey_down;

		break;case SDLK_KP_MULTIPLY:	return	kkey_np_mult;
		break;case SDLK_KP_PLUS:		return	kkey_np_add;
		break;case SDLK_KP_MINUS:		return	kkey_np_sub;
		break;case SDLK_KP_PERIOD:		return	kkey_np_komma;
		break;case SDLK_KP_DIVIDE:		return	kkey_np_div;
		break;case SDLK_NUMLOCK:		return	kkey_numlock;
		
		break;case SDLK_LSHIFT:		return	kkey_lshift;
		break;case SDLK_RSHIFT:		return	kkey_rshift;
		break;case SDLK_LCTRL:		return	kkey_lcontrol;
		break;case SDLK_RCTRL:		return	kkey_rcontrol;
		break;case SDLK_LALT:		return	kkey_lalt;
		break;case SDLK_RALT:		return	kkey_ralt;
		break;case SDLK_LMETA:		return	kkey_lwin;
		break;case SDLK_RMETA:		return	kkey_rwin;

		break;case SDLK_CAPSLOCK:	return	kkey_capslock;
		break;case SDLK_MENU:		return	kkey_menu;
		
		break;case SDLK_TAB:		return	kkey_tab;
		break;case SDLK_RETURN:		return	kkey_return;
		break;case SDLK_KP_ENTER:	return	kkey_np_enter;
		break;case SDLK_SPACE:		return	kkey_space;
		
		break;case SDLK_RIGHTBRACKET:	return	kkey_plus;
		break;case SDLK_BACKSLASH:		return	kkey_grid;
		break;case SDLK_SLASH:			return	kkey_minus;
		break;case SDLK_PERIOD:			return	kkey_point;

		break;case SDLK_COMMA:			return	kkey_komma;
		//break;case SDLK_BACKSLASH:		return	kkey_greater; // DOUBLE

		break;case SDLK_LEFTBRACKET:	return	kkey_ue;
		break;case SDLK_QUOTE:			return	kkey_ae;
		break;case SDLK_SEMICOLON:		return	kkey_oe;
	}

	printf("KeyConvertSDL found an unknown key : %i",iVKey);

	return iVKey;
}





// GetNamedKey
// desc :		get key number for a given key name
// params :
//	- szName	key name
unsigned char		cInput::GetNamedKey		(const char* szName)
{
	int i;for (i=0;i<256;i++)
	if (*szKeyNames[i] != 0)
	if (stricmp(szKeyNames[i],szName) == 0)
		return i;
	return 0;
}



// Reset
// desc :		resets all keys, mousebuttons, and the wheel
// params :		none
void			cInput::Reset	()
{	
	memset(bKeys,0,sizeof(bool)*256);
	memset(bKeyPress,0,sizeof(bool)*256);
	memset(bButton,0,sizeof(bool)*3);
	iMouseWheel = 0;
}





// KeyDown
// desc :		register keypush (and autokeyrepeat)
// params :
//	- iKey		key number
//	- bFirst	really first push, or only autorepeat
void	cInput::KeyDown			(const unsigned char iKey)
{
	// call callback
	if (pKeyDownFunc) (*pKeyDownFunc)(iKey);

	// check if it is the first keystrike or a repetition by checking the stored keystate.
	// if bKeys[iKey] is false, it is the first strike
	// if bKeys[iKey] is true, it is just a repetition

	// update records
	bKeys[iKey] = 1;
	if (bKeyPress[iKey] < 100) bKeyPress[iKey]++;
	if (iKey == kkey_mouse1) 	bButton[0] = 1;
	if (iKey == kkey_mouse2) 	bButton[1] = 1;
	if (iKey == kkey_mouse3) 	bButton[2] = 1;
	if (iKey == kkey_wheelup) 	iMouseWheel++;
	if (iKey == kkey_wheeldown) iMouseWheel--;

	/*
	// console keys
	gConsole.EnterKey(iKey);

	if (gConsole.iReqHeight == 0 && !bKeys[iKey])
	if (gConsole.szBinds[iKey] != NULL)
	{
		// console game keys 
		// THIS IS WHERE CONTROL KEYS AND MOUSEBUTTON ACTIONS ARE PROCESSED
		
		// step trough lines
		int		off = 0;
		int		len = strlen(gConsole.szBinds[iKey]);
		while (off < len)
		{
			int lenline = Linelen(&gConsole.szBinds[iKey][off]);
			gConsole.ExecuteText(&gConsole.szBinds[iKey][off]);
			off += lenline+1; // len of line + 1 -> skip \n char
		}
	}

	gConsole.EnterChar(event.key.keysym.unicode & 0x7F);
	*/
}





// KeyUp
// desc :		register keyrelease
// params :
//	- iKey		key number
void	cInput::KeyUp			(const unsigned char iKey)
{
	// call callback
	if (pKeyUpFunc) (*pKeyUpFunc)(iKey);

	// update records
	bKeys[iKey] = 0;
	bKeyPress[iKey] = 0;
	if (iKey == kkey_mouse1) bButton[0] = 0;
	if (iKey == kkey_mouse2) bButton[1] = 0;
	if (iKey == kkey_mouse3) bButton[2] = 0;
	
	/*
	if (gConsole.iReqHeight == 0)
	if (gConsole.szRetroBinds[iKey] != NULL)
	{
		// step trough lines
		int		off = 0;
		int		len = strlen(gConsole.szRetroBinds[iKey]);
		while (off < len)
		{
			int lenline = Linelen(&gConsole.szRetroBinds[iKey][off]);
			gConsole.ExecuteText(&gConsole.szRetroBinds[iKey][off]);
			off += lenline+1; // len of line + 1 -> skip \n char
		}
	}*/
}




// ****** ****** ****** END
