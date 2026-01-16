# ESP32 + MQTT + MySQL + PHP 物聯網感測器監控系統

## 📋 專案簡介

這是一個物聯網（IoT）感測器監控系統，使用 ESP32 開發板收集環境感測器資料（溫度、濕度、距離），透過 MQTT 通訊協定傳輸資料，儲存到 MySQL 資料庫，並透過 PHP 網頁即時顯示監控儀表板。

### 系統架構

```
ESP32 (感測器) → MQTT Broker → Python 訂閱程式 → MySQL 資料庫 → PHP 後端 → 網頁前端
```

## 🎯 功能特色

- ✅ **即時感測器資料收集**：使用 DHT11 溫濕度感測器和超音波距離感測器
- ✅ **MQTT 通訊**：使用輕量級的 MQTT 協定進行資料傳輸
- ✅ **資料庫儲存**：將感測器資料永久儲存在 MySQL 資料庫
- ✅ **即時監控儀表板**：透過網頁即時查看感測器數值和歷史趨勢圖表
- ✅ **自動更新**：網頁每 5 秒自動更新最新資料

## 📦 硬體需求

### 必要元件

- ESP32 開發板 × 1
- DHT11 溫濕度感測器 × 1
- HC-SR04 超音波距離感測器 × 1
- 杜邦線 數條

### 接線方式

#### DHT11 接線

- VCC → 3.3V
- GND → GND
- DATA → GPIO 19

#### HC-SR04 超音波感測器接線

- VCC → 5V
- GND → GND
- TRIG → GPIO 5
- ECHO → GPIO 18

## 💻 軟體需求

### 開發環境

- **Arduino IDE**：用於編譯和上傳 ESP32 程式碼
- **Python 3.x**：執行 MQTT 訂閱程式
- **MySQL**：資料庫伺服器
- **PHP 7.4+**：後端網頁伺服器（建議使用 XAMPP 或 WAMP）
- **Mosquitto MQTT Broker**：MQTT 訊息代理伺服器

### Python 套件

```bash
pip install paho-mqtt mysql-connector-python
```

## 🚀 安裝與設定步驟

### 步驟 1：安裝 MQTT Broker (Mosquitto)

#### Windows

1. 下載並安裝 Mosquitto：https://mosquitto.org/download/
2. 將 `mosquitto.conf` 檔案複製到 Mosquitto 安裝目錄
3. 啟動 Mosquitto：
   ```bash
   mosquitto -c mosquitto.conf
   ```

#### Linux

```bash
sudo apt-get install mosquitto mosquitto-clients
sudo cp mosquitto.conf /etc/mosquitto/mosquitto.conf
sudo systemctl start mosquitto
```

### 步驟 2：設定 MySQL 資料庫

1. 啟動 MySQL 服務
2. 使用 phpMyAdmin 匯入 `dht.sql` 檔案

### 步驟 3：設定 ESP32 程式碼

1. 開啟 Arduino IDE
2. 安裝必要的程式庫：

   - ESP32 開發板支援（在 Arduino IDE 的「工具」→「開發板管理員」中搜尋並安裝）
   - PubSubClient 程式庫（在「工具」→「管理程式庫」中搜尋並安裝）
   - DHT sensor library（在「工具」→「管理程式庫」中搜尋並安裝）

3. 開啟 `MQTT.ino` 檔案
4. 修改以下設定：
   ```cpp
   const char* ssid = "your_WiFi_ID";           // 改為你的 WiFi 名稱
   const char* password = "your_WiFi_passward"; // 改為你的 WiFi 密碼
   const char* mqttServer = "your_MQTT Broker_IP"; // 改為 MQTT Broker 的 IP 位址
   ```
5. 上傳程式碼到 ESP32

### 步驟 4：設定 Python MQTT 訂閱程式

1. 開啟 `MQTT.py` 檔案
2. 修改資料庫連線設定（如果需要）：
   ```python
   DB_HOST = "localhost"
   DB_USER = "root"
   DB_PASSWORD = ""  # 如果有設定密碼請填入
   DB_NAME = "dht"
   ```
3. 修改 MQTT 設定（如果 MQTT Broker 不在本機）：
   ```python
   MQTT_BROKER = "localhost"  # 改為 MQTT Broker 的 IP
   MQTT_PORT = 1883           # 如果使用 2883 請修改
   ```
