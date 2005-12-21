// ****** ****** ****** roblib.h
#ifndef _ROBLIB_H_
#define _ROBLIB_H_
#include <vector> 
#include <map> 
#include <string> 

// todo : parameterize std::vector 

inline int ifloor	(const int a,const int b) { return (a>=0)?(a/b):((a+1-b)/b); }
inline int iclip 	(const int a,const int b) { return (a>=0)?(a%b):((b-1)+(1+a)%b); } 
// iclip(a,b) = (a+c*b) % b   with c so that a+c*b is positive
// for (a=-8;a<8;++a) iclip(a,4); -> 0,1,2,3,0,1,2,3,0,1,2,3,0,1,2,3

void replace (const char* search,const char* replace,std::string& subject);
	
// calls delete on every element in vector, and then calls clear on the vector,
// useful for vectors full of function pointers
template<typename _data> 
void delete_clear(std::vector<_data>& mycon) {
	for (std::vector<_data>::iterator itor = mycon.begin();itor != mycon.end();++itor) if (*itor) delete *itor;
	mycon.clear();
}
template<typename _key,typename _data> 
void delete_clear(std::map< _key , std::vector<_data> >& mymap) {
	for (std::map< _key , std::vector<_data> >::iterator itor = mymap.begin();itor != mymap.end();++itor) 
		delete_clear((*itor).second);
	mymap.clear();
}

// note : _x is type , x is instance of _x
// note : all reference combos must be declared seperately, the template-chooser only sees the function signature (name+params)
// note : only finds exact matches, even float and double are treated differently, therefore using _p1 AND _p1a
// note : _someclass is _callee itself or some baseclass/ancestor

// for_each_call(vector<_callee*>&,callee_method*); 		callee->callee_method();
// for_each_call(vector<_callee*>&,callee_method*,p1);		callee->callee_method(p1);
// for_each_call(vector<_callee*>&,callee_method*,p1,p2);   callee->callee_method(p1,p2);
	
	// 0 params
	template<typename _ret,typename _callee,typename _someclass> 
	void for_each_call (std::vector<_callee*>& callees, _ret (_someclass::*__f)()) {
		for (std::vector<_callee*>::iterator itor = callees.begin();itor != callees.end();++itor) 
			if (*itor) ((*itor)->*__f)();
	}
	
	// 1 params (all reference combos)
	template<typename _ret,typename _callee,typename _someclass,typename _p1,typename _p1a> 
	void for_each_call (std::vector<_callee*>& callees, _ret (_someclass::*__f)(_p1a),_p1 p1) {
		for (std::vector<_callee*>::iterator itor = callees.begin();itor != callees.end();++itor) 
			if (*itor) ((*itor)->*__f)(p1);
	}
	template<typename _ret,typename _callee,typename _someclass,typename _p1> 
	void for_each_call (std::vector<_callee*>& callees, _ret (_someclass::*__f)(_p1&),_p1& p1) {
		for (std::vector<_callee*>::iterator itor = callees.begin();itor != callees.end();++itor) 
			if (*itor) ((*itor)->*__f)(p1);
	}
	
	// 2 params (all reference combos)
	template<typename _ret,typename _callee,typename _someclass,typename _p1,typename _p2,typename _p1a,typename _p2a> 
	void for_each_call (std::vector<_callee*>& callees, _ret (_someclass::*__f)(_p1a,_p2a),_p1 p1,_p2 p2) {
		for (std::vector<_callee*>::iterator itor = callees.begin();itor != callees.end();++itor) 
			if (*itor) ((*itor)->*__f)(p1,p2);
	}
	template<typename _ret,typename _callee,typename _someclass,typename _p1,typename _p2,typename _p2a> 
	void for_each_call (std::vector<_callee*>& callees, _ret (_someclass::*__f)(_p1&,_p2a),_p1& p1,_p2 p2) {
		for (std::vector<_callee*>::iterator itor = callees.begin();itor != callees.end();++itor) 
			if (*itor) ((*itor)->*__f)(p1,p2);
	}
	template<typename _ret,typename _callee,typename _someclass,typename _p1,typename _p2,typename _p1a> 
	void for_each_call (std::vector<_callee*>& callees, _ret (_someclass::*__f)(_p1a,_p2&),_p1 p1,_p2& p2) {
		for (std::vector<_callee*>::iterator itor = callees.begin();itor != callees.end();++itor) 
			if (*itor) ((*itor)->*__f)(p1,p2);
	}
	template<typename _ret,typename _callee,typename _someclass,typename _p1,typename _p2> 
	void for_each_call (std::vector<_callee*>& callees, _ret (_someclass::*__f)(_p1&,_p2&),_p1& p1,_p2& p2) {
		for (std::vector<_callee*>::iterator itor = callees.begin();itor != callees.end();++itor) 
			if (*itor) ((*itor)->*__f)(p1,p2);
	}

