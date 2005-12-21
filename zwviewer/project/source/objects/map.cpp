// ****** ****** ****** map.cpp
#include <prefix.h> 
#include <stdio.h>
#include <stdlib.h> 
#include <shell.h> 
#include <map.h> 
#include <algorithm> 
#include <roblib.h> 
#include <assert.h> 


#include <geometry.h>
#include <math.h>
#include <input.h>
#include <media.h>
#include <terrain.h>
#include <drawutils.h>
#include <os.h>
#include <SDL/SDL.h>
#include <GL/gl.h>	// OpenGL32 Library
#include <GL/glu.h>	// GLu32 Library
#include <roblib.h> 
#include <string> 

extern vector3d	gvCampos;
extern vector3d	gvX,gvY,gvZ;


// TODO : glCullFace(GL_FRONT); was neccessary, default is back, adjust ?

using namespace	std;

 
SDL_Surface* cMap::pHeightFieldBase = 0;
cM_Texture* cMap::pWallTex = 0;
cM_Texture* cMap::pWegTex = 0;
cM_Texture* cMap::pSeeTex = 0;
cM_Texture* cMap::pBergTex = 0;
cM_Grafik* cMap::pWaldTex = 0;

cMap* cMap::instance () {
	static cMap* pSingleton = 0;
	if (!pSingleton) pSingleton = new cMap();
	return pSingleton;
}

