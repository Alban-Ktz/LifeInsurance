import serial
import requests
import serial.tools.list_ports
import time

# def log(msg):
#     with open("log.txt", "a") as f:
#         f.write(f"[{time.strftime('%H:%M:%S')}] {msg}\n")

print("Démarrage... Attente de 30 secondes pour laisser le temps à Windows d'initialiser l'Arduino.")
# log("Script démarré. Attente initiale de 30s.")
time.sleep(30)

def find_arduino_port():
    ports = serial.tools.list_ports.comports()
    for port in ports:
        if "Arduino" in port.description or "CH340" in port.description or "USB-SERIAL" in port.description:
            return port.device
    if ports:
        return ports[0].device
    return None

while True:
    arduino_port = None
    print("Recherche d'un port Arduino...")
    # log("Recherche d'un port Arduino...")
    while not arduino_port:
        arduino_port = find_arduino_port()
        if not arduino_port:
            print("Aucun port détecté. Veuillez brancher l'Arduino...")
            # log("Aucun port détecté. Nouvelle tentative dans 1s...")
            time.sleep(1)

    try:
        arduino = serial.Serial(arduino_port, 9600)
        # log(f"Port détecté : {arduino_port}")
        
        # Reset DTR pour forcer une réinitialisation
        arduino.setDTR(False)
        time.sleep(1)
        arduino.setDTR(True)
        time.sleep(2)
        # log("DTR reset effectué. Attente pour initialisation de l'Arduino.")

        # Purger les premières lignes parasites
        for _ in range(3):
            arduino.readline()
        # log("Lignes initiales purgées.")

        print(f"Connexion à l'Arduino établie sur {arduino_port}. En attente de données...")
        # log(f"Connexion série établie sur {arduino_port}.")

        while True:
            try:
                value = arduino.readline().decode().strip()
                if "URGENCE" in value:
                    value = 1
                else:
                    value = 0
                requests.post("http://life-insurance.net/update-button", data={"value": value})
                print(f"Envoyé : {value}")
                # log(f"Envoyé : {value}")
            except Exception as e:
                print("Erreur de lecture série :", e)
                # log(f"Erreur de lecture série : {e}")
                break
        arduino.close()
        # log("Connexion série fermée.")
    except Exception as e:
        print("Erreur de connexion série :", e)
        # log(f"Erreur de connexion série : {e}")
        time.sleep(2)
