<?php

// Note: you need to adapt the values below to your system
// and rename this file to os-specific.php

// all these variables must be defined, some can be empty

$os="linux";
$extension=".linux.exe";
$gpp="g++";
$phpgcc_installed = true;
$phpgcc_include_path="/usr/local/include";
$phpgcc_dirname = "phpgcc";
$more_paths="";

/* Description
 
Terminology: 
- calling script: the library user script calling phpgcc.php

Variables in the present script:

- $os: the script phpgcc.php relies on $os to do specific 
the only current tested value is "mac"

- $extension: the executable will be name.$extension where the call script is name.php

- $gpp: the command used to call GCC in C++ mode (several flags are automatically added)

- $phpgcc_installed: 
- true: phpgcc was installed to a system directory (#include <...>)
- false: phpgcc is in a user directory (#include "...")
See below for more explanations.

- $phpgcc_include_path: (can be "") an optional include path to you library, must exclude the dirname

- $phpgcc_dirname: (can be "") the dirname of your library in one of include paths that your system 

- $more_paths: (can be "") an optional place to add things like one or several -I/my-include-path and -L/my-lib-path

More explanations:

If $phpgcc_installed is true then phpgcc.php will look for the phpgcc .cc include files in the include folders searched by gcc using #include <$phpgcc_dirname/file.cc> or #include <file.cc> if #include <$phpgcc_dirname/file.cc> is empty
If $phpgcc_installed is false then it will use #include "..." instead of #include <...>. This is useful for instance if the php gcc files sit in the same dir as the calling script or in a subdir.

*/
?>
