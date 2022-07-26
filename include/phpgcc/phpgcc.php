<?php

// Licence : CC BY-SA
// Author: Arnaud Chéritat
// work: 2005-2021

// Version: 9.6.3

// This PHP script plays the role of a precompiler: it outputs a C++ file and calls
// gcc on it, with the correct libraries linked (see $compile)

// This code is meant to be called from another php script via inclusion
// of the following line at the end:
// require("phpgcc.php");

// see the examples provided somewhere

// **********

// std::regex is a bit slow, so we use boost::regex instead
// if you prefer std::regex, or if boost::regex is too complicated to install,
// then set the following variable to false
$boost_regex = true;

// The following script should be present in the same directory as the 
// present script phpgcc.php
// It defines system-specific variables used later in the present script
// that you may have to change according to your system and installation.

require "os-specific.php";

// Let us check that the following php variables are present in the php
// script calling the present script.
// Some of these variables are here to accelerate the programmation.
// Some of them to enforce cleaner programming (year, version, description).

// mandatory variables
$list = array(
  'author',
  'year',
  'version',
  'description',
  'parameters',
  'globalDeclarations',
  'precomputations',
  'init',
  'phase',
  'colorType',
);
$latin1_vars = array(
  'author',
  'year',
  'version',
  'description',
);
$missing = array();
function hex($i) { // $i should be int between 0 and 255 both included
  return $i < 16 ? '0'.dechex($i) : dechex($i);
}
$latin1_in_utf8 = array(); // list of 256 utf8 encoded strings containing each of the 256 latin-1 characters
for($i = 0; $i<256 ; $i++) {
  if($i<32) continue;
  if($i>=127 && $i<160) continue;
  $latin1_in_utf8[] = mb_convert_encoding(hex2bin(hex($i)),mb_internal_encoding(),"ISO-8859-1");
}
function test_latin1($s) {
  global $latin1_in_utf8;
  $a=mb_str_split($s);
  foreach ($a as $c){
    if(!in_array($c,$latin1_in_utf8)) return false;
  }
  return true;
}
foreach($list as $name) {
  if(!isset($$name)) $missing []= '$'.$name;
  else if(in_array($name,$latin1_vars)) {
    if(test_latin1($$name)) {
      ${$name.'_latin1'}=utf8_decode($$name);
    }
    else {
      echo 'Warning: converting variable $'.$name.' to latin-1: it contains non-convertible characters'."\n";
    }
  }
}
$n=count($missing);
if($n>0) { 
  $s = 'missing variable';
  if($n>1) $s .= 's';
  $s .= ':';
  foreach($missing as $v) $s .= ' $'.$v;
  $s.=" in php script\n";
  exit($s);
}

// set default values of optional variables

if(!isset($batch))        $batch = false;
if(!isset($strip))        $strip = true;
if(!isset($withGD))       $withGD = false;

// Other optional variables should have no default values:
//
// -  $libs
// -  $override_compile_command

// **********

// Check the parameters 

// **********

$colorTypes=array(
  'true color',
  'palette',
  'no pic',
  'ps',
);

if(!in_array($colorType,$colorTypes)) {
  $s='error: $colorType shall be either ';
  $done=false;
  foreach($colorTypes as $ct) {
    if($done)
      $s.=' or ';
    else
      $done=true;
    $s.=$ct;
  }
  $s.="\n".'(with this spelling and capitalization)'."\n";
  exit($s);
};

if(in_array($colorType,array('true color','palette'))) {
  if(!isset($colorSpace)) {
    exit('missing variable $colorSpace in php script');
  }
  
  $colorSpaces=array(
    'linear',
    'sRGB',
  );

  if(!in_array($colorSpace,$colorSpaces)) {
    $s='error: $colorType shall be either ';
    $done=false;
    foreach($colorTypes as $ct) {
      if($done)
        $s.=' or ';
      else
        $done=true;
      $s.=$ct;
    }
    $s.="\n".'(with this spelling and capitalization)'."\n";
    exit($s);
  };
}

