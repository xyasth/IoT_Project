
# IoT Items Transporting Robot

## Project Overview
This project is a smart robotic car system designed to transport items based on weight detection and line-following navigation. The system integrates a load cell (weight sensor) to detect when an item is placed on the car. Upon detecting an item, the car begins to move along a predefined path marked by lines, which are followed using a color sensor.

The car continues to move until it either reaches the end of the line or the item is removed. During operation, the system logs the following events:
- The time the car starts moving
- The weight of the item being transported

Data is sent to a Firebase Realtime Database via a Python script that reads serial data from the Arduino. A Laravel web interface is used to display the real-time logs and status of the system.

## Project Structure

```
iot-project/
├── arduino/     # Contains Arduino sketch code uploaded to the Arduino Uno
├── python/      # Python script that reads serial output and pushes data to Firebase
├── laravel/     # Laravel application to retrieve and display data from Firebase
```

### Arduino
Handles communication with hardware components and prints sensor data to the serial monitor.

### Python
Reads the serial output from the Arduino Uno and sends it to Firebase Realtime Database along with logs of each transmission event.

### Laravel
Fetches data from Firebase and displays it on a web dashboard for monitoring purposes.

## Team Members
- Steven Gonawan - 0706012210042
- Jesslyn Levana Halim - 0706012210017
- Prayogo Kosasih W. - 0706012210027
- Owen Orlando - 0706012210057

## Database
All data and logs are stored in **Firebase Realtime Database** and accessed through the Laravel interface.

