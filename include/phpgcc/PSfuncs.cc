#include <vector>
#include <complex>
typedef std::complex<double> cmplex;
typedef std::vector<cmplex> vec_cmplex;

// MUST be set by the library user after inclusion of the present file
double PSoffx;
double PSoffy;
double PSscale;
// can be modified by the library user after inclusion of the present file
double min_step=0.01;
double max_step=1.0;
double angle=0.01;

bool inside; // CAUTION: not multi-thread safe
cmplex oldz; // CAUTION: not multi-thread safe

/*void zToPS(cmplex z) {
  ps << PSoffx+PSscale*z.real() << " " << PSoffy+PSscale*z.imag();
}*/

std::string zToPS(cmplex z) {
  std::stringstream s;
  s << PSoffx+PSscale*z.real() << " " << PSoffy+PSscale*z.imag();
  return s.str();
}

void sizeToPS(double x) {
  ps << PSscale*x;
}

void nextto(cmplex z) {
  double x,y;
  bool ninside;
  x=PSoffx+PSscale*z.real();
  y=PSoffy+PSscale*z.imag();
  ninside = !(x<bbLeft || x>bbRight || y<bbBottom || y> bbTop);
  if(inside || ninside) {
    if(!inside) {
      ps << zToPS(oldz) << " moveto\n";
    }
    ps << zToPS(z) << " lineto\n";
  }
  inside=ninside;
  oldz=z;
}

void moveto(cmplex z) {
  ps << zToPS(z) << " moveto\n";
}

void lineto(cmplex z) {
  ps << zToPS(z) << " lineto\n";
}

void doCurve(const vec_cmplex &v, const char *s) {
  vec_cmplex::const_iterator it;
    if(v.size()>0) {
    ps << "newpath\n";
    inside = false;
    oldz=v[0];
    nextto(v[0]);
    for( it=v.begin(), it++ ; it<v.end(); it++ ) {
      nextto(*it);
    }
    ps << s;
    ps << "\n";
  }
}

void drawCurveAll(const vec_cmplex &v) {
  vec_cmplex::const_iterator it;
    if(v.size()>0) {
    ps << "newpath\n";
    inside = false;
    oldz=v[0];
    moveto(v[0]);
    for( it=v.begin(), it++ ; it<v.end(); it++ ) {
      lineto(*it);
    }
    ps << "stroke\n";
  }
}

void drawCurve(const vec_cmplex &v) {
    doCurve(v, "stroke");
}

void fillCurve(const vec_cmplex &v) {
    doCurve(v, "fill");
}

vec_cmplex map(cmplex (*f)(const cmplex&), const vec_cmplex &src) {
	vec_cmplex dest;
	vec_cmplex::const_iterator it;
	for( it=src.begin() ; it<src.end(); it++ ) {
		dest.push_back(f(*it));
	}
	return(dest);
}

vec_cmplex reverse(const vec_cmplex &src) {
	vec_cmplex dest;
	vec_cmplex::const_reverse_iterator rit;
	for( rit=src.rbegin() ; rit<src.rend(); ++rit ) {
		dest.push_back(*rit);
	}
	return(dest);
}

vec_cmplex operator+(const vec_cmplex &a,const vec_cmplex &b) {
	vec_cmplex dest;
	vec_cmplex::const_iterator it;
	for( it=a.begin() ; it<a.end(); it++ )
		dest.push_back(*it);
	for( it=b.begin() ; it<b.end(); it++ )
		dest.push_back(*it);
	return(dest);
}

void expurgeCurve(const vec_cmplex &src, vec_cmplex &dest) {

  if(src.size()<3) {
	  dest=src; // for stl vectors, '=' is a copy operator
	  return;
  }

  cmplex z,nv,ov,nnv;
  dest.clear();
  vec_cmplex::const_iterator it;
  it=src.begin();
  dest.push_back(*it);
  z=*it;
  dest.push_back(*it++);
  ov=*it-z;
  bool test;
  for(  ; it<src.end(); it++ ) {
    nv=*it-z;
    if(it<=src.end()-2) { 
        nnv=*(it+1)-*it;
        test=fabs(log(nnv/nv).imag())>angle;
    }
    else test=false;
    if(norm(nv)>min_step*min_step || it==src.end()-1) {
      if(test || fabs(log(nv/ov).imag())>angle || norm(nv)>max_step*max_step || it==src.end()-1) {
        dest.push_back(*it);
        ov=nv;
        z=*it;
      }
    }
  }
}

void drawCurveEconomic(const vec_cmplex &list) {

  vec_cmplex zlist;
  expurgeCurve(list,zlist);

  drawCurve(zlist);
}
