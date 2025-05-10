import random
import time

# lämpötilan sallitut rajat ja hälytysraja
MIN_TEMP = 0
MAX_TEMP = 100
ALERT_TEMP = 70

class Sensor:
    def __init__(self, name):
        self.name = name
        self.current_temp = 0
        self.is_on = False
        self.is_alert = False

    def turn_on(self):
        self.is_on = True
        print(self.name + " on päällä.")

    def turn_off(self):
        self.is_on = False
        print(self.name + " on pois päältä.")

    def set_temp(self, temp):
        if MIN_TEMP <= temp <= MAX_TEMP:
            self.current_temp = temp
            print(self.name + " asetettu lämpötila: " + str(self.current_temp) + "°C.")
        else:
            print(" Virheellinen lukema, lämpötilan on oltava väliltä " + str(MIN_TEMP) + " - " + str(MAX_TEMP) + ".")

    def read_temperature(self):
        if self.is_on:
            self.current_temp = random.randint(MIN_TEMP, MAX_TEMP)
            print(self.name + " mitattu: " + str(self.current_temp) + "°C.")
            self.check_temp()
        else:
            print(self.name + " on pois päältä, kytke se ensin päälle.")

    def check_temp(self):
        if self.current_temp >= ALERT_TEMP:
            self.is_alert = True
            print("Varoitus! " + self.name + " lämpötila on liaan korkea: ")
        else:
            self.is_alert = False
            print(self.name + " lämpötila on normaali.")

    def show_status(self):
        print("--- Sensor Status ---")
        print("Nimi:", self.name)
        print("Virta:", "Päällä" if self.is_on else "Kiinni")
        print("Lämpötila:", str(self.current_temp) + "°C")
        print("Hälytys:", "Kyllä" if self.is_alert else "Ei")
        print("----------------------")

# Testikoodi
if __name__ == "__main__":
    sensor = Sensor("Test Sensor")
    sensor.turn_on()
    sensor.set_temp(90)
    sensor.check_temp()
    sensor.read_temperature()
    sensor.show_status()
    sensor.turn_off()
