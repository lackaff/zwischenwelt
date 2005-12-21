// ****** ****** ****** map.h
#ifndef _MAP_H_
#define _MAP_H_

#include <vector> 
#include <map> 
#include <geometry.h> 
#include <terrain.h> 

#include <SDL/SDL.h>
#include <os.h>
#include <GL/glew.h>
#include <GL/gl.h>	// OpenGL32 Library

class SDL_Surface;
	
// ****** ****** ****** map
	
#define kParts_Per_Row (40)
#define kPartSize 2
#define kPatchUnit (16.0)
#define kPartUnit (kPatchUnit/(float)(kPartSize))

class cMap {
public : 
	static cMap* instance ();
	cMap	();
	~cMap	();

	class cMapPart;
	class cTerrain {
		public : 
		int		type,nwse,x,y;
		cTerrain	(const unsigned char*& reader);
		void	TerrainMod	(cMapPart* mappart);
		void	Draw		(cMapPart* mappart);
	};
	class cBuilding {
		public : 
		int		type,nwse,level,user,x,y;
		cBuilding	(const unsigned char*& reader);
		void	Draw	(cMapPart* mappart);
	};
	
	class cBuildingType {
		public : 
		std::string 				name,gfx;
		int							speed,maxhp;
		std::vector<cM_Grafik*>		tex;
		cBuildingType				(const char* name,const char* gfx,int speed,int maxhp);
	};
	
	class cMapPart : public cTerrainPatch {
		public : 
		std::vector<cTerrain*>		terrain;
		std::vector<cBuilding*>		building;
		int 						x,y;
		bool						hasberg,haswasser;
	
		cMapPart		(int x,int y);
		~cMapPart		();
		void	DrawObjects		();
		void	DrawNWSEWall	(int x,int y,int nwse,float wall_w,float wall_h,cM_Texture* tex,bool cap_ends = false);
	};
	
	void	RecenterParts	(vector3d& cam);
	void	RecenterParts	(int partx,int party);
	void	NeighborParts	();
	
	static SDL_Surface* 		pHeightFieldBase;
	static cM_Texture*			pWallTex;
	static cM_Texture*			pWegTex;
	static cM_Texture*			pSeeTex;
	static cM_Texture*			pBergTex;
	static cM_Grafik*			pWaldTex;
	std::map<int,cBuildingType*>			buildingtype;
	std::map<int,std::vector<cTerrain*> >	terrain;
	std::map<int,std::vector<cBuilding*> >	building;
	std::vector<cMapPart*>		parts;
	int 						part_ltx,part_lty;
	
	static int	mapread1 (const unsigned char*& reader);
	static int	mapread2 (const unsigned char*& reader);
	static int	mapread4 (const unsigned char*& reader);
	
	void	Init	(const char* mapfile,const char* gfxdir,const char* heightfield,int startx,int starty);
	void	Draw	(vector3d o,vector3d dir,vector3d& cam);
};
	
#endif
// ****** ****** ****** END
