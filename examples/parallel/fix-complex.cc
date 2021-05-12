/*
// http://www.cplusplus.com/forum/beginner/80271/
// JLBorges
#include <type_traits>

#define FIX_COMPLEX_OP(OP) \
template< typename T, typename SCALAR > inline \
typename std::enable_if< !std::is_same<T,SCALAR>::value, std::complex<T> >::type \
operator OP ( const std::complex<T>& c, SCALAR n ) { return c OP T(n) ; } \
 \
template< typename T, typename SCALAR > inline \
typename std::enable_if< !std::is_same<T,SCALAR>::value, std::complex<T> >::type \
operator OP ( SCALAR n, const std::complex<T>& c ) { return T(n) OP c ; } \

FIX_COMPLEX_OP(+)
FIX_COMPLEX_OP(-)
FIX_COMPLEX_OP(*)
FIX_COMPLEX_OP(/)
#undef FIX_COMPLEX_OP
*/

// https://stackoverflow.com/questions/2647858/multiplying-complex-with-constant-in-c
// tesch1

// Trick to allow type promotion below
template <typename T>
struct identity_t { typedef T type; };

/// Make working with std::complex<> nubmers suck less... allow promotion.
#define COMPLEX_OPS(OP)                                                 \
  template <typename _Tp>                                               \
  std::complex<_Tp>                                                     \
  operator OP(std::complex<_Tp> lhs, const typename identity_t<_Tp>::type & rhs) \
  {                                                                     \
    return lhs OP rhs;                                                  \
  }                                                                     \
  template <typename _Tp>                                               \
  std::complex<_Tp>                                                     \
  operator OP(const typename identity_t<_Tp>::type & lhs, const std::complex<_Tp> & rhs) \
  {                                                                     \
    return lhs OP rhs;                                                  \
  }
COMPLEX_OPS(+)
COMPLEX_OPS(-)
COMPLEX_OPS(*)
COMPLEX_OPS(/)
#undef COMPLEX_OPS

// https://arstechnica.com/civis/viewtopic.php?f=20&t=1258753
// bames53

//namespace util {

template<typename T>
T &real(std::complex<T> &c) {
  return reinterpret_cast<T*>(&c)[0];
}

template<typename T>
T &imag(std::complex<T> &c) {
  return reinterpret_cast<T*>(&c)[1];
}

//}
