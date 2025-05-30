import xml.etree.ElementTree as ET
import csv
import os
from datetime import datetime
import sys

# Vérification des arguments
if len(sys.argv) < 3:
    print("Usage: python importer.py <fichier_csv> <fichier_xml>")
    sys.exit(1)

csv_filename = sys.argv[1]
xml_filename = sys.argv[2]

# Créer une copie du XML d'origine avec horodatage pour modification
basename = os.path.splitext(xml_filename)[0]
date_str = datetime.now().strftime('%Y%m%d_%H%M%S')
xml_copy = f"{basename}_import_{date_str}.xml"
with open(xml_filename, 'rb') as src, open(xml_copy, 'wb') as dst:
    dst.write(src.read())

# Charger le XML à modifier (la copie)
try:
    tree = ET.parse(xml_copy)
    root = tree.getroot()
except Exception as e:
    print(f"Erreur lors de la lecture du XML : {e}")
    sys.exit(1)

# Charger le CSV
with open(csv_filename, newline='', encoding='utf-8') as csvfile:
    reader = csv.DictReader(csvfile, delimiter=';')
    csv_rows = list(reader)

# Indexation des données du CSV pour accès rapide
csv_map = {}
for row in csv_rows:
    key = (row['category'], row['action'])
    if key not in csv_map:
        csv_map[key] = []
    csv_map[key].append(row)

# Mise à jour du XML
for actionmap in root.findall('actionmap'):
    category = actionmap.attrib.get('name', '')
    for action in actionmap.findall('action'):
        action_name = action.attrib.get('name', '')
        key = (category, action_name)
        # Supprimer les anciens rebinds
        for rebind in list(action.findall('rebind')):
            action.remove(rebind)
        # Ajouter les nouveaux rebinds depuis le CSV
        if key in csv_map:
            for row in csv_map[key]:
                attribs = {}
                if row['input']:
                    attribs['input'] = row['input']
                if row['opts']:
                    attribs[row['opts']] = row['value']
                ET.SubElement(action, 'rebind', attrib=attribs)

# Sauvegarder le XML modifié (dans la copie)
with open(xml_copy, 'wb') as f:
    tree.write(f, encoding='utf-8', xml_declaration=True)

print(f'Import terminé. Nouveau XML généré : {xml_copy}')
