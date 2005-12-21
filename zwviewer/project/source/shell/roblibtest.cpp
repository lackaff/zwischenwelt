// ****** ****** ****** map.cpp 
#include <stdio.h>
#include <roblib.h> 

using namespace	std;

class cRobLibTest {
public:
	class cMyMap {
		public :
			
		class cMyTerrain {
			public :
			int x,y,type;
			cMyTerrain(int x,int y,int type) : x(x),y(y),type(type) {}
			void print () { printf("(%d,%d):%d ",x,y,type); };
			void print2 (int param) { printf("(%d,%d):%d ",x,y,type+param); };
		};

		std::vector<cMyTerrain*>	terrain;
		int somedata;
		
		cMyMap() : somedata(2) { somedata = 4; printf("map constructed\n"); }
		~cMyMap() { delete_clear(terrain); } // calls delete on every element in vector
			
		void drawterrain (cMyTerrain* ter) { printf("%d|",ter->type); }
		void drawterrain2 (cMyTerrain* ter,int param) { printf("%d|",ter->type+param); }
		void Load () { 
			for (int i=0;i<9;++i) terrain.push_back(new cMyTerrain(i/3-1,i%3-1,i));
		}
	};
	
	static void test () {
		printf("class1 construction:\n");
		cMyMap mymap1; // constructor IS called !!!
		printf("class2 construction:\n");
		cMyMap mymap2(); // no constructor called !!!  what kind of type is this ?!?
		printf("class3 construction:\n");
		cMyMap mymap3 = cMyMap();
		printf("class pointer 1 construction:\n");
		cMyMap* mymap4 = new cMyMap; // constructor IS called !!!
		printf("class pointer 2 construction:\n");
		cMyMap* mymap5 = new cMyMap();
		
		printf("mymap1 somedata %d\n",mymap1.somedata);
		//printf("mymap2 somedata %d\n",mymap2.somedata); 
			// error: request for member `somedata' in `mymap2', which is of non-class type `cRobLibTest::cMyMap ()()'
			//   what kind of type is this ?!?
		printf("mymap3 somedata %d\n",mymap3.somedata);
		printf("mymap4 somedata %d\n",mymap4->somedata);
		printf("mymap5 somedata %d\n",mymap5->somedata);
		
		mymap1.Load();
		mymap5->Load(); 
		
		for_each_call(mymap1.terrain,&cMyMap::cMyTerrain::print);
		printf("\n");
		for_each_call(mymap1.terrain,&cMyMap::cMyTerrain::print2,100);
		printf("\n");
		
		for_each_call(mymap1.terrain,mymap1,&cMyMap::drawterrain);
		printf("\n");
		for_each_call(mymap1.terrain,mymap1,&cMyMap::drawterrain2,100);
		printf("\n");
		
		delete mymap4;
		delete mymap5;
	}
	/* OUTPUT : 
	class1 construction:
	map constructed
	class2 construction:
	class3 construction:
	map constructed
	class pointer 1 construction:
	map constructed
	class pointer 2 construction:
	map constructed
	mymap1 somedata 4
	mymap3 somedata 4
	mymap4 somedata 4
	mymap5 somedata 4
	(-1,-1):0 (-1,0):1 (-1,1):2 (0,-1):3 (0,0):4 (0,1):5 (1,-1):6 (1,0):7 (1,1):8
	(-1,-1):100 (-1,0):101 (-1,1):102 (0,-1):103 (0,0):104 (0,1):105 (1,-1):106 (1,0):107 (1,1):108
	0|1|2|3|4|5|6|7|8|
	100|101|102|103|104|105|106|107|108|
	*/
};


void test_roblib() {
	cRobLibTest::test();
}