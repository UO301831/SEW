import xml.etree.ElementTree as ET

def dms_to_decimal(coord_str):
    """
    Convierte una coordenada en formato DMS (ej. "14°45'54E") o decimal (ej. "14.765") a grados decimales.
    """
    coord_str = coord_str.strip()

    if '°' in coord_str:
        # Formato DMS
        parts = coord_str.replace('°', "'").replace("'", "'").replace("''", "'").split("'")
        degrees = int(parts[0])
        minutes = int(parts[1])
        seconds = int(parts[2][:-1])  # Quitar la letra de dirección
        direction = parts[2][-1].upper()

        # Calcular decimal
        decimal = degrees + minutes / 60 + seconds / 3600

        # Aplicar signo según dirección
        if direction in ['S', 'W']:
            decimal = -decimal

        return decimal
    else:
        # Formato decimal
        return float(coord_str)

def xml_to_kml(xml_file, kml_file):
    # Registrar el namespace para XPath
    ns = {'circuito': 'http://www.uniovi.es'}

    # Leer el archivo XML y construir el árbol DOM
    tree = ET.parse(xml_file)
    root = tree.getroot()

    # Extraer el nombre del circuito usando XPath
    nombre_elem = root.find('.//circuito:nombre', ns)
    nombre_circuito = nombre_elem.text if nombre_elem is not None else 'Circuito'

    # Extraer el origen usando XPath
    origen = root.find('.//circuito:origen', ns)
    if origen is not None:
        lon_origen = dms_to_decimal(origen.get('longitud'))
        lat_origen = dms_to_decimal(origen.get('latitud'))
        alt_origen = float(origen.get('altitud'))
    else:
        raise ValueError("No se encontró el elemento <origen>")

    # Extraer los puntos finales de los tramos usando XPath
    tramos = root.findall('.//circuito:tramo/circuito:fin', ns)
    coordenadas = [(lon_origen, lat_origen, alt_origen)]  # Iniciar con el origen

    for fin in tramos:
        lon = dms_to_decimal(fin.get('longitud'))
        lat = dms_to_decimal(fin.get('latitud'))
        alt = float(fin.get('altitud'))
        coordenadas.append((lon, lat, alt))

    # Generar el archivo KML usando plantillas
    with open(kml_file, 'w', encoding='utf-8') as f:
        # Prólogo y encabezado
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<kml xmlns="http://www.opengis.net/kml/2.2">\n')
        f.write('<Document>\n')
        f.write(f'<name>{nombre_circuito}</name>\n')
        f.write('<Placemark>\n')
        f.write(f'<name>{nombre_circuito}</name>\n')
        f.write('<LineString>\n')
        f.write('<coordinates>\n')

        # Escribir las coordenadas extraídas (lon,lat,alt)
        for lon, lat, alt in coordenadas:
            f.write(f'{lon},{lat},{alt}\n')

        # Epílogo
        f.write('</coordinates>\n')
        f.write('</LineString>\n')
        f.write('</Placemark>\n')
        f.write('</Document>\n')
        f.write('</kml>\n')

if __name__ == "__main__":
    xml_to_kml('circuitoEsquema.xml', 'circuito.kml')
