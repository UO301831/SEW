import xml.etree.ElementTree as ET

class Html:
    def __init__(self):
        self.content = []
        self.indent_level = 0

    def _indent(self):
        return '  ' * self.indent_level

    def open_tag(self, tag, attributes=None, self_closing=False):
        attr_str = ''
        if attributes:
            attr_str = ' ' + ' '.join(f'{k}="{v}"' for k, v in attributes.items() if k != 'id')
        
        if self_closing:
            self.content.append(f'{self._indent()}<{tag}{attr_str} />')
        else:
            self.content.append(f'{self._indent()}<{tag}{attr_str}>')
            self.indent_level += 1

    def close_tag(self, tag):
        self.indent_level -= 1
        self.content.append(f'{self._indent()}</{tag}>')

    def add_text(self, text):
        if text: # Solo añadir si hay texto
            self.content.append(f'{self._indent()}{text}')

    def add_comment(self, comment):
        self.content.append(f'{self._indent()}')

    def get_html(self):
        return '\n'.join(self.content)

def toStr(duracion):
    """
    Convierte una duración en formato ISO 8601 (ej. PT42M11.006S) a un formato legible.
    """
    if not duracion or not duracion.startswith('PT'):
        return duracion 
    
    duracion = duracion[2:] 
    minutos = 0
    segundos = 0.0
    
    if 'M' in duracion:
        partes = duracion.split('M')
        minutos = int(partes[0])
        restante = partes[1]
        if restante.endswith('S'):
            segundos = float(restante[:-1])
    elif duracion.endswith('S'):
        segundos = float(duracion[:-1])
    
    return f"{minutos}:{segundos}"


