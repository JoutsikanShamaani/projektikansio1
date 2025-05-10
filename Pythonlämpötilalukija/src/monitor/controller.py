from sensor import Sensor
from logger import log

class Controller:
    def __init__(self, name):
        self.sensor = Sensor(name)

    def turn_on(self):
        self.sensor.turn_on()
        log("Sensori käynnistetty")

    def turn_off(self):
        self.sensor.turn_off()
        log("Sensori sammutettu")

    def set_temp(self, temp):
        self.sensor.set_temp(temp)
        log(f"Lämpötila asetettu {temp}°C")

    def read_temperature(self):
        self.sensor.read_temperature()
        log(f"Lämpötila mitattu: {self.sensor.current_temp}°C")

    def show_status(self):
        self.sensor.show_status()
