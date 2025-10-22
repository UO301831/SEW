#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
xml2altimetrial.py
Genera altimetria.svg a partir de circuitoEsquema.xml usando xml.etree.ElementTree y XPath.
Uso: python xml2altimetrial.py circuitoEsquema.xml altimetria.svg
"""

import sys
import xml.etree.ElementTree as ET
from math import isnan

# -------------------------
# Clase simple para crear SVG
# -------------------------
class Svg:
    def __init__(self, width=1200, height=500):
        self.width = width
        self.height = height
        self.lines = []
        self.header_written = False

    def header(self):
        # viewBox para que sea escalable
        self.lines.append('<?xml version="1.0" encoding="UTF-8"?>')
        self.lines.append(f'<svg xmlns="http://www.w3.org/2000/svg" width="{self.width}" height="{self.height}" viewBox="0 0 {self.width} {self.height}">')
        self.header_written = True

    def footer(self):
        self.lines.append('</svg>')

    def add(self, s):
        self.lines.append(s)

    def rect(self, x, y, w, h, stroke="black", fill="none", stroke_width=1):
        self.add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" stroke="{stroke}" fill="{fill}" stroke-width="{stroke_width}" />')

    def line(self, x1, y1, x2, y2, stroke="black", stroke_width=1, dasharray=None):
        dash = f' stroke-dasharray="{dasharray}"' if dasharray else ''
        self.add(f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="{stroke}" stroke-width="{stroke_width}"{dash} />')

    def text(self, x, y, text, font_size=12, anchor="start", transform=None):
        t = f' transform="{transform}"' if transform else ''
        self.add(f'<text x="{x}" y="{y}" font-size="{font_size}" text-anchor="{anchor}"{t}>{text}</text>')

    def polyline(self, points, stroke="black", stroke_width=2, fill="none", opacity=None):
        pts = " ".join(f'{x:.2f},{y:.2f}' for x, y in points)
        op = f' opacity="{opacity}"' if opacity else ''
        self.add(f'<polyline points="{pts}" stroke="{stroke}" stroke-width="{stroke_width}" fill="{fill}"{op} />')

    def save(self, filename):
        if not self.header_written:
            self.header()
        with open(filename, "w", encoding="utf-8") as f:
            f.write("\n".join(self.lines))
        print(f"SVG guardado en: {filename}")


# -------------------------
# Funciones auxiliares
# -------------------------
def parse_distancia(dist_str, unidad_attr):
    """
    Convierte distancia en atributo (string) a metros.
    Si unidad_attr == 'km' multiplica por 1000.
    """
    try:
        d = float(dist_str)
    except Exception:
        # intentar extraer número
        try:
            d = float(''.join(ch for ch in dist_str if (ch.isdigit() or ch == '.' or ch == ',' )).replace(',', '.'))
        except Exception:
            d = 0.0
    if unidad_attr and unidad_attr.lower() == "km":
        d *= 1000.0
    return d

def safe_float(s, default=0.0):
    try:
        return float(s)
    except:
        return default

# -------------------------
# Generador de altimetría
# -------------------------
def generar_altimetria(xmlfile, svgfile, width=1200, height=500):
    # Namespace del XML (obligatorio según tu XSD)
    ns = {"u": "http://www.uniovi.es"}

    tree = ET.parse(xmlfile)
    root = tree.getroot()

    # Extraer origen (obligatorio según especificación)
    origen_el = root.find(".//u:origen", ns)
    puntos = []  # lista de tuplas (dist_acumulada_m, alt_m)

    # Si existe origen, tomar su altitud como primer punto en distancia 0
    if origen_el is not None:
        alt_origen = safe_float(origen_el.get("altitud", "0"))
        puntos.append((0.0, alt_origen))
    else:
        # si no hay origen, uso primer tramo como inicio (advertencia)
        print("Advertencia: no se encontró <origen> en el XML. Se empezará desde distancia 0 con la primera altitud disponible.")

    # Recorrer tramos usando XPath (obligatorio)
    tramos = root.findall(".//u:tramo", ns)
    distancia_acum = 0.0
    # Si el origen existía, distancia_acum queda en 0; si no, lo dejamos en 0 igualmente
    for tramo in tramos:
        dist_attr = tramo.get("distancia", "0")
        unidad_attr = tramo.get("unidad", None)  # puede ser "m" o "km"
        d_m = parse_distancia(dist_attr, unidad_attr)
        distancia_acum += d_m

        # extraer el <fin> dentro del tramo (XPath relativo)
        fin = tramo.find("./u:fin", ns)
        if fin is not None:
            alt = safe_float(fin.get("altitud", "0"))
            puntos.append((distancia_acum, alt))
        else:
            # si no existe <fin>, lo ignoramos (pero decrementamos la distancia)
            print(f"Advertencia: tramo sin <fin> encontrado. distancia atribuida {d_m}m será omitida.")
            distancia_acum -= d_m

    if len(puntos) < 2:
        print("No hay suficientes puntos para generar altimetría.")
        return

    # calcular rango de distancias y altitudes
    distancias = [p[0] for p in puntos]
    altitudes = [p[1] for p in puntos]
    total_dist = max(distancias) - min(distancias)
    min_alt = min(altitudes)
    max_alt = max(altitudes)
    alt_range = max_alt - min_alt if max_alt > min_alt else 1.0

    # preparar SVG
    margin_x = 80
    margin_y = 40
    svg = Svg(width=width, height=height)
    svg.header()

    # fondo blanco
    svg.rect(0, 0, width, height, stroke="none", fill="white")

    plot_width = width - 2 * margin_x
    plot_height = height - 2 * margin_y

    # escalas
    x_scale = plot_width / total_dist if total_dist > 0 else 1.0
    y_scale = plot_height / alt_range

    # dibujar ejes
    x0 = margin_x
    y0 = height - margin_y  # origen (0,0) para nuestro plot en coordenadas SVG

    # eje X
    svg.line(x0, y0, x0 + plot_width, y0, stroke="#000000", stroke_width=1.2)
    # eje Y
    svg.line(x0, margin_y, x0, y0, stroke="#000000", stroke_width=1.2)

    # etiquetas de ejes
    svg.text(x0 - 40, margin_y + 10, f"{int(max_alt)} m", font_size=12, anchor="end")
    svg.text(x0 - 40, y0, f"{int(min_alt)} m", font_size=12, anchor="end")
    svg.text(x0 + plot_width/2, height - 5, "Distancia (m)", font_size=14, anchor="middle")

    # marcas y rejilla horizontal (cada N metros dependiendo de total_dist)
    # elegir número aproximado de marcas en X
    desired_x_ticks = 8
    step_x = total_dist / desired_x_ticks if desired_x_ticks > 0 else total_dist
    # marcas X
    for i in range(desired_x_ticks + 1):
        dx = i * step_x
        sx = x0 + dx * x_scale
        svg.line(sx, y0, sx, margin_y, stroke="#cccccc", stroke_width=0.8, dasharray="2,2")
        svg.text(sx, y0 + 15, f"{int(dx)}", font_size=10, anchor="middle")

    # marcas Y
    desired_y_ticks = 6
    step_y = alt_range / desired_y_ticks
    for j in range(desired_y_ticks + 1):
        alt = min_alt + j * step_y
        sy = y0 - (alt - min_alt) * y_scale
        svg.line(x0, sy, x0 + plot_width, sy, stroke="#eeeeee", stroke_width=0.8)
        svg.text(x0 - 10, sy + 4, f"{int(alt)}", font_size=10, anchor="end")

    # construir lista de puntos transformados a coordenadas SVG
    pts_svg = []
    for (dist, alt) in puntos:
        sx = x0 + (dist - min(distancias)) * x_scale
        sy = y0 - (alt - min_alt) * y_scale
        pts_svg.append((sx, sy))

    # polilínea del perfil (rellena cerrada para efecto suelo)
    # crear copia para cerrar: añadir punto final en baseline y punto inicial en baseline
    polyline_points = list(pts_svg)
    # añadir punto en baseline (última x, baseline y) y cerrar (primera x, baseline y)
    last_x = polyline_points[-1][0]
    first_x = polyline_points[0][0]
    baseline_y = y0
    polyline_points.append((last_x, baseline_y))
    polyline_points.append((first_x, baseline_y))
    # dibujar polilínea rellena
    svg.polyline(polyline_points, stroke="#d9534f", stroke_width=2, fill="#f2dede", opacity=0.9)

    # dibujar línea superior del perfil (solo la línea entre puntos reales)
    svg.polyline(pts_svg, stroke="#b52b2b", stroke_width=2, fill="none")

    # dibujar puntos y etiquetas cada N puntos (solo altitudes arriba, sin distancias abajo)
    label_every = max(1, len(pts_svg) // 12)
    for idx, (sx, sy) in enumerate(pts_svg):
        svg.line(sx, sy, sx, sy, stroke="#000000", stroke_width=1)  # punto (no visible salvo grosor)
        if (idx % label_every == 0) or idx == 0 or idx == len(pts_svg)-1:
            alt_m = altitudes[idx]
            svg.text(sx, sy - 8, f"{int(alt_m)} m", font_size=10, anchor="middle")
            # Eliminada la línea que ponía los números de distancia abajo

    # título
    svg.text(width/2, 18, "Perfil altimétrico del circuito", font_size=16, anchor="middle")

    svg.footer()
    svg.save(svgfile)


# -------------------------
# Main
# -------------------------
if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Uso: python xml2altimetrial.py circuitoEsquema.xml altimetria.svg")
        sys.exit(1)
    xmlfile = sys.argv[1]
    svgfile = sys.argv[2]
    generar_altimetria(xmlfile, svgfile)