$varNames=array();
foreach($parameters as $p) {
  $nm=$p['name'];
  if(in_array($nm,$varNames))
    exit("error: duplicate identifier ($nm)\n");
  else
    $varNames[]=$nm;
}
if($colorType!='no pic') { 
  if(in_array('filename',$varNames))
    exit('error: $parameters should not contain the key \'filename\''."\n");
  else
    $parameters[]= array("name" => 'filename', "type" => 'string', "description" => 'name of the output image');
  if(in_array('width',$varNames))
    exit('error: $parameters should not contain the key \'width\'.'."\n");
  else
    $parameters[]= array("name" => 'width',  "type" => 'int', "default" => '600', "description" => 'witdh of the image, in pixels');
  if(in_array('height',$varNames))
    exit('error: $parameters should not contain the key \'height\'.'."\n");
  else
    $parameters[]= array("name" => 'height', "type" => 'int', "default" => '600', "description" => 'height of the image, in pixels');
}
if($batch) {
  if(in_array('frames',$varNames))
    exit('error: $parameters should not contain the key \'frames\'.'."\n");
  else
    $parameters[]= array("name" => 'frames', "type" => 'int', "default" => '30', "description" => 'number of images in the movie');
  if(in_array('skip',$varNames))
    exit('error: $parameters should not contain the key \'frames\'.'."\n");
  else
    $parameters[]= array("name" => 'skip', "type" => 'int', "default" => '0', "description" => 'number of frames to skip');
}

$varTypes=array();
foreach($parameters as $p)
  $varTypes[]=$p['type'];
$hasComplexParams=in_array('complex',$varTypes);
$hasColorParams=in_array('color',$varTypes);

$varMandat=array(); // name of mandatory params
$hasOptArgs=false;
foreach($parameters as $p)
  if(isset($p['default'])) { $hasOptArgs=true; }
  else { $varMandat[]=$p['name']; }
$hasMandArgs=count($varMandat) > 0;

// **********

// Determine the file names and the compile command

// **********

$name=basename($_SERVER['SCRIPT_NAME'])."\n";
$name=explode(".",$name);
$name=$name[0];

$ccName=$name.'.cc';
$exeName=$name.$extension;

if(isset($override_compile_command)) {
  $compile = $override_compile_command;
}
else {
  $compile=$gpp.' -std=c++11 -Wall --pedantic-errors ';
  if($phpgcc_include_path != "")
    $compile.= '-I'.$phpgcc_include_path.' ';
  if($more_paths != "")
    $compile.=$more_paths.' ';
  if(isset($compileDirectives))
    $compile.=$compileDirectives.' ';
  $compile.="$ccName ";
  if($hasColorParams && $boost_regex)
    $compile .= '-lboost_regex ';
  if(isset($libs)) 
    $compile.=$libs.' ';
  if($withGD)
  //$compile.='-lgd -lpng -lz -ljpeg -lfreetype -lm';
    $compile.='-lgd -lpng -lz -lm ';
  else
    if(in_array($colorType,array('true color','palette')))
      $compile.='-lpng ';
  $compile.="-o $exeName";
}

echo $compile."\n";

if($strip && $os!="mac") $postprocess="strip --strip-all $exeName";

$handle = fopen($ccName, "wb");

// **********

// Utilities

// **********

function phpgcc_lib_include($name) {
  global $phpgcc_dirname, $phpgcc_installed;

  $temp = $name;
  if($phpgcc_dirname !== "")
    $temp = $phpgcc_dirname."/".$temp;

  if($phpgcc_installed)
    return "<$temp>";
  else
    return "\"$temp\"";
}

$cstr_patterns = array( // order matters!
  '\\', '"', "\r\n", "\n", "\r", 
);

$cstr_replacements = array(
  '\\\\', '\\"', '\n', '\n', '\n', 
);

function escape_string_for_cpp_string($s) {
  global $cstr_patterns, $cstr_replacements;
  return str_replace($cstr_patterns,$cstr_replacements,$s);
}

$star_patterns = array(
  '*/',
);

$star_replacements = array(
  '* /',
);