cMap::cMap () : parts(kParts_Per_Row*kParts_Per_Row) { 
	// textures : 
	pWallTex = cTextureManager::getinstance()->get("data/wall.bmp");
	pWegTex = cTextureManager::getinstance()->get("data/weg.bmp");
	pSeeTex = cTextureManager::getinstance()->get("data/see.png");
	pBergTex = cTextureManager::getinstance()->get("data/stony.jpg");
	pWaldTex = cGraficManager::getinstance()->get("../gfx/wald/wald-.png");
	//pBergTex = cGraficManager::getinstance()->get("../gfx/mountain/berg-.png");
	// id,name,gfx,speed,maxhp
	buildingtype[1] = new cBuildingType("Haupthaus", "gebaeude-r%R%/hq-%L%.png", 0, 5000),
	buildingtype[2] = new cBuildingType("Magieturm", "gebaeude-r%R%/magitower-%L%.png", 0, 480),
	buildingtype[3] = new cBuildingType("Weg", "path/path-%NWSE%-%L%.png", 60, 5),
	buildingtype[4] = new cBuildingType("BROID - Obelisk", "gebaeude-r%R%/broid-%L%.png", 0, 999999999),
	buildingtype[5] = new cBuildingType("Wall", "wall/wall-%NWSE%-%L%.png", 0, 120),
	buildingtype[6] = new cBuildingType("Haus", "gebaeude-r%R%/house-%L%.png", 0, 100),
	buildingtype[7] = new cBuildingType("Lager", "gebaeude-r%R%/lager-%L%.png", 0, 200),
	buildingtype[8] = new cBuildingType("Kaserne", "gebaeude-r%R%/barracks-%L%.png", 0, 150),
	buildingtype[9] = new cBuildingType("Bauernhof", "gebaeude-r%R%/farm-%L%.png", 0, 100),
	buildingtype[10] = new cBuildingType("Entwickleranstalt", "gebaeude-r%R%/hospital-%L%.png", 0, 100),
	buildingtype[11] = new cBuildingType("Werkstatt", "gebaeude-r%R%/werkstatt-%L%.png", 0, 200),
	buildingtype[12] = new cBuildingType("Schmiede", "gebaeude-r%R%/schmiede-%L%.png", 0, 100),
	buildingtype[13] = new cBuildingType("Holzf&auml;ller", "gebaeude-r%R%/holzfaeller-%L%.png", 0, 100),
	buildingtype[14] = new cBuildingType("Steinmetz", "gebaeude-r%R%/steinmetz-%L%.png", 0, 100),
	buildingtype[15] = new cBuildingType("Eisenmine", "gebaeude-r%R%/eisenmine-%L%.png", 0, 100),
	buildingtype[16] = new cBuildingType("Marktplatz", "gebaeude-r%R%/marketplace-%L%.png", 0, 100),
	buildingtype[17] = new cBuildingType("Tor", "gate/tor-zu-%NWSE%-%L%.png", 60, 90),
	buildingtype[18] = new cBuildingType("Br&uuml;cke", "gate/bridge-%NWSE%-%L%.png", 60, 10),
	buildingtype[19] = new cBuildingType("Schild", "gebaeude-r%R%/schild-%L%.png", 60, 10),
	buildingtype[20] = new cBuildingType("Tempel", "gebaeude-r%R%/tempel-%L%.png", 0, 60),
	buildingtype[21] = new cBuildingType("Höllenschlund", "landschaft/hellhole-%L%.gif", 0, 300),
	buildingtype[22] = new cBuildingType("Schachplatz", "gebaeude-r%R%/schachplatz-%L%.png", 0, 50),
	buildingtype[23] = new cBuildingType("Portal", "gate/portal-zu-%L%.png", 0, 900),
	buildingtype[24] = new cBuildingType("Torbr&uuml;cke", "gate/gb-zu-%NWSE%-%L%.png", 120, 90),
	buildingtype[25] = new cBuildingType("Brunnen", "gebaeude-r%R%/brunnen-%L%.png", 60, 10),
	buildingtype[26] = new cBuildingType("LagerRuine", "gebaeude-r%R%/lager-dead.png", 180, 200),
	buildingtype[27] = new cBuildingType("MagieturmRuine", "gebaeude-r%R%/magitower-dead.png", 180, 480),
	buildingtype[28] = new cBuildingType("MartkplatzRuine", "gebaeude-r%R%/marketplace-dead.png", 180, 100),
	buildingtype[29] = new cBuildingType("SchachplatzRuine", "gebaeude-r%R%/schachplatz-dead.png", 180, 50),
	buildingtype[30] = new cBuildingType("SchildRuine", "gebaeude-r%R%/schild-dead.png", 180, 10),
	buildingtype[31] = new cBuildingType("SchmiedeRuine", "gebaeude-r%R%/schmiede-dead.png", 180, 100),
	buildingtype[32] = new cBuildingType("SteinmetzRuine", "gebaeude-r%R%/steinmetz-dead.png", 180, 100),
	buildingtype[33] = new cBuildingType("TempelRuine", "gebaeude-r%R%/tempel-dead.png", 180, 60),
	buildingtype[34] = new cBuildingType("WerkstattRuine", "gebaeude-r%R%/werkstatt-dead.png", 180, 200),
	buildingtype[35] = new cBuildingType("BauernhofRuine", "gebaeude-r%R%/farm-dead.png", 0, 100),
	buildingtype[36] = new cBuildingType("EisenminenRuine", "gebaeude-r%R%/eisenmine-dead.png", 180, 100),
	buildingtype[37] = new cBuildingType("EntwickleranstaltRuine", "gebaeude-r%R%/hospital-dead.png", 180, 100),
	buildingtype[38] = new cBuildingType("KasernenRuine", "gebaeude-r%R%/barracks-dead.png", 180, 150),
	buildingtype[39] = new cBuildingType("BROID - Ruine", "gebaeude-r%R%/broid-dead.png", 180, 999999999),
	buildingtype[40] = new cBuildingType("Holzf&auml;llerRuine", "gebaeude-r%R%/holzfaeller-dead.png", 180, 100),
	buildingtype[41] = new cBuildingType("HausRuine", "gebaeude-r%R%/house-dead.png", 180, 100),
	buildingtype[42] = new cBuildingType("HaupthausRuine", "gebaeude-r%R%/hq-dead.png", 180, 5000),
	buildingtype[43] = new cBuildingType("Teehaus", "gebaeude-r%R%/teahouse-%L%.png", 60, 50),
	buildingtype[44] = new cBuildingType("G&auml;rtnerei", "gebaeude-r%R%/gaertnerei-%L%.png", 0, 0),
	buildingtype[45] = new cBuildingType("Observatorium", "gebaeude-r%R%/observe-%L%.png", 0, 500);
	
}
cMap::~cMap () { delete_clear(terrain); delete_clear(building); delete_clear(parts); }


