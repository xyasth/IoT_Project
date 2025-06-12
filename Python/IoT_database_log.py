import serial
from pyrebase import pyrebase
import time

# --- Firebase Config ---
firebaseConfig = {
  "apiKey": "AIzaSyAr4w2qoufMstWCltsUFhZIQF-6HBmHoJY",
  "authDomain": "iot-log-beda1.firebaseapp.com",
  "databaseURL": "https://iot-log-beda1-default-rtdb.firebaseio.com/",
  "projectId": "iot-log-beda1",
  "storageBucket": "iot-log-beda1.firebasestorage.app",
  "messagingSenderId": "705520819911",
  "appId": "1:705520819911:web:ea4ca64ecb71fc789028e2",
  "measurementId": "G-EXN18J928J"
}

firebase = pyrebase.initialize_app(firebaseConfig)
db = firebase.database()

# --- Serial Config ---
ser = serial.Serial('COM5', 9600)  # Replace 'COM5' with your Arduino port
time.sleep(2)  # Wait for Arduino to reset

# --- State Tracking ---
car_moving = False
last_weight = 0.0

while True:
    try:
        line = ser.readline().decode().strip()
        print("From Arduino:", line)

        # Parse color sensor data
        if line.startswith("R:"):
            parts = line.replace("R:", "").replace("G:", "").replace("B:", "").split()
            red = int(parts[0])
            green = int(parts[1])
            blue = int(parts[2])

            data = {
                "red": red,
                "green": green,
                "blue": blue,
                "timestamp": time.time()
            }

            db.child("sensor_data").push(data)
            print("Uploaded to Firebase:", data)

        # Parse weight
        elif line.startswith("Weight:"):
            parts = line.replace("Weight:", "").replace("grams", "").strip()
            last_weight = float(parts)

        # Detect movement start
        elif "Car STARTED moving." in line and not car_moving:
            car_moving = True
            log = {
                "event": "Car STARTED moving",
                "weight": last_weight,
                "timestamp": time.time()
            }
            db.child("car_movement_log").push(log)
            print("Logged to Firebase:", log)

        # Detect stop
        elif "Car STOPPED." in line and car_moving:
            car_moving = False
            log = {
                "event": "Car STOPPED",
                "weight": last_weight,
                "timestamp": time.time()
            }
            db.child("car_movement_log").push(log)
            print("Logged to Firebase:", log)

    except Exception as e:
        print("Error:", e)
