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
- You can either install the library files in a system folder (the script will call `#include <...>` in the C++ code) or in a local or user folder (it will call `#include "..."`)
  - System install: Copy the folder `phpgcc/` contained in `include/` in one of the include folders searched by the gcc compiler on your system (for instance `/usr/local/include` on some systems).
  - Local or user install: Copy the content of `include/phpgcc/` a folder of your choice (for instance, the folder where you will work, or a subfolder thereof).
- Copy one of the `os-specific-\*.php` files, rename the copy to `os-specific.php`, then edit it according to your system. You may discard the others or store them elsewhere if you like, or keep them, they will not be accessed.
- If you did not install boost-regex, edit `phpgcc.php` and set the variable `$boost_regex` to false.

# Usage
After you have installed, copy one of the examples and modify.

Note: by default, the PNG images are saved in a linear color space (gamma=1.0), *not* sRGB.

# Detailed description

If modifying the examples does not work or if you want to explore more possibilities, here is a detailed description of what the script does and of the variables that must/can be set before calling it.

## How to use the script

Create a php file, the *calling script*, defining the mandatory variables and possibly optional ones, and ending with a call to `phpgcc.php`.

## What the script does

It creates a .cc file from the variables defined in the *calling script* and calls g++ to compile it, with appropriate options that depend also on the variables.

## Variables

### Mandatory

- `$author` either `""` or name of the author (the user of the library)
- `$year` a string containing for instance the year when the calling script was created or last modified
- `$version` a string containing the version number of the calling script

The three variable above must be php strings. They will be included in the C++ code enclosed with double quotes: " (and in EPS %% comments if `$colorType` is `'ps'`) so don't do crazy things with them. For instance, don't put a line break, a `"` nor a `\` in `$author`, `$year` or `$version`.

- `$description` a string containing a description of what the generated program will do (this variable is correctly escaped for its inclusion in C++ and EPS so you are more free than with the previous ones)
- `$colorType` one of  `"true color"`, `"palette"`, `"no pic"`, `"ps"`
- `$parameters` an array of associative arrays of the form
`array("name" => ,  "type" => , "default" => , "description" => )`
where each value is a string:
  - `name` name of the variable in the program *and* in the command line options; some names are forbidden
  - `type` the variable type: one of `int`, `float` (a `double`), `complex` (a `std::complex<double>`), `string` (a `std::string`), `bool` or `color` (a struct defined in `color.cc`)
  - `default` a string with giving the default value for the variable (must be formatted so as to work with `std::ostringstream << default` for the common types, and for `color` see below) or `""` if the variable must be a mandatory parameter on the command line
  - `description` a short description of the role of this parameter in the program, this will be copied in the generated program source and will be accessible from the command line too

  These will be the command line parameters of the generated program.

- `$globalDeclarations` piece of C++ code that is inserted at global scope
- `$init` piece of C++ code that will be inserted in `main()` just after the creation of the image object. It is called only once.
- `$precomputations` piece of C++ code that will be inserted in a function `void performComputations() {...}`. that is called just before the phases. If `$batch` is true, it is called for each sequence element in the case of a sequence.
- `$phase` an array of strings containing C++ code that are inserted inside `performCompuations()` in the array order, preceded and followed by code that measure execution time of each phase. The array `$phase` typically contains only one element (one phase). This array can also be empty. If `$batch` is true, the precomputation and the phases are called for each sequence element.

#### struct color

```c++
typedef struct {
  double red;
  double green;
  double blue;
} color;
```

The format for reading colors is `(R,G,B)` where R,G,B are unsigned integers < 256.

### Optional
- `$batch` (defaults to `false`) set to `true` if you want to create a sequence of PNG images (or EPS images, or computations)
- `$strip` (defaults to `true`) stripping of symbols of the executable after its compilation via the `strip --strip-all` command. Set to `false` to cancel. if `$os` is `"mac"` then `true` is ignored.
- `$withGD` (defaults to `false`) set to `true` to add the [gd library](https://libgd.github.io/) and its dependencies to the linking options `-l` of the gcc command that will create the program (in this case, gd and its dependencies must be installed). If you do not plan to compute your PNG image pixel by pixel (raster aka scanline) but instead drawing here and there, then the gd library is one way to go.
- `$libs` additional linker commands to add libraries. For instance `"-lgmp -lmpfr"` for multiprecision computation.
- `$override_compile_command` if defined, this will replace the complete compilation command
 
### Generated program usage

`prog_name [options]`

Options can be

- `--help`
- `--options`
- `--options --verbose`
- `--help`
- `--var_name value` where `var_name` is one of the variable names defined in `$parameters` and value is its value
- `filename` a file containing lines of the form `var_name = value`

A var_name shall not be attributed twice, with the following exception: command line options supersede text file options (even text files appearing later in the command line).

### Structure of the .cc file created by the script  

*Will be detailed here in a future version of this manual*

# Additional information

## Author
Arnaud Chéritat

## Licence
CC BY-SA 4.0 Arnaud Chéritat

## Timeline
2005-2021 see `version-notes.md`

## Disclaimer
This code is provided as-is without any guarantee of any sort. In now way the author can be liable for any damage it may induce.
