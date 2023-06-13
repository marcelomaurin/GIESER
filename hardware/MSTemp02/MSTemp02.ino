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
#define PORT 80
//#define PORT 443
//#define url "https://maurinsoft.com.br"
#define url "192.168.100.5/wp-json/Geiser/v1/registro.php"


const int GEIGER_COUNTER_PIN = 3;

// Variables
long count = 0;
long countPerMinute = 0;
float usvh = 0.0;
long timePreviousMeassure = 0;



LiquidCrystal_I2C lcd(0x27,16,2);  // set the LCD address to 0x27 for a 16 chars and 2 line display

float temperature = 0;
float humidity = 0;
byte data[40] = {0};
static char disp[10];
static char disp2[10];

// Enter a MAC address for your controller below.
// Newer Ethernet shields have a MAC address printed on a sticker on the shield
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };



// Set the static IP address to use if the DHCP fails to assign
IPAddress ip(192, 168, 100, 177);
IPAddress myDns(192, 168, 100, 1);

// Initialize the Ethernet client library
// with the IP address and port of the URL
// that you want to connect to (port 80 is default for HTTP):
//EthernetClient client;

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
  // Define the MAC address, IP address and DNS server address
  //byte mac[] = {0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED};
  //IPAddress ip(192, 168, 1, 177);
  //IPAddress myDns(8, 8, 8, 8);

  // Start the Ethernet connection:
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
  // Cria uma instância do objeto EthernetClient para estabelecer a conexão
  EthernetClient client;
  // give the Ethernet shield a second to initialize:
  delay(1000);
  Serial.print("connecting to ");
  Serial.print(url);
  Serial.println("...");
  //if (client.connect(server, httpsPort)) {
  // if you get a connection, report back via serial:
  if (client.connect(url, PORT)) {
    Serial.print("connected to ");
    Serial.println(client.remoteIP());
/*
    // Cabeçalho HTTP
    client.println("POST /endpoint HTTP/1.1");
    client.println("Host: api.exemplo.com");
    client.println("Content-Type: application/json");
    client.println("Connection: close");
    client.print("Content-Length: ");
    client.println(strlen(json));
*/
    // Make a HTTP POST request:
    client.println("POST /wp-json/Geiser/v1/registro.php HTTP/1.1");
    client.print("Host: ");
    client.println(url);
    client.println("Content-Type: application/json");
    client.println("Connection: close");
    client.print("usvh: ");
    client.println(usvh);
    client.print("temp: ");
    client.println(temperature);
    client.print("hum: ");
    client.println(humidity);


    // Calculate the content length
    String jsonBody = String("{\"usvh\":") + String(usvh) + String(",\"temp\":") + String(temperature) + String(",\"hum\":") + String(humidity) + String("}");
    client.print("Content-Length: ");
    client.println(jsonBody.length());

    client.println(); // Required empty line before the body
    client.print(jsonBody);
    client.println();
    client.stop();
  } else {
    // if you didn't get a connection to the server:
    Serial.println("connection failed");
  }
  beginMicros = micros();
 
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
  readGeiser();
  writeLCD();
  WriteSite(); /*Grava dados no site */
  writeSerial();
  delay(1000);
}

void tick() {
  Serial.println("t;");
  detachInterrupt(0);
  count++;
  while(digitalRead(GEIGER_COUNTER_PIN) == 0){
  }
  attachInterrupt(0, tick, FALLING);
}
