#include <SimpleDHT.h>
#include <Wire.h> 
#include <LiquidCrystal_I2C.h>
#include <SPI.h>
#include <Ethernet.h>

// for DHT11, 
//      VCC: 5V or 3V
//      GND: GND
//      DATA: 2
// Conversion factor - CPM to uSV/h
#define CONV_FACTOR 0.0793

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

// Enter a MAC address for your controller below.
// Newer Ethernet shields have a MAC address printed on a sticker on the shield
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

// if you don't want to use DNS (and reduce your sketch size)
// use the numeric IP instead of the name for the server:
//IPAddress server(74,125,232,128);  // numeric IP for Google (no DNS)
char server[] = "maurinsoft.com.br";    // name address for Google (using DNS)

// Set the static IP address to use if the DHCP fails to assign
IPAddress ip(192, 168, 0, 177);
IPAddress myDns(192, 168, 0, 1);

// Initialize the Ethernet client library
// with the IP address and port of the server
// that you want to connect to (port 80 is default for HTTP):
EthernetClient client;

// Variables to measure the speed
unsigned long beginMicros, endMicros;
unsigned long byteCount = 0;
bool printWebData = true;  // set to false for better speed measurement

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

void start_Ethernet()
{
  // start the Ethernet connection:
  Serial.println("Initialize Ethernet with DHCP:");
  if (Ethernet.begin(mac) == 0) {
    Serial.println("Failed to configure Ethernet using DHCP");
    // Check for Ethernet hardware present
    if (Ethernet.hardwareStatus() == EthernetNoHardware) {
      Serial.println("Ethernet shield was not found.  Sorry, can't run without hardware. :(");
      while (true) {
        delay(1); // do nothing, no point running without Ethernet hardware
      }
    }
    if (Ethernet.linkStatus() == LinkOFF) {
      Serial.println("Ethernet cable is not connected.");
    }
    // try to configure using IP address instead of DHCP:
    Ethernet.begin(mac, ip, myDns);
  } else {
    Serial.print("  DHCP assigned IP ");
    Serial.println(Ethernet.localIP());
    lcd.setCursor(0,1);
    lcd.print(Ethernet.localIP());
    delay(2000);
  }
 
}

void start_LCD()
{
  lcd.init();                      // initialize the lcd 
  // Print a message to the LCD.
  lcd.backlight();
  lcd.setCursor(0,0);
  lcd.print("Cont Gaiser");
  lcd.setCursor(0,1);
  lcd.print("Buscando Rede...");
  start_Ethernet();
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
  lcd.print(Ethernet.localIP());
  //sprintf(aux,"T:%sC H:%s",dtostrf(temperature,5,1,disp),dtostrf(humidity,5,1,disp2));
  //Serial.print("Display:");
  //Serial.println(aux);
  //lcd.printstr("ABCDE");
  //lcd.print(aux);
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
  if (millis() - timePreviousMeassure > 10000){
    countPerMinute = 6 * count;
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

void WriteSite()
{
   // give the Ethernet shield a second to initialize:
  delay(1000);
  Serial.print("connecting to ");
  Serial.print(server);
  Serial.println("...");

  // if you get a connection, report back via serial:
  if (client.connect(server, 8082)) {
    Serial.print("connected to ");
    Serial.println(client.remoteIP());
    // Make a HTTP request:
    client.println("GET /search?q=arduino HTTP/1.1");
    client.println("Host: maurinsoft.com.br");
    client.println("Connection: close");
    client.println();
  } else {
    // if you didn't get a connection to the server:
    Serial.println("connection failed");
  }
  beginMicros = micros();
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