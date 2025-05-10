import random

# Lämpötilan sallitut rajat ja hälytysraja
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
        print(f"{self.name} on päällä.")

    def turn_off(self):
        self.is_on = False
        print(f"{self.name} on pois päältä.")

    def set_temp(self, temp):
        if MIN_TEMP <= temp <= MAX_TEMP:
            self.current_temp = temp
            print(f"{self.name} asetettu lämpötila: {self.current_temp}°C.")
            self.check_temp()
        else:
            print(f"Virheellinen lukema, lämpötilan on oltava väliltä {MIN_TEMP} - {MAX_TEMP}.")

    def read_temperature(self):
        if self.is_on:
            self.current_temp = random.randint(MIN_TEMP, MAX_TEMP)
            print(f"{self.name} mitattu: {self.current_temp}°C.")
            self.check_temp()
        else:
            print(f"{self.name} on pois päältä, kytke se ensin päälle.")

    def check_temp(self):
        if self.current_temp >= ALERT_TEMP:
            self.is_alert = True
            print(f"Varoitus! {self.name} lämpötila on liian korkea!")
        else:
            self.is_alert = False
            print(f"{self.name} lämpötila on normaali.")

    def show_status(self):
        print("\n--- Sensorin tila ---")
        print("Nimi:", self.name)
        print("Virta:", "Päällä" if self.is_on else "Kiinni")
        print("Lämpötila:", f"{self.current_temp}°C")
        print("Hälytys:", "Kyllä" if self.is_alert else "Ei")
        print("---------------------")
