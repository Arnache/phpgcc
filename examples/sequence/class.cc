#define COUNT_ITER
      // used in scanline.cc, means we will count iterations:
      // the virtual class function handlePoint() shall return this number.
#include "scanline.cc"
      // custom C++ class scanline

#include "tc.cc"
#include "fix-complex.cc"

// good old friend
const cmplex I(0,1);


class MainClass : public scanline {
public:

double scale;
cmplex cscale;
cmplex offset_adj;
  
cmplex c0;

MainClass() {}

template<typename T, typename U> T f(T z,U c) {
  return z*z+c;
}

void init() {
  cmplex cc=approx_c;
  // Newton's method
  tc w,z,a;
  int i;
  for(i=0; i<max_Newton_steps; i++) {
    w.val=cc;
    w.der=1.0;
    z=0.0;
    for(int j=0; j<miz_k; j++)
      z = f(z,w);
    a = z;
    for(int j=0; j<miz_n; j++)
      z = f(z,w);
    a -= z;
    if(norm(a.val)<prec*prec) break;
    cc -= a.val/a.der;
  }
  if(i >= max_Newton_steps) {
    std:: cout << "Failed to find c0." << std::endl;
    exit(1);
  }
  else {
    c0=cc;
    std::cout << "Found the following value for c0: " << c0 << std::endl;
  }
}

void precomp() {
    double t = frameNumber/(double)(frames-1); 
   
    scale = exp(log(xw_init)+t*log(xw_final/xw_init))/width;
    cscale = scale*exp(2*I*M_PI*(-alpha));
    offset_adj = c0 - 0.5*cscale*(width-1+I*(height-1));
}

int handlePoint(int i, int j) {
  tc c(offset_adj+cscale*(i+I*(height-1-j)),cscale*thickness);
  double r,g,b; // color components
  int its = 0; // iterations counter
  tc beta = (1.0+sqrt(1.0-4.0*c))*0.5;
  tc z = c;
  cmplex c_dyn = c.val;
  tc z_dyn(c.val, 1.0);

  for(int k=0; k<iter; k++) {
    if(norm(z.val-beta.val)<norm(z.der-beta.der)) goto boundary;
    if(norm(z.val)>100.0) goto outside;
    if(norm(z_dyn.der)<1E-20) goto inside;
    its++;
    z = f(z,c);
    z_dyn = f(z_dyn,c_dyn);
  }

  goto inside;

 outside:  r=255.0;  g=255.0;  b=255.0;  goto fin; // white
 inside:   r=127.0;  g=127.0;  b=127.0;  goto fin; // gray
 boundary: r=  0.0;  g=  0.0;  b=  0.0;  goto fin; // black
  
 fin:  
  setPixel(i,j,(int) r,(int) g,(int) b);
  return its;
}

};
