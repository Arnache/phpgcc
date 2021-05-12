# v1 
July 2005

# v2
sept 2006

# v3
March 2008

# v4
June 2008

# v5 and v6
Feb 2009

# v7
Nov 2010

# v8
March 2015

# v8.1
Feb 2017

# v8.11
Sept 2020

# v9.0 to v9.3
May 2021

- Modernizing C++ code
  - Using std::complex instead of old personnal implementation of complex numbers
  - RAII: avoiding pointers, using std::vectors, etc.
  - etc.
- Using recent version of PNGImg
- Mark saved image in a linear color space (gamma=1.0)
- In the example suite:
  - modified the simple EPS example to a simpler one
  - modified the sequence example to a simpler one
  - enhanced the code and tweaked a few algos

# v9.4
- Changed the palette handling

# v9.5
- Put the PNGImg in global scope and, for the batch mode, instead of re-creating the PNGImg object for each save, we use the same. The global scope allows for the user of the library to set the colorspace, for instance. The code is also now simpler, and we copy less things around. A bit more risky too: the user shall not interfere with the PNGImg object in a way that could break things, (like changing the size of its data vector). 
- Corrections and tweaks on the examples

# future
- Integrate gd?
