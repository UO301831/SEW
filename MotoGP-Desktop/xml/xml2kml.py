# Importación del módulo xml.etree.ElementTree para trabajar con archivos XML.
# ET permite parsear y navegar por el árbol DOM del XML usando XPath.
import xml.etree.ElementTree as ET

# Definición de la función dms_to_decimal.
# Esta función convierte coordenadas geográficas de formato DMS (grados, minutos, segundos con dirección) 
# o decimal a grados decimales. Para formato decimal, devuelve el string tal cual para evitar redondeo 
# y mantener la precisión exacta requerida por el XSD.
def dms_to_decimal(coord_str):
    """
    Convierte una coordenada en formato DMS (ej. "14°45'54E") o decimal (ej. "14.765") a grados decimales.
    Para formato decimal, devuelve el string tal cual para evitar redondeo.
    """
    # Elimina espacios en blanco al inicio y final de la cadena para limpieza.
    coord_str = coord_str.strip()
    
    # Verifica si la coordenada está en formato DMS (contiene el símbolo de grado '°').
    if '°' in coord_str:
        # Procesa el formato DMS.
        # Reemplaza símbolos para normalizar: '°' por "'", y maneja posibles duplicados.
        parts = coord_str.replace('°', "'").replace("'", "'").replace("''", "'").split("'")
        # Extrae grados como entero.
        degrees = int(parts[0])
        # Extrae minutos como entero.
        minutes = int(parts[1])
        # Extrae segundos como entero, quitando la letra de dirección (ej. 'E', 'N').
        seconds = int(parts[2][:-1])  # Quitar la letra de dirección
        # Extrae la dirección (N, S, E, W) y la convierte a mayúscula.
        direction = parts[2][-1].upper()
        
        # Calcula el valor decimal: grados + minutos/60 + segundos/3600.
        decimal = degrees + minutes / 60 + seconds / 3600
        
        # Aplica el signo negativo si la dirección es Sur (S) o Oeste (W).
        if direction in ['S', 'W']:
            decimal = -decimal
        
        # Devuelve el resultado como string para consistencia y evitar redondeo.
        return str(decimal)  # Devolver como string para consistencia
    else:
        # Si no es formato DMS, asume formato decimal y devuelve tal cual (como string).
        # Esto evita redondeo y mantiene la precisión del XSD (xs:double o xs:string).
        return coord_str

# Definición de la función principal xml_to_kml.
# Esta función lee un archivo XML de circuito, extrae coordenadas usando XPath,
# y genera un archivo KML que representa el trazado del circuito.
def xml_to_kml(xml_file, kml_file):
    # Registra el namespace del XML para usar en expresiones XPath.
    # El namespace es 'http://www.uniovi.es', prefijado como 'circuito'.
    ns = {'circuito': 'http://www.uniovi.es'}
    
    # Parsea el archivo XML y construye el árbol DOM en memoria.
    tree = ET.parse(xml_file)
    # Obtiene la raíz del árbol XML.
    root = tree.getroot()
    
    # Extrae el nombre del circuito usando XPath.
    # Busca el elemento <nombre> en cualquier lugar del árbol.
    nombre_elem = root.find('.//circuito:nombre', ns)
    # Asigna el texto del elemento o un valor por defecto si no existe.
    nombre_circuito = nombre_elem.text if nombre_elem is not None else 'Circuito'
    
    # Extrae el elemento <origen> usando XPath.
    origen = root.find('.//circuito:origen', ns)
    if origen is not None:
        # Convierte longitud y latitud a formato decimal (o mantiene decimal).
        lon_origen = dms_to_decimal(origen.get('longitud'))
        lat_origen = dms_to_decimal(origen.get('latitud'))
        # Altitud se mantiene como string (xs:double en XSD).
        alt_origen = origen.get('altitud') 
    else:
        # Lanza error si no se encuentra el origen, ya que es obligatorio.
        raise ValueError("No se encontró el elemento <origen>")
    
    # Extrae todos los elementos <fin> dentro de <tramo> usando XPath.
    # Esto obtiene los puntos finales de cada tramo.
    tramos = root.findall('.//circuito:tramo/circuito:fin', ns)
    # Inicializa la lista de coordenadas con el punto de origen.
    coordenadas = [(lon_origen, lat_origen, alt_origen)]  
    
    # Itera sobre cada punto final de tramo.
    for fin in tramos:
        # Convierte longitud y latitud.
        lon = dms_to_decimal(fin.get('longitud')) 
        lat = dms_to_decimal(fin.get('latitud')) 
        # Altitud como string.
        alt = fin.get('altitud') 
        # Agrega la coordenada a la lista.
        coordenadas.append((lon, lat, alt))
    
    # Abre el archivo KML para escritura en modo texto con codificación UTF-8.
    with open(kml_file, 'w', encoding='utf-8') as f:
        # Escribe el prólogo XML.
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        # Escribe el inicio del elemento KML con namespace.
        f.write('<kml xmlns="http://www.opengis.net/kml/2.2">\n')
        # Escribe el elemento Document con el nombre del circuito.
        f.write('<Document>\n')
        f.write(f'<name>{nombre_circuito}</name>\n')
        # Escribe el Placemark con el nombre.
        f.write('<Placemark>\n')
        f.write(f'<name>{nombre_circuito}</name>\n')
        # Agrega un estilo para la línea: color rojo y grosor 2 
        f.write('<Style>\n')
        f.write('<LineStyle>\n')
        f.write('<color>ff0000ff</color>\n')  # Color rojo 
        f.write('<width>3</width>\n')  # Grosor de la línea.
        f.write('</LineStyle>\n')
        f.write('</Style>\n')
        # Escribe el LineString para definir la línea.
        f.write('<LineString>\n')
        f.write('<coordinates>\n')
        
        # Escribe cada coordenada en formato lon,lat,alt.
        for lon, lat, alt in coordenadas:
            f.write(f'{lon},{lat},{alt}\n')
        
        # Cierra los elementos del KML.
        f.write('</coordinates>\n')
        f.write('</LineString>\n')
        f.write('</Placemark>\n')
        f.write('</Document>\n')
        f.write('</kml>\n')

if __name__ == "__main__":
    xml_to_kml('circuitoEsquema.xml', 'circuito.kml')