// cMapPart

	// alle objekcte im ersten vector die in dem angegebenen xy bereich liegen in den 2ten vector stopfen.
	template<typename _data> 
	void add_if_in (int minx,int miny,int maxx,int maxy,std::vector<_data*>& src,std::vector<_data*>& dst) {
		for (std::vector<_data*>::iterator itor = src.begin();itor != src.end();++itor)
			if ((*itor)->x >= minx && (*itor)->x < maxx && (*itor)->y >= miny && (*itor)->y < maxy)
				dst.push_back(*itor);
	}
	
	cMap::cMapPart::cMapPart	(int x,int y) : x(x),y(y),hasberg(false),haswasser(false),
		cTerrainPatch(vector3d(x*16,y*16,0),cMap::pHeightFieldBase,x*16,y*16) {
		cMap* map = cMap::instance();
		int minx = (x)*kPartSize;
		int miny = (y)*kPartSize;
		int maxx = (x+1)*kPartSize;
		int maxy = (y+1)*kPartSize;
		add_if_in(minx-kPartSize,miny-kPartSize,maxx+kPartSize,maxy+kPartSize,map->terrain[x-1],terrain);
		add_if_in(minx-kPartSize,miny-kPartSize,maxx+kPartSize,maxy+kPartSize,map->terrain[x],terrain);
		add_if_in(minx-kPartSize,miny-kPartSize,maxx+kPartSize,maxy+kPartSize,map->terrain[x+1],terrain);
		add_if_in(minx,miny,maxx,maxy,map->building[x],building);
			
		pRaw->AddMaterial("data/gras364.jpg");
		for_each_call(terrain,&cTerrain::TerrainMod,this); 
		pRaw->pTer->Recalc();
		pRaw->GenLight(vector3d(0.5,0.5,-0.5));
		pRaw->RenderAlphaLight();
	}
	cMap::cMapPart::~cMapPart () { /* no clear of terrain,building, mainpointer in cMap */ }
	void cMap::cMapPart::DrawObjects () {
		if (haswasser) {
			pSeeTex->Bind();
			glBegin(GL_TRIANGLE_STRIP);
			glTexCoord2f(0,0);glVertex3f(vPos.x,vPos.y,vPos.z+1.0);
			glTexCoord2f(1,0);glVertex3f(vPos.x+kPatchUnit,vPos.y,vPos.z+1.0);
			glTexCoord2f(0,1);glVertex3f(vPos.x,vPos.y+kPatchUnit,vPos.z+1.0);
			glTexCoord2f(1,1);glVertex3f(vPos.x+kPatchUnit,vPos.y+kPatchUnit,vPos.z+1.0);
			glEnd();
		}
		for_each_call(terrain,&cTerrain::Draw,this); 
		for_each_call(building,&cBuilding::Draw,this); 
	}
	
	
	void cMap::cMapPart::DrawNWSEWall (int nx,int ny,int nwse,float wall_w,float wall_h,cM_Texture* tex,bool cap_ends) {
		int dx = nx-x*kPartSize;
		int dy = ny-y*kPartSize;
		float h2,h = GetH(0.5*kPartUnit+kPartUnit*dx,0.5*kPartUnit+kPartUnit*dy);
		vector3d a = vPos + vector3d(kPartUnit*(dx+0.5),kPartUnit*(dy+0.5),h);
		bool midcap = !(((nwse & 1) && (nwse & 4)) || ((nwse & 2) && (nwse & 8)));
		bool endcap = nwse == 0 || cap_ends;
		if ((nwse & 1) || nwse == 0) {
			h2 = GetH(0.5*kPartUnit+kPartUnit*dx,0.01*kPartUnit+kPartUnit*dy);
			DrawWall(a,a+vector3d(0,-0.5*kPartUnit,h2-h),wall_w,wall_h,midcap,endcap,tex);
		}
		if ((nwse & 8) || nwse == 0) {
			h2 = GetH(0.01*kPartUnit+kPartUnit*dx,0.5*kPartUnit+kPartUnit*dy);
			DrawWall(a,a+vector3d(-0.5*kPartUnit,0,h2-h),wall_w,wall_h,midcap,endcap,tex);
		}
		if ((nwse & 4) || nwse == 0) {
			h2 = GetH(0.5*kPartUnit+kPartUnit*dx,0.99*kPartUnit+kPartUnit*dy);
			DrawWall(a,a+vector3d(0,0.5*kPartUnit,h2-h),wall_w,wall_h,midcap,endcap,tex);
		}
		if ((nwse & 2) || nwse == 0) {
			h2 = GetH(0.99*kPartUnit+kPartUnit*dx,0.5*kPartUnit+kPartUnit*dy);
			DrawWall(a,a+vector3d(0.5*kPartUnit,0,h2-h),wall_w,wall_h,midcap,endcap,tex);
		}
	}

	void	cMap::RecenterParts	(vector3d& cam) {
		RecenterParts(floor(cam.x / 16.0) , floor(cam.y / 16.0));
	}
	void	cMap::RecenterParts	(int partx,int party) {
		int diffx = partx - kParts_Per_Row/2 - part_ltx;
		int diffy = party - kParts_Per_Row/2 - part_lty;
		if (diffx == 0 && diffy == 0) return;
		printf("cam above (%d,%d)\n",-partx*kPartSize,party*kPartSize);
		part_ltx += diffx;
		part_lty += diffy;
		SDL_LockSurface(pHeightFieldBase);
		
		/*
		for (int x=0;x<kParts_Per_Row;++x)
		for (int y=0;y<kParts_Per_Row;++y) {
			if (parts[x+y*kParts_Per_Row]) delete parts[x+y*kParts_Per_Row];
			parts[x+y*kParts_Per_Row] = new cMapPart(x+part_ltx,y+part_lty);
		}
		*/
		
		
		//printf("diff %d,%d\n",diffx,diffy);
		
		int cx,cy,oldx,oldy,newx,newy,curpos;
		for (int y=0;y<kParts_Per_Row;++y) {
			for (int x=0;x<kParts_Per_Row;++x) {
				cx = (diffx>=0)?x:(kParts_Per_Row-1-x); // if diffx is negative walk from right to left because olddata being dumped is right
				cy = (diffy>=0)?y:(kParts_Per_Row-1-y);
				oldx = cx + diffx;
				oldy = cy + diffy;
				newx = cx - diffx;
				newy = cy - diffy;
				curpos = cx+cy*kParts_Per_Row;
				
				// dump old data only if its new position would be outside
				if (newx >= kParts_Per_Row || newx < 0 || newy >= kParts_Per_Row || newy < 0)
					if (parts[cx+cy*kParts_Per_Row]) delete parts[cx+cy*kParts_Per_Row];
				
				if (oldx >= kParts_Per_Row || oldx < 0 || oldy >= kParts_Per_Row || oldy < 0)
						parts[curpos] = new cMapPart(cx+part_ltx,cy+part_lty);	// load new
				else	parts[curpos] = parts[oldx+oldy*kParts_Per_Row];		// reuse old data
			}
		}
		
		SDL_UnlockSurface(pHeightFieldBase);
		NeighborParts();
	}
	void	cMap::NeighborParts	() {
		for (int x=0;x<kParts_Per_Row;++x)
		for (int y=0;y<kParts_Per_Row;++y) {
			parts[x+y*kParts_Per_Row]->pL = (x<=0)?0:parts[(x-1)+(y+0)*kParts_Per_Row];
			parts[x+y*kParts_Per_Row]->pB = (y<=0)?0:parts[(x+0)+(y-1)*kParts_Per_Row];
			parts[x+y*kParts_Per_Row]->pR = (x>=kParts_Per_Row-1)?0:parts[(x+1)+(y+0)*kParts_Per_Row];
			parts[x+y*kParts_Per_Row]->pT = (y>=kParts_Per_Row-1)?0:parts[(x+0)+(y+1)*kParts_Per_Row];
		}
	}
 
