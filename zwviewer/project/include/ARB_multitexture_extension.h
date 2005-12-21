//////////////////////////////////////////////////////////////////////////////////////////
//	ARB_multitexture_extension.h
//	Extension setup header
//	You may use this code however you wish, but if you do, please credit me and
//	provide a link to my website in a readme file or similar
//	Downloaded from: www.paulsprojects.net
//	Created:	26th August 2002
//////////////////////////////////////////////////////////////////////////////////////////	

#ifndef ARB_MULTITEXTURE_EXTENSION_H
#define ARB_MULTITEXTURE_EXTENSION_H

bool SetUpARB_multitexture();
extern bool ARB_multitexture_supported;

extern void APIENTRY glActiveTextureARB (GLenum);
extern void APIENTRY glClientActiveTextureARB (GLenum);

/*
extern PFNGLACTIVETEXTUREARBPROC				glActiveTextureARB;
extern PFNGLCLIENTACTIVETEXTUREARBPROC			glClientActiveTextureARB;
extern PFNGLMULTITEXCOORD1DARBPROC				glMultiTexCoord1dARB;
extern PFNGLMULTITEXCOORD1DVARBPROC				glMultiTexCoord1dvARB;
extern PFNGLMULTITEXCOORD1FARBPROC				glMultiTexCoord1fARB;
extern PFNGLMULTITEXCOORD1FVARBPROC				glMultiTexCoord1fvARB;
extern PFNGLMULTITEXCOORD1IARBPROC				glMultiTexCoord1iARB;
extern PFNGLMULTITEXCOORD1IVARBPROC				glMultiTexCoord1ivARB;
extern PFNGLMULTITEXCOORD1SARBPROC				glMultiTexCoord1sARB;
extern PFNGLMULTITEXCOORD1SVARBPROC				glMultiTexCoord1svARB;
extern PFNGLMULTITEXCOORD2DARBPROC				glMultiTexCoord2dARB;
extern PFNGLMULTITEXCOORD2DVARBPROC				glMultiTexCoord2dvARB;
extern PFNGLMULTITEXCOORD2FARBPROC				glMultiTexCoord2fARB;
extern PFNGLMULTITEXCOORD2FVARBPROC				glMultiTexCoord2fvARB;
extern PFNGLMULTITEXCOORD2IARBPROC				glMultiTexCoord2iARB;
extern PFNGLMULTITEXCOORD2IVARBPROC				glMultiTexCoord2ivARB;
extern PFNGLMULTITEXCOORD2SARBPROC				glMultiTexCoord2sARB;
extern PFNGLMULTITEXCOORD2SVARBPROC				glMultiTexCoord2svARB;
extern PFNGLMULTITEXCOORD3DARBPROC				glMultiTexCoord3dARB;
extern PFNGLMULTITEXCOORD3DVARBPROC				glMultiTexCoord3dvARB;
extern PFNGLMULTITEXCOORD3FARBPROC				glMultiTexCoord3fARB;
extern PFNGLMULTITEXCOORD3FVARBPROC				glMultiTexCoord3fvARB;
extern PFNGLMULTITEXCOORD3IARBPROC				glMultiTexCoord3iARB;
extern PFNGLMULTITEXCOORD3IVARBPROC				glMultiTexCoord3ivARB;
extern PFNGLMULTITEXCOORD3SARBPROC				glMultiTexCoord3sARB;
extern PFNGLMULTITEXCOORD3SVARBPROC				glMultiTexCoord3svARB;
extern PFNGLMULTITEXCOORD4DARBPROC				glMultiTexCoord4dARB;
extern PFNGLMULTITEXCOORD4DVARBPROC				glMultiTexCoord4dvARB;
extern PFNGLMULTITEXCOORD4FARBPROC				glMultiTexCoord4fARB;
extern PFNGLMULTITEXCOORD4FVARBPROC				glMultiTexCoord4fvARB;
extern PFNGLMULTITEXCOORD4IARBPROC				glMultiTexCoord4iARB;
extern PFNGLMULTITEXCOORD4IVARBPROC				glMultiTexCoord4ivARB;
extern PFNGLMULTITEXCOORD4SARBPROC				glMultiTexCoord4sARB;
extern PFNGLMULTITEXCOORD4SVARBPROC				glMultiTexCoord4svARB;
*/

#endif	//ARB_MULTITEXTURE_EXTENSION_H