function escape_string_for_cpp_star_comments($s) {
  global $star_patterns, $star_replacements;
  return str_replace($star_patterns,$star_replacements,$s);
}

$epsc_patterns = array( // order matters!
  "\r\n", "\n", "\r", 
);

$epsc_replacements = array(
  "\n% ", "\n% ", "\n% ",
);

function escape_string_for_eps_comment($s) {
  global $epsc_patterns, $epsc_replacements;
  return str_replace($epsc_patterns,$epsc_replacements,$s);
}

// **********

// Create the source file 

// **********

$content = '';
$content .=
"/*
 * .".escape_string_for_cpp_star_comments($ccName)."
 *
 * version $version
 *
 * (this file was generated by a PHP script)
 *
 * description : ".escape_string_for_cpp_star_comments($description)."
 * 
 * compilation : ".escape_string_for_cpp_star_comments($compile)."
 *
 */";
$content .= '

#include <cmath>
#include <chrono>
#include <iostream>
#include <iomanip>
#include <string>
#include <sstream>
#include <fstream>
#include <getopt.h>
#include <vector>
';
if($hasColorParams) {
  $content.='#include '.phpgcc_lib_include('color.cc').'
';
  if($boost_regex)
    $content .= '#include <boost/regex.hpp>
';
  else 
    $content .= '#include <regex>
';
}
if($hasComplexParams) {
    $content .= '#include <complex>
';
}
if(in_array($colorType,array('true color','palette')))
  $content.='#include '.phpgcc_lib_include('PNGImg.cpp').'
';
$content .= "\n";

$content .= "int Argc; char **Argv;
";

if($batch) 
  $content.='
int frameNumber;
';

// Declaration of the variables (command line parameters of the program)
// according to the data in the php variable $parameters

foreach($parameters as $param) {
  switch($param["type"]) {
    case 'int' : 
      $content .= 'int';
      break;
    case 'float' : 
      $content .= 'double';
      break;
    case 'complex' : 
      $content .= 'std::complex<double>';
      break;
    case 'string' : 
      $content .= 'std::string';
      break;
    case 'bool' : 
      $content .= 'bool';
      break;
    case 'color' : 
      $content .= 'color';
      break;
    default :
      echo "Error in php source: parameter type ".$param["type"]." unkonwn\n";
      exit();
  }
  $content .= " ".$param["name"].";";
  if($param["description"]) {
    $content .= " // ".$param["description"];
  } 
  $content .= "\n\n";
}

if(in_array($colorType,array('true color','palette'))) {
  $content .= '
PNGImg img;
std::vector<char> image; // the raw format image will be held here

';
}

if($colorType=='ps')  
  $content.='
std::stringstream ps; // string containing the file data
int bbLeft, bbRight, bbBottom, bbTop; // bounding box
#include '.phpgcc_lib_include('PSfuncs.cc').'

';

$content.='
std::string pic_duration_string;

std::string progName="'.$name.'"; // used in clread.cc

const int paramNumber='.count($parameters).';

bool verbose;

void showVersion() {
  std::cout << "\n'.$name.', version '.$version.'\n" << std::endl;
}

';

if($colorType=='palette') 
  $content .= '
void setPixel(int i, int j, int c) {
  if(0<=i && i<width && 0<=j && j<height) {
    img.data[i+j*width]=c;
  }
}
';
if($colorType=='true color')
    $content.='
void setPixel(int i, int j, int r, int g, int b) {
  if(0<=i && i<width && 0<=j && j<height) {
    int k=(i+j*width)*3;
    img.data[k]=r;
    img.data[k+1]=g;
    img.data[k+2]=b;
  }
}
';



$content .='
/*
 * Write duration in a friendly format
 */
