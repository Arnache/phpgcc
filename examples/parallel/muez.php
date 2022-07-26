<?php

$author='Arnaud Chéritat';

$year='2013';

$version='1.0';

$description='Some Julia set';

$colorType='true color'; // 'true color','palette','no pic','ps'
$colorSpace='linear'; // 'linear','sRGB'

$compileDirectives='-fopenmp -g0 -O3';

$batch = false;

$parameters=array(
  array("name" => 'xw',  "type" => 'float', "default" => '15', "description" => 'mathematical witdh of the image'),
  array("name" => 'alpha', "type" => 'float', "default" => '0', "description" => 'rotation of the picture'),
  array("name" => 'offset', "type" => 'complex', "default" => '(0,3.14)', "description" => 'mathematical coordinate of the center of the image'),
  array("name" => 'thickness', "type" => 'float', "default" => '0.25', "description" => 'line thickness, in pixels'),
  array("name" => 'iter', "type" => 'int', "default" => '10000', "description" => 'maximal number of iterations'),
  array("name" => 'thre', "type" => 'float', "default" => '12.0', "description" => 'if the real part of f^n(z) gets below this threshold, then the pixel at z is painted black'),
  array("name" => 'r1', "type" => 'float', "default" => '0.1', "description" => 'radius of disks drawn around the marked points: the origin and the two asymptotic values'),
);

$globalDeclarations = '
#include "class.cc"
';

$init = '';

$precomputations = '';

/*
// if you want to avoid time the constructor() separately,
// put it here instead of putting it in phase[] 
$precomputations = '
  MainClass ppal; 
';
*/

$phase=array();

$phase[]='
  MainClass ppal;
  ppal.scan(); // putting everything in a C++ class is just a matter of 
               // programming style
';

// Create cc source and compile program

require('/usr/local/include/phpgcc/phpgcc.php');

?>
