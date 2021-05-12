class scanline {

public:

void scan();

#ifdef COUNT_ITER
virtual int handlePoint(int, int) = 0;
long int iterCount;
scanline() {
  iterCount=0;
}
#else
virtual void handlePoint(int, int) = 0;
#endif

};

void scanline::scan()
{
#ifdef COUNT_ITER
  iterCount=0;
#endif
  int step=0;
  printf("0%%"); fflush(stdout);
  for(int j=0; j<height; j++) {    
    while(j*100>height*(step+10)) { // Shows the progress
      step+=10; std::cout << "-" << step << "%"; fflush(stdout);
    }
    for(int i=0; i<width; i++) {
      #ifdef COUNT_ITER
      iterCount += handlePoint(i,j);
      #else
      handlePoint(i,j);
      #endif
    }
  }
  
  printf("-100%%\n");

  #ifdef COUNT_ITER
  printf("Average number of iterations %f (total: %li)\n",((double) iterCount)/((double) (width*height)),iterCount);
  #endif
}