// cTerrain
	
	cMap::cTerrain::cTerrain	(const unsigned char*& reader) {
		type = cMap::mapread1(reader);
		nwse = cMap::mapread1(reader);
		x = -cMap::mapread2(reader);
		y = cMap::mapread2(reader);
	}

	void	cMap::cTerrain::TerrainMod	(cMapPart* mappart) {
		int rand1 = iclip(x + 1111 * y , 255);
		int rand2 = iclip(1111 * x + y , 255);
		int dx = x-mappart->x*kPartSize;
		int dy = y-mappart->y*kPartSize;
		float fx = 0.1*kPartUnit+kPartUnit*dx; // + 0.5 would be middle, but to enable LOD...
		float fy = 0.1*kPartUnit+kPartUnit*dy;
		float h;
		switch (type) {
			case 3: // berg
				if (!mappart->hasberg) {
					// TODO : my linux does not support multitexture or so it seams....
					//mappart->pRaw->AddMaterial("data/stony.jpg");
					//mappart->pRaw->ClearA(0);
					mappart->hasberg = true;
				}
				assert(mappart->pRaw);
				mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,12.0+(rand1 / 255.0) * 10.0,3.0,0.7,fx,fy);
				mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,12.0+(rand2 / 255.0) * 10.0,5.0,1.0,fx+0.5*kPartUnit,fy+0.5*kPartUnit);
				//mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_BaseMat,cTerrainPatchRaw::kTool_Set,1.0,4.0,1.0,fx,fy);
				
			break;
			case 6: // see
			case 2: // fluss
				if (!mappart->haswasser) {
					mappart->haswasser = true;
				}
				assert(mappart->pRaw);
				if (nwse == 0 || nwse == 15 || type == 6) {
					h = -5.0;
					mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,h,10.0,1.5,fx,fy);
				} else {
					h = -2.0;
					mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,h,5.0,1.5,fx,fy);
					if ((nwse & 1)) mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,h,5.0,1.5,fx,fy-0.5*kPartUnit);
					if ((nwse & 8)) mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,h,5.0,1.5,fx-0.5*kPartUnit,fy);
					if ((nwse & 4)) mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,h,5.0,1.5,fx,fy+0.5*kPartUnit);
					if ((nwse & 2)) mappart->pRaw->Circle(cTerrainPatchRaw::kTarget_Terrain,cTerrainPatchRaw::kTool_Level,h,5.0,1.5,fx+0.5*kPartUnit,fy);
				}
			break;
		}
		
	}

	void	cMap::cTerrain::Draw	(cMapPart* mappart) {
		switch (type) {
			//case 6: // see
			//case 2: // fluss
			//case 3: // berg
			case 4: { // Wald				
				int dx = x-mappart->x*kPartSize;
				int dy = y-mappart->y*kPartSize;
				if (dx < 0 || dx >= 2 || dy < 0 || dy >= 2) break;
				float h = mappart->GetH(0.5*kPartUnit+kPartUnit*dx,0.5*kPartUnit+kPartUnit*dy);
				vector3d vpos = mappart->vPos + vector3d(kPartUnit*(dx+0.5),kPartUnit*(dy+0.5),0.1 + h);
				switch (type) {
					case 4:pWaldTex->Bind();break;
					case 3:pBergTex->Bind();break;
					case 2:pSeeTex->Bind();break; 
					case 6:pSeeTex->Bind();break;
				}
				DrawBillBoard(vpos,kPartUnit*0.7,kPartUnit*0.7);
			} break;
		}
	}
			
			
	/*
	
	(1, 'Gras', 'eine gr&uuml;ne Wiese', 120, 1, '#66AA55', 'landschaft/grass.png', 'gr');
	(2, 'Fluss', 'ein pl&auml;tschender Fluss', 0, 0, 'blue', 'river/river-%NWSE%.png', 'fluss_%NWSE%');
	(3, 'Berg', 'riesige un&uuml;berwindbare Berge', 0, 0, '#484848', 'mountain/berg-%NWSE%.png', 'hill_%NWSE%');
	(4, 'Wald', 'dichter dunkler Wald', 180, 0, '#0D7F24', 'wald/wald-%NWSE%.png', 'forest_%NWSE%');
	(5, 'Loch', 'da klafft ein gro&szlig;es Loch', 180, 0, '#666666', 'landschaft/loch.png', 'loch');
	(6, 'See', 'ganz viel Wasser...', 0, 0, 'blue', 'see/see-%NWSE%.png', 'see_%NWSE%');
	(7, 'Wueste', 'trockene, leblose Wueste', 300, 0, '#F9BC06', 'wueste/wueste-%NWSE%.png', 'wueste-%NWSE%');
	(8, 'Kornfeld', 'bringt der angrenzenden Farm einen Bonus', 120, 1, 'yellow', 'landschaft/cornfield.png', 'cfield');
	(9, 'Oase', 'Teich mit Palmen in der Wüste :-)', 120, 0, '#6666aa', 'wueste/oase.png', 'oase');
	(10, 'Blumen', 'Wiese mit Blümchen', 120, 1, '#CC3366', 'landschaft/blumen.png', 'blumen');
	(11, 'Geröll', 'zerbrochenes Gestein', 180, 1, '#686868', 'landschaft/geroell.png', 'sc');
	(12, 'Baumstumpf', 'die Reste eines abgeholzten Waldes', 120, 1, '#66AA55', 'landschaft/baumstumpf.png', 'bs');
	(13, 'junger Wald', 'kleine Bäumchen fangen hier an zu wachsen', 120, 1, '#66AA55', 'landschaft/jungwald.png', 'yw');
	(14, 'Schnee', 'eine zugeschneite Wiese', 120, 1, '#88CC77', 'winter/landschaft/grass.png', 'wgr');
	   
	*/

