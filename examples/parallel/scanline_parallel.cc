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
  int step=0;
  printf("0%%"); fflush(stdout);
  int done=0;
  #pragma omp parallel for
  for(int j=0; j<height; j++) {
    #pragma omp critical
    while(done*100>height*(step+10)) { // Shows the progress
      step+=10; std::cout << "-" << step << "%" << std::flush;
    }
    for(int i=0; i<width; i++) {
      #ifdef COUNT_ITER
      iterCount += handlePoint(i,j);
      #else
      handlePoint(i,j);
      #endif
    }
    #pragma omp critical
    done++;
  }

  std::cout << "-100%\n";

  #ifdef COUNT_ITER
  std::cout << "Average number of iterations " << ((double) iterCount)/((double) (width*height)) << " (total: " << iterCount << ")\n";
  #endif
}
