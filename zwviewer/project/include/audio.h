#ifndef _AUDIO_H_
#define _AUDIO_H_

#if 0
#include <SDL/SDL.h>
#include <SDL/SDL_mixer.h>

class cAudio
{
public:
	static cAudio *instance();
	static void shutdown();

	void playMusic(const char *szFile);
	void playSound(const char *szFile);


private:
	cAudio();
	~cAudio();
	static cAudio *smInstance;

	int mAudio_rate,mAudio_channels,mBits;
	unsigned short mAudio_format;
	const unsigned int mAudio_buffers;

	Mix_Music *music;
};

#endif

#endif