def xml_to_html(xml_file, html_file):
    ns = {'circuito': 'http://www.uniovi.es'}
    
    try:
        tree = ET.parse(xml_file)
        root = tree.getroot()
    except FileNotFoundError:
        print(f"Error: No se encuentra el archivo {xml_file}")
        return

    html = Html()
    
    html.add_text('<!DOCTYPE HTML>')
    html.open_tag('html', {'lang': 'es'})
    
    # --- HEAD ---
    html.open_tag('head')
    html.add_comment('Datos que describen el documento')
    html.open_tag('meta', {'charset': 'UTF-8'}, self_closing=True)
    html.open_tag('title')
    html.add_text('MotoGP - Circuito')
    html.close_tag('title')
    
    html.open_tag('meta', {'name': 'author', 'content': 'Alejandro Requena'}, self_closing=True)
    html.open_tag('meta', {'name': 'description', 'content': 'Información relevante sobre los circuitos de MotoGP'}, self_closing=True)
    html.open_tag('meta', {'name': 'keywords', 'content': 'motoGP, circuito, motos'}, self_closing=True)
    html.open_tag('meta', {'name': 'viewport', 'content': 'width=device-width, initial-scale=1.0'}, self_closing=True)
    
    # CSS
    html.open_tag('link', {'rel': 'stylesheet', 'type': 'text/css', 'href': '../estilo/estilo.css'}, self_closing=True)
    html.open_tag('link', {'rel': 'stylesheet', 'type': 'text/css', 'href': '../estilo/layout.css'}, self_closing=True)
    html.open_tag('link', {'rel': 'icon', 'href': '../multimedia/iconoMotoGP.ico'}, self_closing=True)
    html.close_tag('head')
    
    # --- BODY ---
    html.open_tag('body')
    
    # HEADER
    html.open_tag('header')
    html.open_tag('h1')
    html.open_tag('a', {'href': 'index.html'})
    html.add_text('MotoGP Desktop')
    html.close_tag('a')
    html.close_tag('h1')
    
    html.close_tag('header')
    
    html.open_tag('main')
    
    # 1. INFORMACIÓN BÁSICA
    html.open_tag('section') # Sin ID
    html.open_tag('h2')
    html.add_text('Información Básica')
    html.close_tag('h2')
    html.open_tag('ul')
    
    # Mapeo de etiquetas simples
    tags_info = [
        ('longitud', 'Longitud', 'medida'),
        ('anchura', 'Anchura', 'medida'),
        ('fecha', 'Fecha', None),
        ('hora', 'Hora', None),
        ('vueltas', 'Vueltas', None),
        ('localidad_proxima', 'Localidad Próxima', None),
        ('pais', 'País', None),
        ('patrocinador_principal', 'Patrocinador Principal', None)
    ]

    for tag_xml, label, attr_extra in tags_info:
        node = root.find(f'.//circuito:{tag_xml}', ns)
        if node is not None:
            html.open_tag('li')
            text = f'{label}: {node.text}'
            if attr_extra:
                text += f' {node.get(attr_extra)}'
            html.add_text(text)
            html.close_tag('li')
    
    html.close_tag('ul')
    html.close_tag('section')
    
    # 2. BIBLIOGRAFÍA
    bibliografia = root.find('.//circuito:bibliografia', ns)
    if bibliografia is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Bibliografía')
        html.close_tag('h2')
        html.open_tag('ul')
        referencias = bibliografia.findall('.//circuito:referencia', ns)
        for ref in referencias:
            html.open_tag('li')
            html.open_tag('a', {'href': ref.get('enlace'), 'target': '_blank'})
            html.add_text(ref.get('nombre'))
            html.close_tag('a')
            html.close_tag('li')
        html.close_tag('ul')
        html.close_tag('section')
    
    # 3. FOTOGRAFÍA
    fotografia = root.find('.//circuito:fotografia', ns)
    if fotografia is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Fotografía')
        html.close_tag('h2')
        
        html.open_tag('section') 
        
        imagenes = fotografia.findall('.//circuito:imagen', ns)
        for img in imagenes:
            html.open_tag('img', {'src': 'multimedia/' + img.get('nombre')+".jpg", 'alt': img.get('nombre')}, self_closing=True)
        
        html.close_tag('section') 
        html.close_tag('section') 
    
    # 4. MULTIMEDIA
    multimedia = root.find('.//circuito:multimedia', ns)
    if multimedia is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Multimedia')
        html.close_tag('h2')
        videos = multimedia.findall('.//circuito:video', ns)
        for video in videos:
            html.open_tag('video', {'controls': 'controls', 'src': 'multimedia/' + video.get('nombre').lower()+".mp4"})
            html.add_text('Tu navegador no soporta el elemento video.')
            html.close_tag('video')
        html.close_tag('section')
    
    # 5. VENCEDOR
    vencedor = root.find('.//circuito:vencedor', ns)
    if vencedor is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Vencedor')
        html.close_tag('h2')
        html.open_tag('p')
        html.add_text(f'Nombre: {vencedor.get("nombre")}, Tiempo: {toStr(vencedor.get("tiempo"))}')
        html.close_tag('p')
        html.close_tag('section')
    
    # 6. CLASIFICACIÓN
    clasificacion = root.find('.//circuito:clasificacion', ns)
    if clasificacion is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Clasificación')
        html.close_tag('h2')
        html.open_tag('table')
        
        # Thead
        html.open_tag('thead')
        html.open_tag('tr')
        headers = ['Puesto', 'Nombre', 'Puntos']
        for h in headers:
            html.open_tag('th')
            html.add_text(h)
            html.close_tag('th')
        html.close_tag('tr')
        html.close_tag('thead')
        
        # Tbody
        html.open_tag('tbody')
        pilotos = clasificacion.findall('.//circuito:piloto', ns)
        for piloto in pilotos:
            html.open_tag('tr')
            html.open_tag('td')
            html.add_text(piloto.get('puesto'))
            html.close_tag('td')
            html.open_tag('td')
            html.add_text(piloto.get('nombre'))
            html.close_tag('td')
            html.open_tag('td')
            html.add_text(piloto.get('puntos'))
            html.close_tag('td')
            html.close_tag('tr')
        html.close_tag('tbody')
        html.close_tag('table')
        html.close_tag('section')
    
    html.close_tag('main')
    html.close_tag('body')
    html.close_tag('html')
    
    try:
        with open(html_file, 'w', encoding='utf-8') as f:
            f.write(html.get_html())
        print(f"Archivo {html_file} generado correctamente.")
    except Exception as e:
        print(f"Error escribiendo el archivo: {e}")

if __name__ == "__main__":
    xml_to_html('circuitoEsquema.xml', 'infoCircuito.html')