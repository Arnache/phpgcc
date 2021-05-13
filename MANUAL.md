# phpgcc
phpgcc is a library that I designed to decrease boilerplate coding in my command-line programs that create PNG or EPS images, or sequences of PNG images.

# Installation
The good new is that there is no compilation to perform to install. On the other side you will have to copy files to appropriate places (see below)

## Pre-requisites
Should work on Linux, on Windows with Cygwin, and on Mac with clang.

Have `gcc` installed. It has to support C++11 (this should be the case by default on most machines).

Have `libpng-dev` installed.

Optionally install `boost-regex`.

Have `php-cli` installed.

## Procedure
- Copy the folder phpgcc/ contained in include/ in one of the include folders searched by the gcc compiler on your system (may be /usr/local/include). 
- Copy one of the os-specific-\*.php files, rename the copy to os-specific.php, then edit it according to your system

*THERE ARE OTHER POSSIBILITIES*

# Usage
After you have installed, copy one of the examples and modify. Note: by default, the PNG images are saved in a linear color space (gamma=1.0), *not* sRGB.

# Author
Arnaud Chéritat

# Licence
CC BY-SA 4.0 Arnaud Chéritat

# Timeline
2005-2021

# Disclaimer
This code is provided as-is without any guarantee of any sort. In now way the author can be liable for any damage it may induce.
