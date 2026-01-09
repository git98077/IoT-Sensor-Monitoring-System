#include <WiFi.h>
#include <PubSubClient.h>

#include <DHT.h>
#define DHTPIN 19
#define DHTTYPE DHT11
DHT dht11(DHTPIN, DHTTYPE);

// 超音波模組腳位
#define TRIG_PIN 5
#define ECHO_PIN 18
const char* mqttServer = "your_MQTT Broker_IP"; // MQTT Broker 的 IP 位址
const int mqttPort = 2883; // MQTT 端口
const char* mqttUser = ""; // 若需要認證，這裡填入 MQTT 的使用者名稱
const char* mqttPassword = ""; // 若需要認證，這裡填入 MQTT 的密碼
WiFiClient espClient;
PubSubClient client(espClient); // 建立 MQTT 客戶端物件


const char* ssid = "your_WiFi_ID";
const char* password = "your_WiFi_passward";

int temperature = 0;
int humidity = 0;
float distance = 0;

void setup() {
  Serial.begin(115200);

  dht11.begin();

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  connectWiFi();
  client.setServer(mqttServer, mqttPort);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

 // 嘗試連接 MQTT（若連線失敗，跳過 MQTT 但繼續執行其他部分）
  if (!client.connected()) {
    if (!reconnectMQTT()) {
      Serial.println("MQTT 連線失敗，跳過本次傳送");
    }
  }
  client.loop(); // 保持 MQTT 連線活躍（若已連線）
  // 讀取 DHT11 資料
  Load_DHT11_Data();
  distance = readUltrasonicDistance();
  // 準備 MQTT 資料
  String payload = "{\"temperature\":" + String(temperature) +
                   ",\"humidity\":" + String(humidity) +
                   ",\"distance\":" + String(distance) + "}";


  // 若 MQTT 已連線，傳送資料
  if (client.connected()) {
    if (client.publish("sensor/data", payload.c_str())) {
      Serial.println("資料已傳送至 MQTT Broker");
    } else {
      Serial.println("MQTT 傳送失敗");
    }
  }
  delay(5000); // 減少延遲，保持檢查頻率
}

  //取得溫溼度資料
void Load_DHT11_Data() {
  temperature = dht11.readTemperature();
  humidity = dht11.readHumidity();
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("Failed to read from DHT sensor!");
    temperature = 0;
    humidity = 0;
  }
  Serial.printf("Temperature: %d °C\n", temperature);
  Serial.printf("Humidity: %d %%\n", humidity);
}

float readUltrasonicDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH, 30000); // 最長等待 30ms（約 5m）
  float distance_cm = duration * 0.0343 / 2;

  if (duration == 0) {
    Serial.println("Ultrasonic sensor timeout!");
    return -1.0; // 表示無法測量
  }

  Serial.printf("Distance: %.2f cm\n", distance_cm);
  return distance_cm;
}

void connectWiFi() {
  WiFi.mode(WIFI_OFF);
  delay(1000);
  WiFi.mode(WIFI_STA);

  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.print("connected to : "); Serial.println(ssid);
  Serial.print("IP address: "); Serial.println(WiFi.localIP());
}

bool reconnectMQTT() {
  Serial.print("正在嘗試連接 MQTT...");
  if (client.connect("ESP32Client", mqttUser, mqttPassword)) {
    Serial.println("MQTT 連線成功");
    return true;
  } else {
    Serial.print("MQTT 連線失敗，錯誤碼：");
    Serial.println(client.state());
    return false;
  }
}
