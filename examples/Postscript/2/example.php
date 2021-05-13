<?php

$author='Arnaud Chéritat';

$year='2009';

$version='1.0';

$description='swirling simply connected set';

$colorType='ps';

$compileDirectives='-g0 -O3';

$parameters=array(
//  array("name" => '',  "type" => '', "default" => '', "description" => ''),
);

$globalDeclarations = '
#include "class.cc"
';

$init='';

$precomputations = '
  // MainClass ppal; // put it here instead of in phase if you want time the constructor() separately
';

$phase=array();

$phase[]='
  MainClass ppal;
  ppal.run(); // putting everything in a C++ class is just a matter of 
               // programming style
';

// Create cc source and compile program

require('/usr/local/include/phpgcc/phpgcc.php');

?>
