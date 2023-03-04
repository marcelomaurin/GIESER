#include <SimpleDHT.h>
#include <Wire.h> 
#include <LiquidCrystal_I2C.h>
//#include <Adafruit_SSD1306.h>
//#include <splash.h>

// for DHT11, 
//      VCC: 5V or 3V
//      GND: GND
//      DATA: 2
// Conversion factor - CPM to uSV/h
#define CONV_FACTOR 0.00812

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
static char disp[7];
char aux[16];


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
  lcd.init();
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
  lcd.setCursor(0,0);
  sprintf(aux,"T:%sC H:%sRH",dtostrf(temperature,5,2,disp),dtostrf(humidity,5,2,disp));
  lcd.print(aux);
  lcd.setCursor(0,1);
  sprintf(aux,"P:%s Sv:%s",dtostrf(countPerMinute,5,2,disp),dtostrf(usvh,5,2,disp));
  lcd.print(aux);

}

void readGeiser()
{  
  if (millis() - timePreviousMeassure > 10000){
    countPerMinute = 6 * count;
    usvh = countPerMinute * CONV_FACTOR;
    timePreviousMeassure = millis();
    Serial.print("cpm="); 
    Serial.print(countPerMinute, DEC);
    Serial.print(";"); 
    Serial.print("uSv/h=");
    Serial.println(usvh, 4);
    //writeOledScreenText((String) countPerMinute, (String) usvh); // Todo, verify this
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
  Serial.print("Humidade:");
  Serial.println((float)humidity);
  Serial.print("CPM:");
  Serial.println( countPerMinute);
  Serial.print("uSVH:");
  Serial.println(usvh);
}

void loop() {
  // start working...
  readDHT();
  readGeiser();
  writeLCD();
  // DHT11 sampling rate is 1HZ.
  writeSerial();
  //delay(1500);
}

void tick() {
  Serial.println("t;");
  detachInterrupt(0);
  count++;
  while(digitalRead(GEIGER_COUNTER_PIN) == 0){
  }
  attachInterrupt(0, tick, FALLING);
}