std::string cct(std::chrono::high_resolution_clock::time_point timer1, std::chrono::high_resolution_clock::time_point timer2) {
  // total number of seconds as a float
  double time = (std::chrono::duration_cast<std::chrono::duration<double>>(timer2-timer1)).count();
  std::ostringstream ss;
  if(time<60) {
    ss.precision(3);
    ss.setf(std::ios_base::fixed);
    ss << time << " sec";
  }
  else if(time<3600) {
    int m=(int)(time/60.0);
    ss << m << " min ";
    ss.precision(3);
    ss.setf(std::ios_base::fixed);
    ss << time-m*60.0 << " sec"; 
  } else if(time<86400) {
    int h=(int)(time/3600.0);
    int m=(int)(time/60.0-h*60.0);
    ss << h << " h " << m << " min";
  } else {
    int d=(int)(time/86400.0);
    int h=(int)(time/3600.0-d*24.0);
    if(d>1)
      ss << d << " days " << h << " h";
    else
      ss << d << " day " << h << " h";
    ss << " (you must be a patient person)";
  }
  return ss.str();
}

/*
 * Write duration in a friendly format on stdout
 */
void pct(std::chrono::high_resolution_clock::time_point timer1, std::chrono::high_resolution_clock::time_point timer2) {
  std::cout << cct(timer1,timer2);
}

';

$content .= $globalDeclarations;

if($colorType=='palette') 
  $content.='
void setColor(int i, int r, int g, int b) {
  img.palette[i].red=r;
  img.palette[i].green=g;
  img.palette[i].blue=b;
}
';

$content.='
/* rudimentary stob function (bad error handling)*/
bool stob(const std::string s) {
  if(s=="true" || s=="t" || s=="yes") return true;
  if(s=="false" || s=="f" || s=="no") return false;
  std::cerr << "Error : boolean argument " << s << " not recognized, should be lowercase and equal to true, t, yes, false, f or no." << std::endl;
  exit(1);
}
';

if($hasColorParams) {
$content .= ($boost_regex ? 'boost' : 'std').'::regex color_matcher(R"([\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)\s*])");

/* rudimentary string to color conversion funtion (bad error handling) */ 
color stocolor(const std::string s) {
  '.($boost_regex ? 'boost' : 'std').'::smatch matches;
  bool res = '.($boost_regex ? 'boost' : 'std').'::regex_match(s,matches,color_matcher);
  if(res) {
    if(matches.size() !=4) {
      std::cerr << "Bug! in function stocolor() called with argument \"" << s << "\"." << std::endl;
      exit(1);
    }
    int r=stoi(matches[1].str());
    int g=stoi(matches[1].str());
    int b=stoi(matches[1].str());
    if(!(0<= r && r<256 && 0<= g && g<256 && 0<= b && b<256)) {
      std::cerr << "Invalid values for R,G,B in color " << s << ".\nValues should be in [0,255]." << std::endl;
      exit(1);
    }
    color c;
    c.red = r;
    c.green = g;
    c.blue = b;
    return c;
  }
  else {
    std::cerr << "Error : color argument " << s << " not recognized, should be of the form (R,G,B) where R, G and B are integers in [0,255]." << std::endl;
    exit(1);
  }
}
';
}

$content .='
/* Write a "command line syntax" explanation */
void explainSyntax() {
    std::cout << "Syntax: " << "'.$name.'" <<" [textfiles] [options]\n";
    std::cout << "where the (optional) textfiles contain the" << std::endl << "settings and the (optional) options supersede the ones given in the textfiles" << std::endl;
      std::cout << "  (mandatory settings) ->";';
foreach($parameters as $param) {
  if(!isset($param["default"])) {
    $content.='
      std::cout << " '.$param["name"].'";';
  }
}
if(!$hasMandArgs) {
    $content.='
      std::cout << " no mandatory settings";';
}
$content .= '
    std::cout << std::endl;';
$content .= '  
      std::cout << "  (optional settings) ->";';
foreach($parameters as $param) {
  if(isset($param["default"])) {
    $content.='
      std::cout << " '.$param["name"].'";';
  }
}
if(!$hasOptArgs) {
    $content.='
      std::cout << " no optional settings";';
}
$content .= '
    std::cout << std::endl;
    std::cout << "\'" << "'.$name.'" << " --help\' for a more detailed description" << std::endl;
    std::cout << "\'" << "'.$name.'" << " --options\' for options description" << std::endl;
}

