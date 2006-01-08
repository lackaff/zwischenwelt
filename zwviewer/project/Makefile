CFLAGS = -L/usr/lib -g -w -I/usr/include -Iinclude 
LFLAGS = -lstdc++ -lGL -lGLU -lGLEW `sdl-config --libs` -lSDL_image
CC = gcc-3.3
RM = rm

SRCS = $(shell find . | grep -i .cpp | grep -v svn)
HEADERS = $(shell find . | grep -i .h | grep -v svn)

OBJS = $(addsuffix .o, $(basename $(SRCS)))

.cpp.o: $< $(HADERS)
	$(CC) $(CFLAGS) -c $< -o $@

zwviewer: $(OBJS) 
	$(CC) $(CFLAGS) $(LFLAGS) $(OBJS) -o ../zwviewer

clean:
	$(RM) -f ../zwviewer $(OBJS)
	
all: zwviewer


