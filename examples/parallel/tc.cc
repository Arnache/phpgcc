// Personnal implementation of a C++ class called tc, which is
// a complex number z=val together with a variation dz=der
// (a.k.a. a 1-jet from R to C / or from C to C).
//
// The name tc is a reference to the tangent space TM of a manifold,
// which are pairs of points and vectors. Here M=C.
// 
// Compatibility: use at least C++11 

// v 0.32

// Author : Arnaud Chéritat
// Licence : CC BY-SA 4.0

#include <complex>

typedef std::complex<double> cmplex;

// casting

class tc {
 public :
  static constexpr cmplex I {0.0,1.0}; // C++11 requires 'constexpr'
  cmplex val;
  cmplex der;
  tc(cmplex un=cmplex(0.0, 0.0), cmplex deux=cmplex(0.0, 0.0)) : val{un}, der{deux} {};
  tc(const tc& t) { // copy constructor (do I need to define it?)
    val = t.val;
    der = t.der;
  }
  // assignment operators must belong to the class
  tc& operator=(const tc& t)  {
    val = t.val;
    der = t.der;
    return *this;
  }
  tc& operator=(const double& r)  {
    val = r;
    der = 0.0;
    return *this;
  }
  tc& operator=(const cmplex& c)  {
    val = c;
    der = 0.0;
    return *this;
  }

  tc& operator += (const tc &t) {
    val += t.val;
    der += t.der;
    return *this;
  }
  tc& operator -= (const tc &t) {
    val -= t.val;
    der -= t.der;
    return *this;
  }
  tc& operator *= (const tc &t) {
    der = val*t.der+der*t.val;
    val *= t.val; // order matters!
    return *this;
  }
  tc& operator /= (const tc &t) {
    // kind of optimized
    double nr = 1.0/std::norm(t.val);
    cmplex aux(nr*t.val.real(),(-nr)*t.val.imag()); // now aux = 1/t.val
    val *= aux; // order matters!
    der = (der-t.der*val)*aux; // Caution: here val is already multiplied by aux
    return *this;
  }
};

// non-member functions

inline tc operator*(const tc& a, const tc& b) {
  return tc(a.val*b.val, a.val*b.der+a.der*b.val);
}
inline tc operator*(const double& a, const tc& b) {
  return tc(a*b.val, a*b.der);
}
inline tc operator*(const tc& a, const double& b) {
  return tc(a.val*b, a.der*b);
}

inline tc operator/(const tc& a, const tc& b) {
  tc t =  a; // copy
  t /= b;
  return t; 
  //  return tc(a.val/b.val,a.der/b.val-a.val*b.der/(b.val*b.val));
}
inline tc operator/(const double& a, const tc& b) {
  cmplex u = a/b.val;
  return tc(u,-u*b.der/b.val);
}
inline tc operator/(const tc& a, const double& b) {
  return tc(a.val/b,a.der/b);
}

inline tc operator+(const tc& a, const tc& b) {
  return tc(a.val+b.val,a.der+b.der);
}
inline tc operator+(const double& a, const tc& b) {
  return tc(a+b.val,b.der);
}
inline tc operator+(const tc& a, const double& b) {
  return tc(a.val+b,a.der);
}

inline tc operator-(const tc& a, const tc& b) {
  return tc(a.val-b.val,a.der-b.der);
}
inline tc operator-(const double& a, const tc& b) {
  return tc(a-b.val,-b.der);
}
inline tc operator-(const tc& a, const double& b) {
  return tc(a.val-b,a.der);
}

// unary minus
inline tc operator-(const tc& a) {
  return tc(-a.val,-a.der);
}

inline tc exp(const tc& a) {
  cmplex aux=std::exp(a.val);
  return tc(aux,aux*a.der);
}

inline tc sin(const tc& a) {
  return (exp(a*tc::I)-exp(a*(-tc::I)))*cmplex(0.0,-0.5);
}

inline tc cos(const tc& a) {
  return (exp(a*tc::I)+exp(a*(-tc::I)))*0.5;
}

inline tc tan(const tc& a) {
  tc u=exp(a*tc::I);
  tc v=exp(a*(-tc::I));
  return (u-v)/(tc::I*(u+v)); 
}

inline tc log(const tc& a) { // principal branch, like in std::complex
  return tc(std::log(a.val),a.der/a.val);
}

inline tc sqrt(const tc& a) {
  return exp(0.5*log(a));
}

inline tc conj(const tc& a) { // CAUTION: LOSS OF HOLOMORPHY CONCERNING THE der PART
  return tc(std::conj(a.val), std::conj(a.der));
}