/* describe */
void describe() {
   std::cout << "Description: '.escape_string_for_cpp_string($description).'\n\n";
}

/* triggered by --help command line option */
void help() {
    std::cout << "'.$name.', '.$author.' '.$year.'\n\n";
    std::cout << "(program created with the help of the library phpgcc by Arnaud Chéritat)\n\n";
    describe();
    std::cout << "Syntax: " << "'.$name.'" << " [textfiles] [options]\n";
    std::cout << "  in any order, where textfiles are text files (indeed) containing the\n";
    std::cout << "  settings as a sequence of lines, one for each parameter, of the form\n";
    std::cout << "       name = value\n";
    std::cout << "  and options are command line options, setting the same values, of the form\n";
    std::cout << "       -name value\n";
    std::cout << "  A name shall not be attributed twice, with the following exception:\n";
    std::cout << "  command line options supersede text file options (even text files appearing\n";
    std::cout << "  later in the command line).\n";
    std::cout << "\n";
    std::cout << "Type \''.$name.' --options\' for a list of parameters\n";
    std::cout << "Type \''.$name.' --options --verbose\' for a detailed description\n";
    std::cout << std::endl;
}

/* triggered by --options command line option */
void listOptions() {
    describe();
    if(verbose) {
      std::cout << "Parameters description:\n\n";
      std::cout << ". Mandatory arguments:\n\n";';
foreach($parameters as $param) {
  if(!isset($param["default"])) {
    $content.='
      std::cout << "  '.$param["name"].' ('.$param["type"].'): '.$param["description"].'\n";';
  }
} 
$content .= '  
      std::cout << "\n";
      std::cout << ". Optional arguments:\n\n";';
foreach($parameters as $param) {
  if(isset($param["default"])) {
    $content.='
      std::cout << "  '.$param["name"].' ('.$param["type"].'): default value = '.$param["default"].'\n";
      std::cout << "    '.$param["description"].'\n";
';
  }
}
$content .= '
    }
    else {
      std::cout << "Parameters:\n";
';
foreach($parameters as $param) {
  $content.='
      std::cout << "  '.$param["name"].' ('.$param["type"].', '.(isset($param["default"]) ? 'optional' : 'mandatory').')\n";
';
}
$content .= '  
      std::cout << "\nType \''.$name.' --options --verbose\' for a detailed description\n";
    }
}

/* Set parameter number "i" to value "value" */
void setParam(int i, const std::string value) {
  switch(i) {';
$i=0;
foreach($parameters as $param) {
  $content.='
    case '.$i.': { ';
  switch($param["type"]) {
   case "int":
    $content.=$param["name"].'=stoi(value);
      if(verbose) std::cout << "  '.$param["name"].' = " << '.$param["name"].' << "\n";';
    break;
   case "float":
    $content.=$param["name"].'=stod(value);
      if(verbose) std::cout << "  '.$param["name"].' = " << '.$param["name"].' << "\n";';
//if(verbose) printf("  %s = %f\n","'.$param["name"].'",'.$param["name"].');';
    break;
   case "complex":
    $content.='std::istringstream is(value);
      is >> '.$param["name"].';
      if(verbose) std::cout << "  '.$param["name"].' = " << '.$param["name"].' << "\n";
';
    break;
   case "string":
    $content.=$param["name"].' = value;
      if(verbose) std::cout << "  '.$param["name"].' = " << '.$param["name"].' << "\n";';
  break;
   case "bool" : 
    $content.=$param["name"].' = stob(value);
      if(verbose) std::cout << "  '.$param["name"].' = " << ('.$param["name"].' ? "true" : "false") << "\n";';
    break;
   case "color" : 
    $content.=$param["name"].' = atocolor(value);
      if(verbose) std::cout << "  '.$param["name"].' = (" << '.$param["name"].'.red << ", " << '.$param["name"].'.green << ", " << '.$param["name"].'.blue << ", " << '.$param["name"].'.gamma << ")\n";';
    break;
   default:
    echo "ERROR in the php source: parameter type ".$param["type"]." unknown\n";
    exit();
  }
$content.='
    } break;';
  $i++;
} 
$content.='
    default: {
      std::cerr << "Internal error... setParam(i, value) called with bad i" << std::endl;
      exit(1);
    }
  }
}';

