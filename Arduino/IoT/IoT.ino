#include <Wire.h>
#include "Adafruit_TCS34725.h"
#include "HX711.h"

// ----- Color Sensor Setup -----
Adafruit_TCS34725 tcs = Adafruit_TCS34725(
  TCS34725_INTEGRATIONTIME_50MS,
  TCS34725_GAIN_4X
);

// ----- Load Cell (HX711) Setup -----
#define DT 9
#define SCK 10
float calibration_factor = 200;
HX711 scale(DT, SCK);
float weight_grams = 0;

// ----- Motor Control Pins -----
int motor1pin1 = 2;
int motor1pin2 = 3;
int motor2pin1 = 4;
int motor2pin2 = 5;

// ----- Variables -----
uint16_t baselineRed = 0;
bool initialized = false;
bool isMoving = false;

void setup() {
  Serial.begin(9600);

  // Motor pin setup
  pinMode(motor1pin1, OUTPUT);
  pinMode(motor1pin2, OUTPUT);
  pinMode(motor2pin1, OUTPUT);
  pinMode(motor2pin2, OUTPUT);

  // Initialize color sensor
  if (tcs.begin()) {
    Serial.println("TCS34725 found!");
  } else {
    Serial.println("No TCS34725 found ... check wiring.");
    while (1); // Stop here if sensor not found
  }

  delay(1000);
  uint16_t r, g, b, c;
  tcs.getRawData(&r, &g, &b, &c);
  baselineRed = r;
  initialized = true;
  Serial.print("Baseline Red: ");
  Serial.println(baselineRed);

  // Initialize HX711
  scale.set_scale();
  scale.tare();
  long zero_factor = scale.read_average();
  Serial.print("Zero factor: ");
  Serial.println(zero_factor);
}


void loop() {
  // ---- Read weight from load cell ----
  scale.set_scale(calibration_factor);
  weight_grams = scale.get_units();
  if (weight_grams < 0) weight_grams = 0;

  Serial.print("Weight: ");
  Serial.print(weight_grams);
  Serial.println(" grams");

  // ---- React only if weight > 5 grams ----
  if (weight_grams > 40) {
    if (!initialized) return;

    uint16_t r, g, b, c;
    tcs.getRawData(&r, &g, &b, &c);

    Serial.print("R: "); Serial.print(r);
    Serial.print(" G: "); Serial.print(g);
    Serial.print(" B: "); Serial.println(b);

    int diff = abs(baselineRed - r);
    Serial.println(baselineRed);
    Serial.println(r);

    // Log when car starts moving
    if (!isMoving) {
      Serial.println("Car STARTED moving.");
      isMoving = true;
    }

    if (diff >= 4) {
      goLeft();
    } else if (diff <= -4 || abs(diff) < 4) {
      goRight();
    }

  } else {
    if (isMoving) {
      Serial.println("Car STOPPED.");
      isMoving = false;
    }
    stopMotors();
  }

  // Allow live calibration from serial monitor
  if (Serial.available()) {
    char temp = Serial.read();
    if (temp == '+' || temp == 'a')
      calibration_factor += 1;
    else if (temp == '-' || temp == 'z')
      calibration_factor -= 1;
  }

  delay(1);
}

// ----- Motor Control Functions -----
void goLeft() {
  digitalWrite(motor1pin1, LOW);
  digitalWrite(motor1pin2, HIGH);
  digitalWrite(motor2pin1, LOW);
  digitalWrite(motor2pin2, LOW);
  Serial.println("GO LEFT");
}

void goRight() {
  digitalWrite(motor1pin1, LOW);
  digitalWrite(motor1pin2, LOW);
  digitalWrite(motor2pin1, LOW);
  digitalWrite(motor2pin2, HIGH);
  Serial.println("GO RIGHT");
}

void stopMotors() {
  digitalWrite(motor1pin1, LOW);
  digitalWrite(motor1pin2, LOW);
  digitalWrite(motor2pin1, LOW);
  digitalWrite(motor2pin2, LOW);
  Serial.println("STOP (Weight too low)");
}
