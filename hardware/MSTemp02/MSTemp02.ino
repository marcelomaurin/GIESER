#include <ESP8266WiFi.h>
#include <Wire.h> 
#include <SoftwareSerial.h>


// for DHT11, 
//      VCC: 5V or 3V
//      GND: GND
//      DATA: 2
// Conversion factor - CPM to uSV/h
#define CONV_FACTOR 0.0793

#ifndef STASSID
#define STASSID "maurin"
#define STAPSK "1425361425"
#endif

const char* ssid = STASSID;
const char* password = STAPSK;

const int GEIGER_COUNTER_PIN = D3; /*Login pin esp8266*/
// Defina os pinos RX e TX para a conexão serial por software
const byte swSerialRX = D5; // GPIO 14
const byte swSerialTX = D6; // GPIO 12

// Variables
long count = 0;
long countPerMinute = 0;
float usvh = 1.0;
long timePreviousMeassure = 0;

byte data[40] = {0};
static char disp[10];
static char disp2[10];

// Crie uma instância da conexão serial por software
SoftwareSerial swSerial(swSerialRX, swSerialTX);


void start_SoftSerial()
{
  // Inicie a comunicação serial por software
  swSerial.begin(9600);

}

void start_Serial()
{
  Serial.begin(115200);
}

void start_Wifi()
{
   /* Explicitly set the ESP8266 to be a WiFi-client, otherwise, it by default,
     would try to act as both a client and an access-point and could cause
     network-issues with your other WiFi-devices on your WiFi-network. */
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED) {
      delay(500);
      Serial.print(".");
    }

    Serial.println("");
    Serial.println("WiFi connected");
    Serial.println("IP address: ");
    Serial.println(WiFi.localIP());
}

void start_Geiser()
{  
  // Init pins
  //pinMode(GEIGER_COUNTER_PIN, INPUT);
   //pinMode(interruptPin, INPUT_PULLUP);
  pinMode(GEIGER_COUNTER_PIN, INPUT_PULLUP);
  //attachInterrupt(digitalPinToInterrupt(GEIGER_COUNTER_PIN), tick, FALLING); //define external interrupts
  attachInterrupt(digitalPinToInterrupt(GEIGER_COUNTER_PIN), tick, CHANGE); //define external interrupts
  Serial.println("Init arduino geiger counter");  
}


void setup() {
  start_Serial();
  start_SoftSerial();
  start_Wifi();
  //start_Geiser();
}

//Escreve no nextion
void NextionValue(char *info)
{
  char cmd[80];
  memset(cmd,'\0',sizeof(cmd));
  sprintf(cmd,"Menu.t0.txt='%s'%c%c%c",info, 0xFF, 0xFF, 0xFF );
  swSerial.print(cmd);
  //myNextion.setText("p0.t0", "-1");
  Serial.println(cmd);
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

void readSoftSerial()
{
  // Leia os dados da conexão serial por software e envie para a conexão serial por hardware
  if (swSerial.available()) {
    char receivedChar = swSerial.read();
    Serial.print(receivedChar);
  }
}

void writeSerial()
{  
  Serial.print("CPM:");
  Serial.println( countPerMinute);
  delay(200);
  Serial.print("uSVH:");
  Serial.println(usvh);
  delay(200);
}

void writeNextion()
{
  char info[20];
  memset(info,'\0',sizeof(info));
  sprintf(info,"%f",usvh);
  NextionValue(info);
}

void loop() {
  // start working...
  //readGeiser();
  writeSerial();
  readSoftSerial();
  writeNextion();
  
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
