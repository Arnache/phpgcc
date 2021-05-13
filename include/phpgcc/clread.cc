/*

 clread.cc : Command Line Read.
 
 version 2 (some C-inherited features were converted to C++ standard facilities)
 
 This is an inclusion file.
 It is to be included with #include "clread.cc" at the very beginning of main()

 It will read the command line arguments (using the getopt library #include <getopt.h>)
 and call the procedure setParam() accordingly

*/

{ // insulation scope (so that vars defined here are only local)

  if(argc==1 && hasMandatoryArguments) {
    explainSyntax();
    exit(1);
  }

  // Let's read the command line arguments

  // We'll use the GNU command getopt_long_only

  bool super[paramNumber];
  bool set[paramNumber];
  std::string value[paramNumber];
  
  bool askedForHelp, version, askedForOptions;
  // !! the boolean variable 'verbose' shall be global

  for(int i=0; i<paramNumber; i++) super[i]=false; 
  for(int i=0; i<paramNumber; i++) set[i]=false; 

  int c;
  askedForHelp = false;
  askedForOptions = false;
  verbose = false;
  version = false;
  int index;
  // deal with options
  while((c = getopt_long_only(argc, argv, "", options, &index)) != -1) {
    if(c=='?') {
      std::cout << "Command line arguments malformed." << std::endl;
      std::cout << "Type '" << progName << " --help' for general syntax." << std::endl;
      std::cout << "Type '" << progName << " --options' for options description." << std::endl;
      std::cout << "Type '" << progName << " --options --verbose' for a detailed description." << std::endl;
      exit(1);
    }
    if(index==HELP_INDEX) askedForHelp = true;
    else if(index==VERBOSE_INDEX) verbose = true;
    else if(index==VERSION_INDEX) version = true;
    else if(index==OPTIONS_INDEX) askedForOptions = true;
    else {
      if(super[index]) {
        std::cout << "Command line argument error:" << std::endl << "  parameter '" << options[index].name << "' set at least twice" << std::endl;
        exit(-1);
      }
      else {
        // save the value
        super[index] = true;
        value[index] = std::string(optarg);
      }
    }
  }
  // deal with non-options
  // CAUTION: the function getopt_long_only(...) pushes these arguments
  // to the end of argv, so that in fact they can have been put anywhere
  // in the initial argv list.
  int settingsFilesNumber=argc-optind;
  std::vector<std::string> settingsFilenames;
  if(settingsFilesNumber>0) {
    settingsFilenames.resize(settingsFilesNumber);
    for(int i=optind; i<argc; i++) {
      settingsFilenames[i-optind]=std::string(argv[i]);
    }
  }
  /*
  if(optind==argc) {
    settingsFilename=NULL;
  }
  else if(optind==argc-1) {
    settingsFilename=argv[optind];
  }
  else {
    std::cout << "Command line argument error:\n"
         << "  besides the ones of the form '-name value', there should be only" << std::endl << "  one argument (the name of the settings text file). It seems that" << std::endl << "  here, several where provided." << std::endl; 
    std::cout << "Type '" << progName << " --help' for a description." << std::endl;
    exit(1);
  }
  */

  if(version) {
    showVersion();
    if(argc==2) exit(0);
  }

  if(askedForHelp) {
    help();
  }

  if(askedForOptions) {
    listOptions();
  }

  if(askedForHelp || askedForOptions) {
    exit(0);
  }
  
  std::string str, str2, str3;
  size_t i1,i2,i3;
  int line;

  for(int j=0; j<settingsFilesNumber; j++) {
    std::ifstream settingsFile;
    std::string settingsFilename = settingsFilenames[j];
    settingsFile.open(settingsFilename); // text mode
    if(settingsFile.fail()) {
      std::cout << "Can't open file " << settingsFilename;
      perror("");
      exit(1);
    }
  
    std::cout << "\nReading parameters file " << settingsFilenames[j] << "\n" << std::endl;
   
    std::string str;

    for(line=1 ; !(settingsFile.eof()); line++) {
      if(std::getline(settingsFile, str)) { // read one line    
        // is the line a comment ?
        i2=str.find_first_not_of(' ');
        if(i2==std::string::npos) goto nextline;
        if(str.at(i2)=='#' || str.at(i2)=='%' || str.at(i2)=='/') goto nextline;
        // seek the '=' sign
        i1=str.find_first_of('=');
        if(i1!=std::string::npos) {

          // get part before = sign
          str2=str.substr(0,i1);
          // strip possible whitespaces at the beginning and the end
          i2=str2.find_last_not_of(' ');
          if(i2!=std::string::npos) str2=str2.substr(0,i2+1);
          i2=str2.find_first_not_of(' ');
          if(i2!=std::string::npos) str2=str2.substr(i2,std::string::npos);
          
          // get part after the = sign
          str3=str.substr(i1+1,std::string::npos);
          // strip possible whitespaces at the beginning and the end
          i3=str3.find_last_not_of(" \n\r");
          if(i3!=std::string::npos) str3=str3.substr(0,i3+1);
          i3=str3.find_first_not_of(' ');
          if(i3!=std::string::npos) str3=str3.substr(i3,std::string::npos);

          for(int i=0; i<paramNumber; i++) {
            if(str2.compare(options[i].name)==0) {
              if(set[i]) {
                std::cout << "Error in settings files. While reading " << settingsFilename << ", the parameter '" << options[i].name << "' has been set a second time.\n";
                exit(-1);
              }
              set[i]=true;
              setParam(i,str3.c_str());
              goto go_on;
            }
          }     

          std::cout << "ERROR, unkown parameter name '" << str2 << "'.\n";
          exit(1);
         go_on: {}
        }
        else {
          i2=str.find_first_not_of(" \n\r");
           if(i2!=std::string::npos) {
           std::cout << "ERROR parsing settings file " << settingsFilename << ": '=' sign not found in line " << line << ".\n";
           exit(1);
          }
        }
       nextline:
        ;
      }
    }
    settingsFile.close();
  }
  
  if(verbose) std::cout << "\nSetting/replacing values given on the command line (if any)\n" << std::endl;

  // command line options supersede the value (if any)
  for(int i=0; i<paramNumber; i++) {
    if(super[i]) {
      set[i]=true;
      setParam(i,value[i]);
    }
  }  

  if(verbose) std::cout << "\nSetting default values for others (if any left)\n" << std::endl;
  
  // Default values for others
  for(int i=0; i<paramNumber; i++) {
    if(hasDefault[i]) { 
      if(!set[i]) {
        set[i]=true;
        setParam(i,defaultValue[i]);
      }
    }
  }  
  
  for(int i=0; i<paramNumber; i++) {
    if(!set[i]) {
      std::cout << "ERROR all parameters were not set." << std::endl;
      std::cout << "Missing ones:";
      for(int i=0; i<paramNumber; i++) {
        if(!set[i]) std::cout << " " << options[i].name;    
      }
      std::cout << std::endl;
      exit(-1);
    }
  }

  std::cout << std::endl;

} // end of insulation scope
