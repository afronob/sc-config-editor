import xml.etree.ElementTree as ET
import csv
import os
from datetime import datetime
import sys

# Get XML filename
if len(sys.argv) > 1:
    xml_filename = sys.argv[1]
else:
    print("Usage: python extractor.py <fichier_xml>")
    sys.exit(1)

# Parse XML
tree = ET.parse(xml_filename)
root = tree.getroot()

# Prepare CSV output filename
basename = os.path.splitext(xml_filename)[0]
date_str = datetime.now().strftime('%Y%m%d_%H%M%S')
csv_filename = f"{basename}_{date_str}.csv"

# Prepare CSV header
header = ['category', 'action', 'input', 'opts', 'value']
rows = []

# Extract data from XML
for actionmap in root.findall('actionmap'):
    category = actionmap.attrib.get('name', '')
    for action in actionmap.findall('action'):
        action_name = action.attrib.get('name', '')
        for rebind in action.findall('rebind'):
            input_val = rebind.attrib.get('input', '')
            # Séparer la première option (clé/valeur) si elle existe
            opts = []
            value = ''
            for k, v in rebind.attrib.items():
                if k != 'input':
                    opts.append(k)
                    value = v
                    break  # On ne prend que la première option, comme dans l'exemple
            opts_str = opts[0] if opts else ''
            rows.append([category, action_name, input_val, opts_str, value])
        # If no rebind, still output empty input/opts/value
        if not action.findall('rebind'):
            rows.append([category, action_name, '', '', ''])

# Write to CSV
with open(csv_filename, 'w', newline='', encoding='utf-8') as csvfile:
    writer = csv.writer(csvfile, delimiter=';')
    writer.writerow(header)
    writer.writerows(rows)

print(f'Extraction terminée : {csv_filename}')
