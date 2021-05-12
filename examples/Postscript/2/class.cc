// my old friend
const cmplex I(0,1);

#include "fix-complex.cc"

class MainClass {
public:
  void run();

  // Constructor
  
  MainClass() {
    PSoffx=200;
    PSoffy=300;
    PSscale=15;

    bbLeft=0;
    bbRight=400;
    bbBottom=0;
    bbTop=400;
  }
};


void MainClass::run() {

	ps << "1 setlinejoin\n";
	ps << "1 setlinecap\n";
  ps << zToPS(0.) << " " << PSscale << " 0 360 arc\n";

	double sub=100;
	int imax=(int)(6*sub);
	double fac=5.0;
	double ini=100;

	std::vector<cmplex> curve;

	for(int i=0; i<=imax; i++) {
		curve.push_back(exp(i/sub*I*2*M_PI)*(1.0+fac*sub/(ini+i)));
	}
	for(int i=imax; i>=0; i--) {
		curve.push_back(exp(i/sub*I*2*M_PI)*(1.0+fac*sub/(ini+sub*0.5+i)));
	}

	std::vector<cmplex>::iterator it;

	ps << "0.875 setgray\n";
	ps << "newpath\n";
	moveto(curve[0]);
	for( it=curve.begin(), it++ ; it<curve.end(); it++ ) {
		lineto(*it);
	}
	ps << zToPS(4.5-3*I) << " " << zToPS(2-5*I) << " " << zToPS(-2-5*I) << " curveto\n";
	ps << zToPS(-4-5*I) << " " << zToPS(-10-6*I) << " " << zToPS(-10-10*I) << " curveto\n";
	ps << zToPS(-10-14*I) << " " << zToPS(-7-16*I) << " " << zToPS(-2-16*I) << " curveto\n";
	ps << zToPS(8-16*I) << " " << zToPS(6.55-3*I) << " " << zToPS(6-0*I) << " curveto\n";
	ps << "fill\n";

	ps << "0 setgray\n";
	ps << "newpath\n";
	moveto(curve[0]);
	for( it=curve.begin(), it++ ; it<curve.end(); it++ ) {
		lineto(*it);
	}
	ps << zToPS(4.5-3*I) << " " << zToPS(2-5*I) << " " << zToPS(-2-5*I) << " curveto\n";
	ps << zToPS(-4-5*I) << " " << zToPS(-10-6*I) << " " << zToPS(-10-10*I) << " curveto\n";
	ps << zToPS(-10-14*I) << " " << zToPS(-7-16*I) << " " << zToPS(-2-16*I) << " curveto\n";
	ps << zToPS(8-16*I) << " " << zToPS(6.55-3*I) << " " << zToPS(6-0*I) << " curveto\n";
	ps << "stroke\n";

	ps << "newpath\n";
	ps << zToPS(0) << " " << (1.0+fac*sub/(ini+sub*0.5+imax))*PSscale << " 0 360 arc\n";
	ps << "stroke\n";
}
