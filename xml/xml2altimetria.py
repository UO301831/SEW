#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
xml2altimetrial.py
Genera altimetria.svg simple a partir de circuitoEsquema.xml.
Uso: python xml2altimetrial.py circuitoEsquema.xml altimetria.svg
"""

import sys
import xml.etree.ElementTree as ET

# Clase simple para crear SVG básico
class Svg:
    def __init__(self, width=800, height=400):
        self.width = width
        self.height = height
        self.lines = []

    def header(self):
        self.lines.insert(0, '<?xml version="1.0" encoding="UTF-8"?>')
        self.lines.insert(1, f'<svg xmlns="http://www.w3.org/2000/svg" width="{self.width}" height="{self.height}" viewBox="0 0 {self.width} {self.height}">')

    def footer(self):
        self.lines.append('</svg>')

    def add(self, s):
        self.lines.append(s)

    def line(self, x1, y1, x2, y2, stroke="black", stroke_width=1):
        self.add(f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="{stroke}" stroke-width="{stroke_width}" />')

    def text(self, x, y, text, font_size=12, anchor="start"):
        self.add(f'<text x="{x}" y="{y}" font-size="{font_size}" text-anchor="{anchor}">{text}</text>')

    def polyline(self, points, stroke="black", stroke_width=2, fill="none"):
        pts = " ".join(f'{x:.1f},{y:.1f}' for x, y in points)
        self.add(f'<polyline points="{pts}" stroke="{stroke}" stroke-width="{stroke_width}" fill="{fill}" />')

    def save(self, filename):
        self.header()
        self.footer()
        with open(filename, "w", encoding="utf-8") as f:
            f.write("\n".join(self.lines))
        print(f"SVG guardado en: {filename}")

# Función auxiliar para convertir distancia a metros
def parse_distancia(dist_str, unidad_attr):
    try:
        d = float(dist_str)
    except:
        d = 0.0
    if unidad_attr and unidad_attr.lower() == "km":
        d *= 1000.0
    return d

# Función principal para generar altimetría simple
def generar_altimetria(xmlfile, svgfile):
    ns = {"u": "http://www.uniovi.es"}
    tree = ET.parse(xmlfile)
    root = tree.getroot()

    # Extraer puntos: origen + tramos
    puntos = []
    origen = root.find(".//u:origen", ns)
    if origen is not None:
        alt = float(origen.get("altitud", "0"))
        puntos.append((0.0, alt))

    tramos = root.findall(".//u:tramo", ns)
    distancia_acum = 0.0
    for tramo in tramos:
        d = parse_distancia(tramo.get("distancia", "0"), tramo.get("unidad"))
        distancia_acum += d
        fin = tramo.find("./u:fin", ns)
        if fin is not None:
            alt = float(fin.get("altitud", "0"))
            puntos.append((distancia_acum, alt))

    if len(puntos) < 2:
        print("No hay suficientes puntos.")
        return

    # Calcular escalas
    distancias = [p[0] for p in puntos]
    altitudes = [p[1] for p in puntos]
    total_dist = max(distancias)
    min_alt, max_alt = min(altitudes), max(altitudes)
    alt_range = max_alt - min_alt or 1.0

    margin = 50
    plot_width = 800 - 2 * margin
    plot_height = 400 - 2 * margin
    x_scale = plot_width / total_dist if total_dist > 0 else 1.0
    y_scale = plot_height / alt_range

    svg = Svg()

    # Ejes simples
    x0, y0 = margin, 400 - margin
    svg.line(x0, y0, x0 + plot_width, y0)  # Eje X
    svg.line(x0, margin, x0, y0)  # Eje Y

    # Etiquetas eje X: solo 0 y distancia final
    svg.text(x0, y0 + 15, "0", anchor="middle")
    svg.text(x0 + plot_width, y0 + 15, f"{int(total_dist)}", anchor="middle")

    # Intervalos eje Y: marcas cada cierto rango
    desired_y_ticks = 5
    step_y = alt_range / desired_y_ticks
    for i in range(desired_y_ticks + 1):
        alt = min_alt + i * step_y
        sy = y0 - (alt - min_alt) * y_scale
        svg.line(x0 - 5, sy, x0, sy, stroke="gray", stroke_width=1)  # Marca pequeña
        svg.text(x0 - 10, sy + 4, f"{int(alt)}", anchor="end")

    # Puntos transformados
    pts_svg = [(x0 + d * x_scale, y0 - (a - min_alt) * y_scale) for d, a in puntos]

    # Línea del perfil
    svg.polyline(pts_svg, stroke="blue", stroke_width=3)

    # Título
    svg.text(400, 30, "Perfil Altimétrico", font_size=16, anchor="middle")

    svg.save(svgfile)

# Main
if __name__ == "__main__":
    if len(sys.argv) < 3:
        xmlfile = "circuitoEsquema.xml"
        svgfile = "altimetria.svg"
    else:
        xmlfile = sys.argv[1]
        svgfile = sys.argv[2]
    generar_altimetria(xmlfile, svgfile)