// for_each_call(vector<_param*>&,_callee&,callee_method*); 		callee->callee_method(param);
// for_each_call(vector<_param*>&,_callee&,callee_method*,p1);		callee->callee_method(param,p1);
// for_each_call(vector<_param*>&,_callee&,callee_method*,p1,p2);   callee->callee_method(param,p1,p2);
	
	// 0 params
	template<typename _ret,typename _param,typename _callee,typename _someclass> 
	void for_each_call (std::vector<_param*>& params,_callee& callee,_ret (_someclass::*__f)(_param*)) {
		for (std::vector<_param*>::iterator itor = params.begin();itor != params.end();++itor)
			(callee.*__f)(*itor);
	}
	
	// 1 params (all reference combos)
	template<typename _ret,typename _param,typename _callee,typename _someclass,typename _p1,typename _p1a> 
	void for_each_call (std::vector<_param*>& params,_callee& callee,_ret (_someclass::*__f)(_param*,_p1a),_p1 p1) {
		for (std::vector<_param*>::iterator itor = params.begin();itor != params.end();++itor)
			(callee.*__f)(*itor,p1);
	}
	template<typename _ret,typename _param,typename _callee,typename _someclass,typename _p1> 
	void for_each_call (std::vector<_param*>& params,_callee& callee,_ret (_someclass::*__f)(_param*,_p1&),_p1& p1) {
		for (std::vector<_param*>::iterator itor = params.begin();itor != params.end();++itor)
			(callee.*__f)(*itor,p1);
	}
	
	// 2 params (all reference combos)
	template<typename _ret,typename _param,typename _callee,typename _someclass,typename _p1,typename _p2,typename _p1a,typename _p2a> 
	void for_each_call (std::vector<_param*>& params,_callee& callee,_ret (_someclass::*__f)(_param*,_p1a,_p2a),_p1 p1,_p2 p2) {
		for (std::vector<_param*>::iterator itor = params.begin();itor != params.end();++itor)
			(callee.*__f)(*itor,p1,p2);
	}
	template<typename _ret,typename _param,typename _callee,typename _someclass,typename _p1,typename _p2,typename _p2a> 
	void for_each_call (std::vector<_param*>& params,_callee& callee,_ret (_someclass::*__f)(_param*,_p1&,_p2a),_p1& p1,_p2 p2) {
		for (std::vector<_param*>::iterator itor = params.begin();itor != params.end();++itor)
			(callee.*__f)(*itor,p1,p2);
	}
	template<typename _ret,typename _param,typename _callee,typename _someclass,typename _p1,typename _p2,typename _p1a> 
	void for_each_call (std::vector<_param*>& params,_callee& callee,_ret (_someclass::*__f)(_param*,_p1a,_p2&),_p1 p1,_p2& p2) {
		for (std::vector<_param*>::iterator itor = params.begin();itor != params.end();++itor)
			(callee.*__f)(*itor,p1,p2);
	}
	template<typename _ret,typename _param,typename _callee,typename _someclass,typename _p1,typename _p2> 
	void for_each_call (std::vector<_param*>& params,_callee& callee,_ret (_someclass::*__f)(_param*,_p1&,_p2&),_p1& p1,_p2& p2) {
		for (std::vector<_param*>::iterator itor = params.begin();itor != params.end();++itor)
			(callee.*__f)(*itor,p1,p2);
	}


#endif
// ****** ****** ****** END
