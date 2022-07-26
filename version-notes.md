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

- Modernising C++ code
  - Using std::complex instead of old personal implementation of complex numbers
  - RAII: avoiding pointers, using std::vectors, etc.
  - etc.
- Using recent version of PNGImg
- Mark saved image in a linear color space (gamma=1.0)
- In the example suite:
  - modified the simple EPS example to a simpler one
  - modified the sequence example to a simpler one
  - enhanced the code and tweaked a few algorithms

# v9.4
- Changed the palette handling

# v9.5
- Put the PNGImg in global scope and, for the batch mode, instead of re-creating the PNGImg object for each save, we use the same. The global scope allows for the user of the library to set the colorspace, for instance. The code is also now simpler, and we copy less things around. A bit more risky too: the user shall not interfere with the PNGImg object in a way that could break things, (like changing the size of its data vector). 
- Corrections and tweaks on the examples

# v9.6

- Added details of the other php fields in the manual
- Modified the help text of the generated program
- Added php field `$author`
- Added escaping of characters for inclusion of $description in C++ and EPS
- Added comments in the os-specific-*.php files, changed some var names and their behaviour to include the possibility of a local use (without copying files to a system folder)
- Updated the manual
- Adapted the example and made small modification of the comments
- Moved the `-g0 -O3` compiler options out of the default to put them in the examples in `$compileDirectives`
- phpgcc.php was encoded in latin-1, now it is in utf-8 (this had minor impact)

# v9.6.1

- The computation time was not properly saved in the metadata, this is now corrected. Cleaned up the corresponding code a little bit.

# v9.6.2

- #inlcuded <vector>

# v9.6.3

- corrected an omission: for movies, clear the text fields of the PNGImg object (since we reuse it; an alternative would be to dump it and recreate it every time)
- minor modifications in php error output
- added $colorType for paletted images and true color : 'linear' or 'sRGB'
- removed copyright symbol near the year in metadata, reordered the line
- added latin-1 enforcement for $author, $year, $version, $description
- as a consequence: php must now be installed with mbstring extension

# Future
- Complete the section of the manual called "Structure of the .cc file created by the script"
- Integrate gd?
