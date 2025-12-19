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
            attr_str = ' ' + ' '.join(f'{k}="{v}"' for k, v in attributes.items())
        if self_closing:
            self.content.append(f'{self._indent()}<{tag}{attr_str} />')
        else:
            self.content.append(f'{self._indent()}<{tag}{attr_str}>')
            self.indent_level += 1

    def close_tag(self, tag):
        self.indent_level -= 1
        self.content.append(f'{self._indent()}</{tag}>')

    def add_text(self, text):
        self.content.append(f'{self._indent()}{text}')

    def add_comment(self, comment):
        self.content.append(f'{self._indent()}<!-- {comment} -->')

    def get_html(self):
        return '\n'.join(self.content)

def toStr(duracion):
    """
    Convierte una duración en formato ISO 8601 (ej. PT42M11.006S) a un formato legible (ej. 42 minutos 11.006 segundos).
    """
    if not duracion.startswith('PT'):
        return duracion  # Si no es formato ISO, devolver tal cual
    
    duracion = duracion[2:]  # Quitar 'PT'
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
    # Registrar el namespace para XPath
    ns = {'circuito': 'http://www.uniovi.es'}
    
    # Leer el archivo XML y construir el árbol DOM
    tree = ET.parse(xml_file)
    root = tree.getroot()
    
    # Crear instancia de la clase Html
    html = Html()
    
    # Agregar DOCTYPE
    html.add_text('<!DOCTYPE HTML>')
    
    # Generar la estructura HTML
    html.open_tag('html', {'lang': 'es'})
    html.open_tag('head')
    # Comentarios y metadatos del ejemplo
    html.add_comment('Datos que describen el documento')
    html.open_tag('meta', {'charset': 'UTF-8'}, self_closing=True)
    html.open_tag('title')
    html.add_text('MotoGP - Circuito')
    html.close_tag('title')
    
    html.open_tag('meta', {'name': 'author', 'content': 'Alejandro Requena'}, self_closing=True)
    html.open_tag('meta', {'name': 'description', 'content': 'Información relevante sobre los circuitos de MotoGP'}, self_closing=True)
    html.open_tag('meta', {'name': 'keywords', 'content': ''}, self_closing=True)
    html.open_tag('meta', {'name': 'viewport', 'content': 'width=device-width, initial-scale=1.0'}, self_closing=True)
    
    # Enlazar a ambos CSS con rutas relativas correctas
    html.open_tag('link', {'rel': 'stylesheet', 'type': 'text/css', 'href': 'estilo/estilo.css'}, self_closing=True)
    html.open_tag('link', {'rel': 'stylesheet', 'type': 'text/css', 'href': 'estilo/layout.css'}, self_closing=True)
    html.open_tag('link', {'rel': 'icon', 'href': 'multimedia/iconoMotoGP.ico'}, self_closing=True)
    html.close_tag('head')
    
    html.open_tag('body')
    html.open_tag('header')
    html.add_comment('Datos con el contenidos que aparece en el navegador')
    html.open_tag('h1')
    html.open_tag('a', {'href': 'index.html'})
    html.add_text('MotoGP Desktop')
    html.close_tag('a')
    html.close_tag('h1')
    html.close_tag('header')
    
    html.open_tag('main')
    
    # Información básica
    html.open_tag('section')
    html.open_tag('h2')
    html.add_text('Información Básica')
    html.close_tag('h2')
    html.open_tag('ul')
    
    longitud = root.find('.//circuito:longitud', ns)
    if longitud is not None:
        html.open_tag('li')
        html.add_text(f'Longitud: {longitud.text} {longitud.get("medida")}')
        html.close_tag('li')
    
    anchura = root.find('.//circuito:anchura', ns)
    if anchura is not None:
        html.open_tag('li')
        html.add_text(f'Anchura: {anchura.text} {anchura.get("medida")}')
        html.close_tag('li')
    
    fecha = root.find('.//circuito:fecha', ns)
    if fecha is not None:
        html.open_tag('li')
        html.add_text(f'Fecha: {fecha.text}')
        html.close_tag('li')
    
    hora = root.find('.//circuito:hora', ns)
    if hora is not None:
        html.open_tag('li')
        html.add_text(f'Hora: {hora.text}')
        html.close_tag('li')
    
    vueltas = root.find('.//circuito:vueltas', ns)
    if vueltas is not None:
        html.open_tag('li')
        html.add_text(f'Vueltas: {vueltas.text}')
        html.close_tag('li')
    
    localidad = root.find('.//circuito:localidad_proxima', ns)
    if localidad is not None:
        html.open_tag('li')
        html.add_text(f'Localidad Próxima: {localidad.text}')
        html.close_tag('li')
    
    pais = root.find('.//circuito:pais', ns)
    if pais is not None:
        html.open_tag('li')
        html.add_text(f'País: {pais.text}')
        html.close_tag('li')
    
    patrocinador = root.find('.//circuito:patrocinador_principal', ns)
    if patrocinador is not None:
        html.open_tag('li')
        html.add_text(f'Patrocinador Principal: {patrocinador.text}')
        html.close_tag('li')
    
    html.close_tag('ul')
    html.close_tag('section')
    
    # Bibliografía
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
    
    # Fotografía
    fotografia = root.find('.//circuito:fotografia', ns)
    if fotografia is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Fotografía')
        html.close_tag('h2')
        html.open_tag('section')
        imagenes = fotografia.findall('.//circuito:imagen', ns)
        for img in imagenes:
            html.open_tag('img', {'src': img.get('src'), 'alt': img.get('nombre')}, self_closing=True)
        html.close_tag('section')
        html.close_tag('section')
    
    # Multimedia
    multimedia = root.find('.//circuito:multimedia', ns)
    if multimedia is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Multimedia')
        html.close_tag('h2')
        videos = multimedia.findall('.//circuito:video', ns)
        for video in videos:
            html.open_tag('video', {'controls': 'controls', 'src': video.get('src'), 'aria-label': video.get('nombre')})
            html.add_text('Tu navegador no soporta el elemento video.')
            html.close_tag('video')
        html.close_tag('section')
    
    # Vencedor
    vencedor = root.find('.//circuito:vencedor', ns)
    if vencedor is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Vencedor')
        html.close_tag('h2')
        html.open_tag('p')
        html.add_text(f'Nombre: {vencedor.get("nombre")}, Tiempo:{toStr(vencedor.get("tiempo"))}')
        html.close_tag('p')
        html.close_tag('section')
    
    # Clasificación
    clasificacion = root.find('.//circuito:clasificacion', ns)
    if clasificacion is not None:
        html.open_tag('section')
        html.open_tag('h2')
        html.add_text('Clasificación')
        html.close_tag('h2')
        html.open_tag('table')
        html.open_tag('thead')
        html.open_tag('tr')
        html.open_tag('th')
        html.add_text('Puesto')
        html.close_tag('th')
        html.open_tag('th')
        html.add_text('Nombre')
        html.close_tag('th')
        html.open_tag('th')
        html.add_text('Puntos')
        html.close_tag('th')
        html.close_tag('tr')
        html.close_tag('thead')
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
    
    # Escribir el archivo HTML
    with open(html_file, 'w', encoding='utf-8') as f:
        f.write(html.get_html())

if __name__ == "__main__":
    xml_to_html('circuitoEsquema.xml', '../InfoCircuito.html')
