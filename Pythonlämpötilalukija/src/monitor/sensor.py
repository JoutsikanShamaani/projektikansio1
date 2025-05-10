import random

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
            self.check_temp()
        else:
            print("Virheellinen lukema, lämpötilan on oltava väliltä " + str(MIN_TEMP) + " - " + str(MAX_TEMP) + ".")

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
            print("Varoitus! " + self.name + " lämpötila on liian korkea!")
        else:
            self.is_alert = False
            print(self.name + " lämpötila on normaali.")

    def show_status(self):
        print("\n--- Sensorin tila ---")
        print("Nimi:", self.name)
        print("Virta:", "Päällä" if self.is_on else "Kiinni")
        print("Lämpötila:", str(self.current_temp) + "°C")
        print("Hälytys:", "Kyllä" if self.is_alert else "Ei")
        print("---------------------")

# User menu
if __name__ == "__main__":
    sensor = Sensor("Sensori")

    while True:
        print("\nValitse toiminto:")
        print("1. Käynnistä sensori")
        print("2. Sammuta sensori")
        print("3. Aseta lämpötila")
        print("4. Mittaa lämpötila (satunnainen)")
        print("5. Näytä tila")
        print("6. Lopeta")

        choice = input("Valinta: ")

        if choice == "1":
            sensor.turn_on()
        elif choice == "2":
            sensor.turn_off()
        elif choice == "3":
            try:
                temp = int(input("Anna lämpötila: "))
                sensor.set_temp(temp)
            except ValueError:
                print("Virheellinen syöte. Anna numero.")
        elif choice == "4":
            sensor.read_temperature()
        elif choice == "5":
            sensor.show_status()
        elif choice == "6":
            print("Ohjelma suljetaan.")
            break
        else:
            print("Tuntematon valinta.")
