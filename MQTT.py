import paho.mqtt.client as mqtt
import mysql.connector
from mysql.connector import Error
from datetime import datetime
import json

MQTT_BROKER = "localhost"
MQTT_PORT = 1883
MQTT_TOPIC = "sensor/data"

# MySQL 設定
DB_HOST = "localhost"
DB_USER = "root"
DB_PASSWORD = ""
DB_NAME = "dht"

# 儲存資料庫連接
connection = None

# 連接到 MySQL 資料庫
def connect_to_db():
    global connection
    if connection is None or not connection.is_connected():
        try:
            connection = mysql.connector.connect(
                host=DB_HOST,
                user=DB_USER,
                password=DB_PASSWORD,
                database=DB_NAME
            )
            if connection.is_connected():
                print("成功連接到資料庫")
        except Error as e:
            print(f"資料庫連接失敗: {e}")
            connection = None
    return connection

# 處理 MQTT 訊息
def on_message(client, userdata, msg):
    try:
        message = msg.payload.decode("utf-8")
        print(f"接收到訊息：{message}")

        data = json.loads(message)
        distance = data["distance"]
        temperature = data["temperature"]
        humidity = data["humidity"]
        timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

        connection = connect_to_db()
        if connection:
            cursor = connection.cursor()
            insert_query = """
                INSERT INTO dht (temperature, humidity, distance, datetime)
                VALUES (%s, %s, %s, %s)
            """
            cursor.execute(insert_query, (temperature, humidity, distance, timestamp))
            connection.commit()
            print("訊息已成功儲存到資料庫")
            cursor.close()
    except Error as e:
        print(f"儲存失敗: {e}")
    except Exception as ex:
        print(f"處理訊息時出現錯誤: {ex}")

# 設置 MQTT 客戶端
def setup_mqtt_client():
    client = mqtt.Client()
    client.on_message = on_message
    client.connect(MQTT_BROKER, MQTT_PORT, 60)
    client.subscribe(MQTT_TOPIC)
    print(f"已訂閱主題：{MQTT_TOPIC}")
    return client

if __name__ == "__main__":
    mqtt_client = setup_mqtt_client()
    print("開始接收 MQTT 訊息...")
    mqtt_client.loop_forever()