// Now we define in the C++ program a few arrays, that we fill according to the 
// php variable $parameters, and that will be used to handle the program's
// command line options processing

$content.='

/* 
   This is the list of command-lines options, in a format understood by
   the GNU getopt library
 */
const static struct option options[]=
  {';
foreach($parameters as $param) {
  $content.='
    {"'.$param["name"].'", 1, NULL, 0},';
}
$content.='
    {"help", 0, NULL, 0},
    {"verbose", 0, NULL, 0},
    {"version", 0, NULL, 0},
    {"options", 0, NULL, 0},
    {0,0,0,0}
  };
const int HELP_INDEX = paramNumber;
const int VERBOSE_INDEX = paramNumber+1;
const int VERSION_INDEX = paramNumber+2;
const int OPTIONS_INDEX = paramNumber+3;

/* Does parameter number i have a default value ? */
const bool hasDefault[]=
  {';
foreach($parameters as $param) {
  $content.='
    '.(isset($param["default"]) ? 'true' : 'false').',';
}
$content.='
  };

/* What is the default value of parameter number i ? */
const char* defaultValue[]=
  {';
foreach($parameters as $param) {
  $content.='
    "'.(isset($param["default"]) ? $param["default"] : '').'",';
} 
$content.='
  };

bool hasMandatoryArguments='.($hasMandArgs ? 'true' : 'false' ).';

void performComputations() {

  // Let\'s define a few variables

  std::chrono::high_resolution_clock::time_point
    timer1,timer2  // used to chronometerize computation phases
   ,timer3,timer4; // used to chronometerize total

  timer3 = std::chrono::high_resolution_clock::now();
  
  // Now, do the computations

';

// Now we are in the core of the program.
// We first execute a precomputations phase, written in the php
// variable $precomputations.
// Then we execute the different phases of the program, that are written
// in the php variable $phase, which shall be an array of strings
// -each phase is timed 
// -the total duration is measured too
// -the precomputation is timed and included in the total duration
// -all these durations are printed on screen.
// The special case where there is only one phase is taken care of.

$content .= '

  std::cout << "Precomputations:\n";
  timer1 = std::chrono::high_resolution_clock::now();

';

$content .= $precomputations;

$content .= '

  timer2 = std::chrono::high_resolution_clock::now();

  // Print computation time

  if(verbose) {
    std::cout << "Precomptations duration: ";
    pct(timer1,timer2);
    std::cout << "\n";
  }
';

$n=count($phase);

foreach(range(1,$n) as $i) {

if($n>1) {
  $content .= '

  std::cout << "Phase '.$i.'/'.$n.'\n";
';
}
else {
  $content .= '

  std::cout << "Computations:\n";
';
}

$content .= '
  timer1 = std::chrono::high_resolution_clock::now();

';

$content .= $phase[$i-1];

$content .= '

  timer2 = std::chrono::high_resolution_clock::now();

  // Print computation time

  if(verbose) { 
    std::cout << "Duration: ";
    pct(timer1,timer2);
    std::cout << "\n\n";
  }

';
}
$content.='
  timer4 = std::chrono::high_resolution_clock::now();
';
if($colorType=='no pic') $content.='std::cout << "Global duration: ";
';
else $content.='  std::cout << "Image computation total duration: ";
';
$content.='
  pct(timer3,timer4);
  pic_duration_string = cct(timer3,timer4);
  std::cout << "\n";
}

/*
 * A procedure to save a picture
 * and useful information in the metadata (like parameters, time, etc...)
 */