// cBuilding

	cMap::cBuilding::cBuilding	(const unsigned char*& reader) {
		type = cMap::mapread1(reader);
		nwse = cMap::mapread1(reader);
		level = cMap::mapread1(reader);
		user = cMap::mapread2(reader);
		x = -cMap::mapread2(reader);
		y = cMap::mapread2(reader);
	}
	void	cMap::cBuilding::Draw	(cMapPart* mappart) {
		/*
		case 'n' : $sum += 1;
		case 'w' : $sum += 2;
		case 's' : $sum += 4;
		case 'e' : $sum += 8;
		*/
		switch (type) {
			case 5: // wall
				mappart->DrawNWSEWall(x,y,nwse,2,5,cMap::pWallTex);
				return;
			break;
			case 3: // weg
				mappart->DrawNWSEWall(x,y,nwse,3,0.5,cMap::pWegTex);
				return;
			break; 
			case 17: // tor
				mappart->DrawNWSEWall(x,y,nwse,2,5,cMap::pWallTex,true);
				mappart->DrawNWSEWall(x,y,~nwse,3,0.5,cMap::pWegTex,true);
				return;
			break;
		}
		
		int dx = x-mappart->x*kPartSize;
		int dy = y-mappart->y*kPartSize;
		float h = mappart->GetH(0.5*kPartUnit+kPartUnit*dx,0.5*kPartUnit+kPartUnit*dy);
		vector3d vpos = mappart->vPos + vector3d(kPartUnit*(dx+0.5),kPartUnit*(dy+0.5),0.1 + h);
		cBuildingType* bt = cMap::instance()->buildingtype[type];
		if (bt) {
			bt->tex[0]->Bind();
			DrawBillBoard(vpos,kPartUnit*0.7,kPartUnit*0.7);
		}
	}
	
