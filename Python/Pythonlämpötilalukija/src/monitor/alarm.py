# alarm.py
class Alarm:
    def __init__(self):
        self.is_active = False

    def activate(self):
        self.is_active = True
        print("🚨 Hälytys aktivoitu!")

    def deactivate(self):
        self.is_active = False
        print("Hälytys poistettu.")

    def status(self):
        return self.is_active
