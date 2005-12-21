// ****** ****** ****** list.h
#ifndef _LIST_H_
#define _LIST_H_

#include <prefix.h>

// interface

	class cIterator {
	public:
		virtual	void	release			() = 0;
		virtual	bool	hasnext			() = 0;
		virtual	void*	next			() = 0;
	};

	class cListIterator : public cIterator {
	public:
		virtual	void	release			() = 0;
		virtual	bool	hasnext			() = 0;
		virtual	void*	next			() = 0;
		virtual	void	insert			(void* pData) = 0;
		virtual	void* 	remove			() = 0;
		virtual	void	set				(void* pData) = 0;
	};

	class cList {
	public:
		virtual	void			release		() = 0;
		virtual	void			insert		(void* pData) = 0;
		virtual	bool			remove		(void* pData);
		virtual	int				seek		(void* pData);
		virtual	bool			has			(void* pData);
		virtual	cListIterator*	iterator	() = 0;
		virtual unsigned int	size		() = 0;
	};

// linked list

	class cLinkedListNode {
	public:
		void*				pData;
		cLinkedListNode*	pNext;
		cLinkedListNode		(void* pData,cLinkedListNode* pNext);
		~cLinkedListNode	();
	};

	class cLinkedList : public cList {
	friend class cLinkedListIterator;
	public:
		cLinkedList		();
		~cLinkedList	();

		virtual	void			release		();
		virtual	void			insert		(void* pData);
		virtual	void			insertfirst	(void* pData);
		virtual	void*			removefirst	();
		virtual	cListIterator*	iterator	();
		virtual unsigned int	size		();

	private:
		cLinkedListNode*	pFirst;
		unsigned int		iSize;
	};

	class cLinkedListIterator : public cListIterator {
	public:
		cLinkedListIterator(cLinkedList* pList);
		virtual	void	release			();
		virtual	bool	hasnext			();
		virtual	void*	next			();
		virtual	void	insert			(void* pData);
		virtual	void* 	remove			();
		virtual	void	set				(void* pData);
	private:
		cLinkedList*		pList;
		cLinkedListNode*	pCur;
		cLinkedListNode*	pPrev;
	};

// double linked list

	class cDoubleLinkedListNode {
	public:
		void*					pData;
		cDoubleLinkedListNode*	pNext;
		cDoubleLinkedListNode*	pPrev;

		cDoubleLinkedListNode	(void* pData,cDoubleLinkedListNode* pPrev,cDoubleLinkedListNode* pNext);
		~cDoubleLinkedListNode	();
	};

	class cDoubleLinkedList : cLinkedList {
	friend class cDoubleLinkedListIterator;
	public:
		cDoubleLinkedList	();
		~cDoubleLinkedList	();

		virtual	void			release		();
		virtual	void			insert		(void* pData);
		virtual	void			insertfirst	(void* pData);
		virtual	void			insertlast	(void* pData);
		virtual	void*			removefirst	();
		virtual	void*			removelast	();
		virtual	cListIterator*	iterator	();
		virtual unsigned int	size		();

	private:
		cDoubleLinkedListNode*	pFirst;
		cDoubleLinkedListNode*	pLast;
		unsigned int			iSize;
	};

	class cDoubleLinkedListIterator : public cListIterator {
	public:
		cDoubleLinkedListIterator	(cDoubleLinkedList* pList);
		~cDoubleLinkedListIterator	();

		virtual	void	release		();
		virtual	bool	hasnext		();
		virtual	void*	next		();
		virtual	bool	hasprev		();
		virtual	void*	prev		();
		virtual	void	insert		(void* pData);
		virtual	void* 	remove		();
		virtual	void	set			(void* pData);
	private:
		cDoubleLinkedList*		pList;
		cDoubleLinkedListNode*	pNext;
		cDoubleLinkedListNode*	pPrev;
		cDoubleLinkedListNode*	pLast;
	};

// hashmap
	class cHashMapNode {
	public:
		void*	pData;
	};

	class cStringHashMapNode : public cHashMapNode {
	public:
		char*	pKey;

		cStringHashMapNode	(void* pData,const char* pKey); // copies string
		~cStringHashMapNode	(); // releases string
	};

	class cIntegerHashMapNode : public cHashMapNode {
	public:
		unsigned int	iKey;

		cIntegerHashMapNode		(void* pData,const unsigned int iKey);
	};

	// uses stringhash from robstring
	class cHashMap : public cList {
	friend class cHashMapIterator;
	public:
		cHashMap			(int iFields); // fields are the resolution of the map
		~cHashMap			();
		virtual	void			release		();
		virtual	cListIterator*	iterator	();
		virtual unsigned int	size		();
	protected:
		cLinkedList**			pArr;
		unsigned int			iFields;
		unsigned int			iSize;
	};

	
	class cStringHashMap : public cHashMap {
	public :
		cStringHashMap		(int iFields); // fields are the resolution of the map
		virtual	void	insert		(void* pData);
		void			insert		(void* pData,const char* pKey); // copies string
		void*			get			(const char* pKey);
		void*			remove		(const char* pKey);
	};
	class cIntegerHashMap : public cHashMap {
	public :
		cIntegerHashMap		(int iFields); // fields are the resolution of the map
		virtual	void	insert		(void* pData);
		void			insert		(void* pData,const unsigned int iKey);
		void*			get			(const unsigned int iKey);
		void*			remove		(const unsigned int iKey);
	};

	
	class cHashMapIterator : public cListIterator {
	public:
		cHashMapIterator(cHashMap* pHashMap);
		~cHashMapIterator();
		virtual	void	release		();
		virtual	bool	hasnext		();
		virtual	void*	next		();
		virtual	void	insert		(void* pData); // calls HashMap insert !
		virtual	void* 	remove		();
		virtual	void	set			(void* pData); // useless
	private:
		cHashMap*				pHashMap;
		unsigned int			iField;
		cListIterator*			pIterator;
	};

#endif
// ****** ****** ****** end