4. 執行 Python 程式：
   ```bash
   python MQTT.py
   ```

### 步驟 5：設定 PHP 網頁

1. 將以下檔案複製到網頁伺服器目錄（如 XAMPP 的 `htdocs` 資料夾）：

   - `db.php`
   - `datap.php`
   - `UIP.php`

2. 修改 `db.php` 中的資料庫連線設定（如果需要）：

   ```php
   $host = '127.0.0.1';
   $db   = 'dht';
   $user = 'root';
   $pass = '';  // 如果有設定密碼請填入
   ```

3. 在瀏覽器開啟：`http://localhost/UIP.php`

## 📁 專案檔案說明

| 檔案名稱         | 說明                                                 |
| ---------------- | ---------------------------------------------------- |
| `MQTT.ino`       | ESP32 Arduino 程式碼，負責讀取感測器並發送 MQTT 訊息 |
| `MQTT.py`        | Python 程式，訂閱 MQTT 主題並將資料存入 MySQL        |
| `mosquitto.conf` | MQTT Broker 設定檔                                   |
| `dht.sql`        | MySQL 資料庫結構和範例資料                           |
| `db.php`         | PHP 資料庫連線設定檔                                 |
| `datap.php`      | PHP API，提供 JSON 格式的感測器資料                  |
| `UIP.php`        | 前端網頁，顯示即時監控儀表板                         |

## 🔧 系統運作流程

1. **資料收集**：ESP32 每 5 秒讀取一次 DHT11 和超音波感測器的數值
2. **資料傳輸**：ESP32 將感測器資料以 JSON 格式發送到 MQTT Broker 的 `sensor/data` 主題
3. **資料儲存**：Python 程式訂閱 MQTT 主題，接收到資料後存入 MySQL 資料庫
4. **資料顯示**：PHP 網頁從資料庫讀取資料，透過 Chart.js 繪製圖表，每 5 秒自動更新

## 📊 資料格式

ESP32 發送的 MQTT 訊息格式：

```json
{
  "temperature": 24,
  "humidity": 58,
  "distance": 115.44
}
```

## 🐛 常見問題排除

### ESP32 無法連接到 WiFi

- 檢查 WiFi 名稱和密碼是否正確
- 確認 ESP32 和路由器距離不要太遠
- 檢查路由器是否支援 2.4GHz（ESP32 不支援 5GHz）

### MQTT 連線失敗

- 確認 MQTT Broker 是否正常運行
- 檢查 ESP32 程式碼中的 MQTT Server IP 是否正確
- 確認防火牆沒有阻擋 MQTT 埠口（預設 1883 或設定的 2883）

### Python 程式無法連接資料庫

- 確認 MySQL 服務是否啟動
- 檢查資料庫名稱、使用者名稱和密碼是否正確
- 確認已安裝 `mysql-connector-python` 套件

### 網頁無法顯示資料

- 確認 PHP 和 MySQL 服務是否正常運行
- 檢查 `db.php` 中的資料庫連線設定
- 開啟瀏覽器開發者工具（F12）查看是否有錯誤訊息

## 📝 注意事項

- ESP32 的 MQTT 埠口設定為 2883，請確保 MQTT Broker 也使用相同埠口
- Python 程式預設使用 1883 埠口，如果 MQTT Broker 使用 2883，請修改 `MQTT.py` 中的 `MQTT_PORT`
- 建議在實際部署時為 MQTT Broker 設定使用者認證，提高安全性
- 資料庫密碼建議不要留空，應設定強密碼


## 📚 學習資源

- [ESP32 官方文件](https://docs.espressif.com/projects/esp-idf/en/latest/esp32/)
- [MQTT 協定說明](https://mqtt.org/)
- [Arduino 官方教學](https://www.arduino.cc/en/Tutorial/HomePage)
- [PHP 官方文件](https://www.php.net/docs.php)
- 
### 實體照片
- **Google雲端**:https://docs.google.com/document/d/1eLEjyF4uRs32MKpp7_eZoKOy8plpqR21/edit?usp=sharing&ouid=116740084328701321030&rtpof=true&sd=true
