#include <SimpleDHT.h>
#include <Wire.h> 
#include <LiquidCrystal_I2C.h>

// for DHT11, 
//      VCC: 5V or 3V
//      GND: GND
//      DATA: 2
// Conversion factor - CPM to uSV/h
//#define CONV_FACTOR 0.0793  //USP balanceado
#define CONV_FACTOR 0.0085  //contador chines

//#define MAXTIME 10000 //Tempo entre leituras 10s
#define MAXTIME 10000 //Tempo entre leituras 10s

// Pins
int pinDHT22 = 4;

const int GEIGER_COUNTER_PIN = 3;

// Variables
long count = 0;
long countPerMinute = 0;
float usvh = 0.0;
long timePreviousMeassure = 0;

SimpleDHT22 dht22(pinDHT22);

LiquidCrystal_I2C lcd(0x27,16,2);  // set the LCD address to 0x27 for a 16 chars and 2 line display

float temperature = 0;
float humidity = 0;
byte data[40] = {0};
static char disp[10];
static char disp2[10];



void start_Serial()
{
  Serial.begin(115200);
}

void start_Geiser()
{
  
  // Init pins
  pinMode(GEIGER_COUNTER_PIN, INPUT);
  attachInterrupt(digitalPinToInterrupt(GEIGER_COUNTER_PIN), tick, FALLING); //define external interrupts
  Serial.println("Init arduino geiger counter");
  
}

void start_LCD()
{
  lcd.init();                      // initialize the lcd 
  // Print a message to the LCD.
  lcd.backlight();
}

void setup() {
  start_Serial();
  
  start_LCD();
  start_Geiser();
  
}

void writeLCD()
{
  lcd.clear();
  char aux[16];
  memset(aux, 0, sizeof(aux));
  memset(disp,0,sizeof(disp));
  memset(disp,0,sizeof(disp2));
  lcd.setCursor(0,0);
  
  sprintf(aux,"T:%sC H:%s",dtostrf(temperature,5,1,disp),dtostrf(humidity,5,1,disp2));
  Serial.print("Display:");
  Serial.println(aux);
  //lcd.printstr("ABCDE");
  lcd.print(aux);
  memset(aux, 0, sizeof(aux));
  memset(disp,0,sizeof(disp));
  memset(disp,0,sizeof(disp2));
  lcd.setCursor(0,1);
  sprintf(aux,"%suSV/h F:%d",dtostrf(usvh,5,2,disp),countPerMinute);
  lcd.print(aux);
  //lcd.printstr("1234");
  Serial.print("Display:");
  Serial.println(aux);
  
  
}

void readGeiser()
{  
  if (millis() - timePreviousMeassure > MAXTIME){
    countPerMinute = ((int)((60/(MAXTIME/1000))) * count);
    usvh = countPerMinute * CONV_FACTOR;
    timePreviousMeassure = millis();
    Serial.print("cpm="); 
    Serial.print(countPerMinute, DEC);
    Serial.print(";"); 
    Serial.print("uSv/h=");
    Serial.println(usvh, 4);
    count = 0;
  }
  
  
}

void readDHT()
{ 
  // read with raw sample data.
  
   int err = SimpleDHTErrSuccess;
  if ((err = dht22.read2(&temperature, &humidity, NULL)) != SimpleDHTErrSuccess) {
    Serial.print("Read DHT22 failed, err="); Serial.println(err);delay(2000);
    return;
  }
}


void writeSerial()
{
  
  Serial.print("Temperatura:");
  Serial.println((float)temperature); 
  delay(200);
  Serial.print("Humidade:");
  Serial.println((float)humidity);
  delay(200);
  Serial.print("CPM:");
  Serial.println( countPerMinute);
  delay(200);
  Serial.print("uSVH:");
  Serial.println(usvh);
  delay(200);
}

void loop() {
  // start working...
  readDHT();
  readGeiser();
  writeLCD();
  // DHT21 sampling rate is 1HZ.
  writeSerial();
  delay(500);
}

void tick() {
  Serial.println("t;");
  detachInterrupt(0);
  count++;
  while(digitalRead(GEIGER_COUNTER_PIN) == 0){
  }
  attachInterrupt(0, tick, FALLING);
}
