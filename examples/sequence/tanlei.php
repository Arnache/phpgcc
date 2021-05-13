<?php

$year='2010';

$version='1.0';

$description='parabolic stuff';

$colorType='true color';

$compileDirectives='-g0 -O3';

$batch=true;

$parameters=array(
  array("name" => 'xw_init',  "type" => 'float', "default" => '3.0', "description" => 'mathematical witdh of the image (departure)'),
  array("name" => 'xw_final',  "type" => 'float', "default" => '0.05', "description" => 'mathematical witdh of the image (arrival)'),
  array("name" => 'alpha', "type" => 'float', "default" => '0.0', "description" => 'rotation of the picture'),
  array("name" => 'thickness', "type" => 'float', "default" => '0.25', "description" => 'line thickness, in pixels'),
  array("name" => 'iter', "type" => 'int', "default" => '10000', "description" => 'max number of iterations'),
  array("name" => 'approx_c', "type" => 'complex', "default" => '(0.0,1.0)', "description" => 'approximative value of the Miziurewics parameter c'),
  array("name" => 'miz_k', "type" => 'int', "default" => '4', "description" => 'k in f^n(f^k(0))=f^k(0)'),
  array("name" => 'miz_n', "type" => 'int', "default" => '1', "description" => 'n in f^n(f^k(0))=f^k(0)'),
  array("name" => 'prec', "type" => 'float', "default" => '1E-9', "description" => 'controls the targetted quality of approximation of c via the test |f^n(f^k(0))-f^k(0)|<prec'),
  array("name" => 'max_Newton_steps', "type" => 'int', "default" => '20', "description" => 'max number of steps of Newton\'s method (you should not often need a big value)'),
);

$globalDeclarations = '
#include "class.cc"
MainClass ppal;
';

// the following decls are put in main(), just after reading the settings
$init = '
  ppal.init();
';

// then the following decls are executed for each image
$precomputations = '
  ppal.precomp();
';

$phase=array();

$phase[]='
  ppal.scan(); // putting everything in a C++ class is just a matter of 
               // programming style
';

// Create cc source and compile program

require('/usr/local/include/phpgcc/phpgcc.php');

?>
