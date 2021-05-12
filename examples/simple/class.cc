#define COUNT_ITER
      // used in scanline.cc, means we will count iterations:
      // the virtual class function handlePoint() shall return this number.
#include "scanline.cc"
      // custom C++ class scanline

#include "fix-complex.cc"
#include "tc.cc"

// good old friend
const cmplex I(0,1);

class MainClass : public scanline {
public:
  int handlePoint(int i, int j);

  double scale;
  cmplex cscale;
  cmplex offset_adj;
  
  // Constructor
  
  MainClass() {
    scale=xw/width;
    cscale=scale*exp(2*I*M_PI*(-alpha));
    offset_adj=offset-0.5*cscale*(width-1+I*(height-1));
  }
};

int MainClass::handlePoint(int i, int j) {
  tc c(offset_adj+cscale*(i+I*(height-1-j)),cscale*thickness);
  int col; // color index in palette 
  int its=0; // iterations counter

  cmplex c_dyn=c.val;
  tc z_dyn(c_dyn,1.0);

  tc z=c;
  tc beta=(1+sqrt(1-4*c))/2.0;

  for(int k=0; k<iter; k++) {
    if(norm(z.val-beta.val)<norm(z.der-beta.der)) goto boundary;
    if(norm(z.val)>100.0) goto outside;
    if(norm(z_dyn.der)<1E-12) goto inside;
    its++;
    z = z*z+c;
    z_dyn = z_dyn*z_dyn+c_dyn;
  }
  goto exhaust;

 outside:
  col=WHITE;
  goto fin;

 inside:
  col=GRAY;
  goto fin;
 
 boundary:
  col=BLACK;
  goto fin;
    
 exhaust:
  col=RED;
  goto fin;

 fin:  
  setPixel(i,j,col);
  return its;
}
