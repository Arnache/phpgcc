#define COUNT_ITER
      // used in scanline.cc, means we will count iterations:
      // the virtual class function handlePoint() shall return this number.
#include "scanline_parallel.cc"
      // custom C++ class scanline

//using std::exp;

#include "fix-complex.cc"
#include "tc.cc"
const cmplex I(0.0,1.0);
const double tau = 2*M_PI;

class MainClass : public scanline {
public:
  int handlePoint(int i, int j);

  double scale;
  cmplex cscale;
  cmplex offset_adj;
  
  cmplex rho,av1,av2,c,d,beta;
  double theta,r0,lr;
  
  // The map whose Julia set we want to draw
  
  template <class T> T f(T z) { T u=exp(z); return((u-1)/(c*u+d)); }
  
  // Constructor
  
  MainClass() {

    // Here we perform some recomputations

    // parameters to convert pixel coordinates to/from complex numbers

    scale=xw/width;
    cscale=scale*std::exp(2.0*I*M_PI*(-alpha));
    offset_adj=offset-0.5*cscale*(width-1+I*(height-1));

    // The golden mean minus one    
    theta=(sqrt(5.0)-1)/2;
    lr=tau*theta;
    rho=exp(I*lr);
    // parameters c and d of funciton f
    c=1.0/(I*tau);
    d=1/rho-c;
    // a point beta in the Julia set
    beta=log(-d/c);
    // the map f has two asymptotic values av1 and av2
    av1=1/c;
    av2=-1/d;
    // r0: inner radius of the Siegel disk
    double aux;
    cmplex z=av2;
    r0=norm(z);
    for(int i=0; i<iter; i++) {
      z=f(z);
      aux=norm(z);
      if(aux<r0) r0=aux;
    }
    r0=sqrt(r0);
  }
};

int MainClass::handlePoint(int i, int j) {
  tc z(offset_adj+cscale*(i+I*(height-1-j)),cscale*thickness);
  int its=0; // iterations counter
  double whiteness=1;
  double aux;
  double r,g,b;
  // mark origin and asymptotic values by dots
  if(norm(z.val-av1)<r1*r1) goto blue;
  if(norm(z.val-av2)<r1*r1) goto green;
  if(norm(z.val)<r1*r1) goto red;

  // central loop
  for(int k=0; k<iter; k++) {
    if(sqrt(norm(z.val))+2.0*sqrt(norm(z.der))<r0) goto gray;
    if(whiteness*255.0<1.0) goto black;
    aux=sqrt(norm(z.val-av2)/norm(z.der));
    if(aux<whiteness) whiteness=aux;
    aux=sqrt(norm(1/z.val)/norm((1/z).der));
    if(aux<whiteness) whiteness=aux;
    if(z.val.real()<-thre) goto black;
    its++;
    z = f(z);
  }
  goto gray;

 gray:   r=255.0*whiteness;  g=r;  b=r;  goto fin;
 
 black:  r=0;   g=0;   b=0;    goto fin;

 blue:   r=0;   g=0;   b=255;  goto fin;
 red:    r=240; g=0;   b=0;    goto fin;
 green:  r=0;   g=200; b=0;    goto fin;

 fin:  
  setPixel(i,j,(int)r,(int)g,(int)b);
  return its;
}