void savePic(std::string fname) {
  std::chrono::high_resolution_clock::time_point timer1,timer2; // to measure save pic duration
  timer1 = std::chrono::high_resolution_clock::now();
  
';
if(in_array($colorType,array('true color','palette'))) {
$content .= '
  // Metadata
  // We add some text to the PNG file in its metadata:
  // program name, version, description, parameters, computation time

  img.text_list.clear(); // added in v9.6.3

  PNGImg::pngText t;
  t.type = PNG_TEXT_COMPRESSION_NONE;

  t.key = "Software";
  t.text = "'.$ccName.' version '.$version_latin1.' '.$author_latin1.' '.$year_latin1.'";
  img.text_list.push_back(t);

  t.key = "Description";
  t.text = "'.escape_string_for_cpp_string($description).'";
  img.text_list.push_back(t);

  t.key = "Parameters";
  t.text.clear();
  std::stringstream s;
';
  foreach($parameters as $i) {
    $content .= '  s << "'.$i["name"].' = ";
';
    switch($i["type"]) {
      case 'int' : 
        $content .= '  s << '.$i["name"].';';
        break;
      case 'float' :
        $content .= '  s << std::setprecision(16);'."\n";
        $content .= '  s << '.$i["name"].';';
        break;
      case 'complex' : 
        $content .= '  s << std::setprecision(16);'."\n";
        $content .= '  s << '.$i["name"].';';
        break;
      case 'string' : 
        $content .= '  s << '.$i["name"].';';
        break;
      case 'bool' : 
        $content .= '  s << ('.$i["name"].' ? "true" : "false");';
        break;
      case 'color' : 
        $content .= '  s << "(" << '.$i["name"].'.red << "," << '.$i["name"].'.green << "," << '.$i["name"].'.blue << ")";';
        break;
      default :
        break;
    }
    $content .= '
  s << "\n";
';
  }
  $content .= '
  t.text = s.str() ;
  img.text_list.push_back(t);

  t.key = "Computation time";
  t.text = pic_duration_string;
  img.text_list.push_back(t);

  // Save the picture
  img.save(fname);
';
} // endif(true color or paletted)
if($colorType=='ps') {
  // We open the ps file for writing
  $content.='

  std::ofstream file;
  file.open(fname); // text mode!
  if( file.fail() ) {
    // if( (file.rdstate() & std::ifstream::failbit ) != 0 ) {
    std::cerr << "Can\'t open file" << fname << "\n";
    exit(1);
  };

  // Write file header and save some metadata in it
  file << "%!PS-Adobe EPSF-3.0\n";
  file << "%%BoundingBox: " << bbLeft << " " << bbBottom << " " << bbRight << " " << bbTop << "\n";
  file << "% Software: '.$ccName.' '.$author.' '.$year.'\n";
  file << "% Description: '.escape_string_for_cpp_string(escape_string_for_eps_comment($description)).'\n";
  file << "% Parameters:\n";
  std::stringstream s;
';
  foreach($parameters as $i) {
    $content .= '  s << "% '.$i["name"].' = ";
';
  switch($i["type"]) {
    case 'int' : 
      $content .= '  s << '.$i["name"].';';
      break;
    case 'float' :
      $content .= '  s << std::setprecision(16);'."\n";
      $content .= '  s << '.$i["name"].';';
      break;
    case 'complex' : 
      $content .= '  s << std::setprecision(16);'."\n";
      $content .= '  s << '.$i["name"].';';
      break;
    case 'string' : 
      $content .= '  s << '.$i["name"].';';
      break;
    case 'bool' : 
      $content .= '  s << ('.$i["name"].' ? "true" : "false");';
      break;
    case 'color' : 
      $content .= '  s << "(" << '.$i["name"].'.red << "," << '.$i["name"].'.green << "," << '.$i["name"].'.blue << ")";';
      break;
    default :
      break;
    }
    $content .= '
  s << "\n";
';
  }
  $content.='
  file << s.str();
  
  file << "% Command Line arguments";
  for(int i=0;i<Argc;i++) {
    file << " " << Argv[i];
  }
  file << "\n";

  file << ps.str(); // Here we write what the library user has put in the variable ps, of type std::stringstream

  //  file << "showpage\n"; // NO: EPS should --not-- end with showpage

  // Close the file

  file.close();

';
} // endif(postscript)

$content.='
  timer2 = std::chrono::high_resolution_clock::now();

  if(verbose) {
    std::cout << "Saving picture took: ";
    pct(timer1,timer2);
    std::cout << "\n\n";
  }
}
';