// cBuildingType
	

	cMap::cBuildingType::cBuildingType (const char* name,const char* gfx,int speed,int maxhp) : 
		name(name),gfx(gfx),speed(speed),maxhp(maxhp) {	
		std::string path = std::string("../gfx/")+gfx;
		replace("%NWSE%","we",path);
		replace("%R%","1",path);
		replace("%L%","1",path);
		printf("loading %s :\n",path.c_str());
		tex.push_back( cGraficManager::getinstance()->get( path.c_str() ) );
	}
	
	
// reader
	
	int		cMap::mapread1 (const unsigned char*& reader) { 
		return (int)*(reader++) - 128; 
	}
	int		cMap::mapread2 (const unsigned char*& reader) { 
		int res = ((int)reader[0]) + ((int)reader[1] << 8) - 256*128;
		reader += 2;
		return res;
	}
	int		cMap::mapread4 (const unsigned char*& reader) { 
		int res = ((int)reader[0]) + ((int)reader[1] << 8) + ((int)reader[2] << 16) + ((int)reader[3] << 24) - 256*256*256*128;
		reader += 4;
		return res;
	}


	
	
void cMap::Init (const char* mapfile,const char* gfxdir,const char* heightfield,int startx,int starty) {
	delete_clear(terrain);
	delete_clear(building);
	
	// read map.dat
	if (mapfile) {
		int mapdatlen = 0;
		unsigned char* mapdat = (unsigned char*)gShell.Read(mapfile,mapdatlen);
		const unsigned char* reader = mapdat;
		const unsigned char* subreader;
		const unsigned char* end = mapdat + mapdatlen;
		int i,type,len;
		if (mapdat) {
			while (reader < end) {
				type = mapread2(reader);
				len = mapread4(reader);
				subreader = reader;
				reader += len;
				cTerrain* pterrain;
				cBuilding* pbuilding;
				switch (type) {
					case 0: printf("map data version %d\n",mapread2(subreader)); break;
					case 1: 
						printf("terrain chunks : %d\n",len/6); 
						len /= 6;
						for (i=0;i<len;++i) {
							pterrain = new cTerrain(subreader);
							if (i<0) printf("t type=%d,nwse=%d,x=%d,y=%d\n",pterrain->type,pterrain->nwse,pterrain->x,pterrain->y);
							terrain[((pterrain->x<0)?(pterrain->x +1-kPartSize):pterrain->x) / kPartSize].push_back(pterrain);
						}
					break;
					case 2: 
						printf("building chunks : %d\n",len/8); 
						len /= 9;
						for (i=0;i<len;++i) {
							pbuilding = new cBuilding(subreader);
							if (i<0) printf("b type=%d,nwse=%d,level=%d,user=%d,x=%d,y=%d\n",pbuilding->type,pbuilding->nwse,pbuilding->level,pbuilding->user,pbuilding->x,pbuilding->y);
							building[((pbuilding->x<0)?(pbuilding->x +1-kPartSize):pbuilding->x) / kPartSize].push_back(pbuilding);
						}
					break;
					default: printf("unknown data %d %d\n",type,len); break;
				}
			}
			free(mapdat);
		} else {
			printf("map.dat not found, exiting");
		}
	}
	
	printf("terrain %d keys\n",terrain.size());
	printf("building %d keys\n",building.size());
	
	cM_Image* myhf = cImageManager::getinstance()->get(heightfield);
	pHeightFieldBase = (SDL_Surface*)myhf->pData;
	
	if (!pHeightFieldBase) return;
	SDL_LockSurface(pHeightFieldBase);

	startx *= -1;
	gvCampos.x = 8.0 + 16.0 * (startx/kPartSize);
	gvCampos.y = 8.0 + 16.0 * (starty/kPartSize);
	part_ltx = startx/kPartSize-kParts_Per_Row/2;
	part_lty = starty/kPartSize-kParts_Per_Row/2;
	for (int x=0;x<kParts_Per_Row;++x)
	for (int y=0;y<kParts_Per_Row;++y) 
		parts[x+y*kParts_Per_Row] = new cMapPart(x+part_ltx,y+part_lty);
	NeighborParts();
	
	SDL_UnlockSurface(pHeightFieldBase);
}


