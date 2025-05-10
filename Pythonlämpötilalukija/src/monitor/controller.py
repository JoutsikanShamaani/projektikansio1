from sensor import Sensor
from alarm import Alarm

class Controller:
    def __init__(self):
        self.sensor = Sensor("Lämpötila-anturi")
        self.alarm = Alarm()
        
    def turn_on_sensor(self):
        self.sensor.turn_on()
    
    def turn_off_sensor(self):
        self.sensor.turn_off()
        self.alarm.deactivate() # hälytys kiinni kun sensori menee kiinni
    
    def set_temperature(self, temp):
        self.sensor.set_temp(temp)
        self._handle_alarm()

    def read_temperature(self):
        self.sensor.read_temperature()
        self._handle_alarm()
    
    def show_status(self):
        self.sensor.show_status()
        print("Hälytys:", "Aktiivinen" if self.alarm.status() else "Ei aktiivinen")

    def _handle_alarm(self):
        if self.sensor.is_alert:
            self.alarm.activate()
        else:
            self.alarm.deactivate()
#


if __name__ == "__main__":
    controller = Controller("Sensori 1")
    controller.turn_on_sensor()
    controller.set_temperature(75)
    controller.read_temperature()
    controller.show_status()
    controller.turn_off_sensor()