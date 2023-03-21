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


void NextionMensage(String info);
void NextionWAITESC();
void showPageId();
void NextionFieldText(char *field,char *value);
void NextionShow(char* info1);
void NextionValue(char *info);




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
    NextionFieldText("Menu.att","Conectado!!!");
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

/*Captura a pagina em que o nextion esta*/
void showPageId() {
  char pageId[10];
  swSerial.print("sendme\n"); // Enviar o comando "sendme" para solicitar o ID da página atual
  delay(10);
  while (swSerial.available() > 0) { // Esperar até que haja dados disponíveis na serial
    char c = swSerial.read();
    if (c == 0xFF) { // Verificar se é um byte de início de mensagem
      int i = 0;
      while (swSerial.available() > 0 && i < sizeof(pageId) - 1) { // Ler o ID da página até o final da mensagem
        c = swSerial.read();
        if (c == 0xFF) { // Verificar se é um byte de início de mensagem (pode ocorrer dentro da mensagem)
          i = 0;
        } else if (c == '\n') { // Verificar se é o final da mensagem
          pageId[i] = '\0'; // Adicionar terminador de string ao final do ID da página
          break;
        } else {
          pageId[i] = c;
          i++;
        }
      }
      Serial.print("ID da pagina atual: ");
      Serial.println(pageId);
    }
  }
}


void NextionShow(char* info1)
{
  char strFF = 0xFF;
  char cmd[40];
  char *pos;
  Serial.print("Info:");
  Serial.println(info1);
  pos =strstr(info1,"\n");
  
  Serial.print("Info:");
  Serial.println(info1);
  memset(cmd,'\0',sizeof(cmd));

  sprintf(cmd,"page %s%c%c%c",info1,strFF,strFF,strFF);  
  Serial.println(cmd);
  swSerial.print(cmd);  
  delay(100);  
}

void NextionFieldText(char *field,char *value)
{
  char strFF = 0xFF;  
  //String cmd;
  char cmd[40];
  memset(cmd,'\0',sizeof(cmd));
  sprintf(cmd,"%s.txt=\"%s\"%c%c%c",field,value,strFF,strFF,strFF);  
  //cmd = field+".txt=\""+value+"\""+String(strFF)+String(strFF)+String(strFF);
  Serial.println(cmd); 
  swSerial.println(cmd);  
}

void NextionWAITESC()
{
  
}

void NextionMensage(String info)
{
  char strFF = 0xFF;
  swSerial.print("page MSG"+String(strFF)+String(strFF)+String(strFF));  
  delay(100);
  String cmd;
  
  cmd = "MSGtxt.txt=\""+info+"\""+String(strFF)+String(strFF)+String(strFF);
  Serial.println(cmd);  
  swSerial.print(cmd);
}

void NextionMensageSTOP(String info)
{
  char strFF = 0xFF;
  swSerial.print("page MSG"+String(strFF)+String(strFF)+String(strFF));  
  delay(100);
  String cmd;
  
  cmd = "MSGtxt.txt=\""+info+"\" "+String(strFF)+String(strFF)+String(strFF);
  Serial.println(cmd);  
  swSerial.print(cmd);
  NextionWAITESC();
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
  sprintf(info,"%.2f",usvh);
  //NextionValue(info);
  NextionFieldText("Menu.t0",info);
  //NextionFieldText("Menu.att",String(WiFi.localIP()).c_str());
  
  
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
