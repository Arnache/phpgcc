<?php

$year='2009';

$version='1.0';

$description='Line example';

$colorType='ps';

$parameters=array(
//  array("name" => '',  "type" => '', "default" => '', "description" => ''),
);

$globalDeclarations = '
#include "class.cc"
';

$init='';

$precomputations = '
  // MainClass ppal; // put it here instead of in phase if you want to avoid timing the constructor()
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
