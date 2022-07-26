<?php

$author='Arnaud Chéritat';

$year='2010';

$version='1.0';

$description='The Mandelbrot set';

$colorType='palette'; // 'true color','palette','no pic','ps'
$colorSpace='linear'; // 'linear,'sRGB'

$compileDirectives='-g0 -O3';

$batch = false;

$parameters=array(
  array("name" => 'xw',  "type" => 'float', "default" => '3.0', "description" => 'mathematical witdh of the image'),
  array("name" => 'alpha', "type" => 'float', "default" => '0', "description" => 'rotation of the picture'),
  array("name" => 'offset', "type" => 'complex', "default" => '-0.75', "description" => 'mathematical coordinate of the center of the image'),
  array("name" => 'thickness', "type" => 'float', "default" => '0.25', "description" => 'line thickness, in pixels'),
  array("name" => 'iter', "type" => 'int', "default" => '1000', "description" => 'max number of iterations'),
);

$globalDeclarations = '
const int BLACK=0;
const int RED=1;
const int BLUE=2;
const int GREEN=3;
const int YELLOW=4;
const int BROWN=5;
const int GRAY=127;
const int WHITE=255;

#include "class.cc"
';

$init = '
  setColor(1,255,0,0);
  setColor(2,0,0,255);
  setColor(3,0,191,0);
  setColor(4,200,200,0);
  setColor(5,191,127,0);
';

$precomputations = '
  // MainClass ppal; // put it here instead of in phase if you want time the constructor() separately
';

$phase=array();

$phase[]='
  MainClass ppal;
  ppal.scan(); // putting everything in a C++ class is just a matter of 
               // programming style
';

// Create cc source and compile program

require('/usr/local/include/phpgcc/phpgcc.php');

?>