if($batch)  {
$content.='
/*
 * The name of the file will be filename000123.png where
 * the nb of digits is given by ndigits
 * n better be < 10^ndigits   
 */
std::string batchFilename(int n, int ndigits) {

  std::stringstream s;
  // open file

  s << filename;
  if(ndigits>0) {
    s.width(ndigits);
    s.fill(\'0\');
    s << std::right << n;
  }
  s << "'.($colorType=='ps' ? '.eps' : '.png').'";

  return s.str();
}

/*
 * launches a series of computation (movie)
 */
void run() {
  std::chrono::high_resolution_clock::time_point
     timer01,timer02 // to chronometerize total computation time
    ,timer1,timer2;  // to chronometerize each image

  timer01 = std::chrono::high_resolution_clock::now();

  int nDigits,aux1;

  // count the number of digits needed to number the pictures
  aux1=10; for(nDigits=1; aux1<frames+1; nDigits++) aux1*=10;

  // compute each image
  int k=skip;
  for(; k<frames; k++) {
    frameNumber=k;
    timer1 = std::chrono::high_resolution_clock::now();
	std::cout << "Computation number " << 1+k-skip << "/" << frames-skip << ". ";
';
if($colorType=='ps') 
  $content .= '
    filestr="";
';
if($colorType!='no pic')
  $content.='
    std::string filename2 = batchFilename(k,nDigits);
    std::cout << "Image " << filename2 << std::endl;
';
$content .= '
    performComputations();

';
if($colorType!='no pic')
  $content.='
    // save picture
    savePic(filename2);
    timer2 = std::chrono::high_resolution_clock::now();
	std::cout << " (saved) took: ";
    pct(timer1,timer2);
';
$content.='
    if(verbose) std::cout << std::endl;

    std::cout << "\n";

  }

  timer02 = std::chrono::high_resolution_clock::now();

  // Print total computation time

  std::cout << "Total Duration: ";
  pct(timer01,timer02);
  std::cout << "\n\n";
}
';
}

$content.='
/*
 * main is the entry point of every C/C++ program
 */ 
int main(int argc, char** argv) {
  std::chrono::high_resolution_clock::time_point timer1,timer2; // program duration time

  timer1 = std::chrono::high_resolution_clock::now();

  Argc=argc; Argv=argv;

  // all the command line argument reading/processing is done in clread.cc
  #include '.phpgcc_lib_include('clread.cc').'
';
if(in_array($colorType,array('true color','palette'))) {
  $content.='
  img.width = width;
  img.height = height;
  img.bit_depth = 8;
';
  if($colorSpace=='linear')
  $content.='
  img.has_sRGB=false;
  img.has_gamma=true;
  img.decoding_gamma=1.0;
';
}
if($colorType=='true color')
  $content.='
  img.color_type = PNG_COLOR_TYPE_RGB;

  // allocate memory (auto-deallocated by RAII)
  img.data.resize(width*height*3);

';
if($colorType=='palette') {
  $content.='
  img.color_type = PNG_COLOR_TYPE_PALETTE;
  img.palette.resize(256);
  // let us define a grayscale default palette
  for(int i=0; i<256; i++) { 
    setColor(i,i,i,i);
  }

  // allocate memory (auto-deallocated by RAII)
  img.data.resize(width*height);
';
}
$content .= '
  // init  
';
$content .= $init."\n";

if($batch) { $content .= '
  run();
';
}
else { $content .= '
  performComputations();
';
  if($colorType!='no pic') $content .= '
  savePic(filename);
';
}

$content .= '
  timer2 = std::chrono::high_resolution_clock::now();

  // Print program duration

  std::cout << "Program Duration: ";
  pct(timer1,timer2);
  std::cout << "\n\n";

  // terminate the program

  return 0;
}
';

// **********

// Write $content to the source file and call the compile command

// **********


fwrite($handle,$content);

exec($compile);
if($postprocess!="") exec($postprocess);

?>