void	cMap::Draw (vector3d o,vector3d dir,vector3d& cam) {
	// z : draw bounding spheres
	
	int nullcount = 0;
	vector<cMapPart*>::iterator itor;
	for (itor=parts.begin();itor != parts.end();++itor) if (*itor) {
		if (gInput.bKeys[cInput::kkey_z]) {
			DrawSphere((*itor)->vPos + vector3d(8,8,(*itor)->fMidH),vector3d(1,1,0),(*itor)->fRad);
		}
		
		// draw 3d mouse hit
		int ix,iy;
		float dist;
		if ((*itor)->RayIntersect(o,dir,dist,ix,iy)) {
			vector3d hit = o+dist*dir;
			DrawSphere(hit,vector3d(1,1,0),0.2);
		}
	} else ++nullcount;
	if (nullcount > 0) printf("nullitors %d of %d\n",nullcount,parts.size());
	
	//if (gvCampos.z > 50.0)
	//	gvCampos.z = 50.0;
	RecenterParts(gvCampos);
	
	for_each_call(parts,&cTerrainPatch::HClip,gvCampos,1.0);
	for_each_call(parts,&cTerrainPatch::CalcLOD,gvCampos);
	cTerrainPatch::PrepareDraw(); 
	for_each_call(parts,&cTerrainPatch::Draw); 
	cTerrainPatch::CleanupDraw();
	
	glDisable(GL_LIGHTING);
	for_each_call(parts,&cMapPart::DrawObjects);
}

// ****** ****** ****** END