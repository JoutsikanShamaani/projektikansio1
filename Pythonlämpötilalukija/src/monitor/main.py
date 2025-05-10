from controller import Controller

def main():
    controller = Controller("Sensori")

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
            controller.turn_on()
        elif choice == "2":
            controller.turn_off()
        elif choice == "3":
            try:
                temp = int(input("Anna lämpötila: "))
                controller.set_temp(temp)
            except ValueError:
                print("Virheellinen syöte. Anna numero.")
        elif choice == "4":
            controller.read_temperature()
        elif choice == "5":
            controller.show_status()
        elif choice == "6":
            print("Ohjelma suljetaan.")
            break
        else:
            print("Tuntematon valinta.")

if __name__ == "__main__":
    main()
