class MainClass {
public:
  void run();

  // Constructor
  
  MainClass() {
    PSoffx=200;
    PSoffy=200;
    PSscale=50;

    bbLeft=0;
    bbRight=400;
    bbBottom=0;
    bbTop=400;
  }
};

void MainClass::run() {
	ps << "1 setlinejoin\n";
	ps << "1 setlinecap\n";

	ps << "newpath\n";
	moveto(0);
	lineto(cmplex(1,1));
	ps << "stroke\n";
}
