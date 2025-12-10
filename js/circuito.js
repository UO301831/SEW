class Circuito {
  constructor() {
    this.input = document.querySelector('#inputInfoHTML');
    this.targetIds = ['#info-basica','#bibliografia','#fotografia','#multimedia','#vencedor','#clasificacion'];
    if (!this.comprobarApiFile()) {
      alert('El navegador no soporta File API');
      return;
    }
    if (this.input) {
      this.input.addEventListener('change', (e) => this.leerArchivoHTML(e));
    }
  }

  /** Devuelve true si el navegador soporta File API */
  comprobarApiFile() {
    return !!(window.File && window.FileReader && window.FileList && window.Blob);
  }

  /** Lee el archivo HTML seleccionado y llama al parseador */
  leerArchivoHTML(evt) {
    const file = evt.target.files && evt.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
      const texto = reader.result;
      this._procesarHTML(texto);
    };
    reader.readAsText(file, 'utf-8');
  }

  /** Inserta en las secciones del documento el contenido correspondiente */
  _procesarHTML(htmlText) {
    try {
      const parser = new DOMParser();
      const doc = parser.parseFromString(htmlText, 'text/html');
      this.targetIds.forEach((id) => {
        const origen = doc.querySelector(id);
        const destino = document.querySelector(id);
        if (origen && destino) destino.innerHTML = origen.innerHTML;
      });
    } catch (e) {}
  }

}

class CargadorSVG {
  /** Configura el input para leer archivos SVG y el contenedor donde insertarlo */
  constructor() {
    this.input = document.querySelector('#inputSVG');
    this.container = document.querySelector('#altimetria');
    if (this.input) {
      this.input.addEventListener('change', (e) => this.leerArchivoSVG(e));
    }
  }

  /** Lee el archivo SVG seleccionado y lo pasa a insertar */
  leerArchivoSVG(evt) {
    const file = evt.target.files && evt.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
      const texto = reader.result;
      this.insertarSVG(texto);
    };
    reader.readAsText(file, 'utf-8');
  }

  /** Inserta el texto SVG dentro del contenedor (innerHTML) */
  insertarSVG(svgText) {
    if (!this.container) return;
    this.container.innerHTML = svgText;
  }
}

class CargadorKML {
  /** Configura el input para leer KML y el svg fallback donde dibujar */
  constructor() {
    this.input = document.querySelector('#inputKML');
    this.svg = document.querySelector('#mapSvgFallback');
    if (this.input) {
      this.input.addEventListener('change', (e) => this.leerArchivoKML(e));
    }
  }

  /** Lee el archivo KML seleccionado y llama al procesador */
  leerArchivoKML(evt) {
    const file = evt.target.files && evt.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
      const texto = reader.result;
      this.procesarKML(texto);
    };
    reader.readAsText(file, 'utf-8');
  }

  /** Extrae coordenadas del KML y dibuja una polyline en el SVG */
  procesarKML(kmlText) {
    try {
      const parser = new DOMParser();
      const xml = parser.parseFromString(kmlText, 'application/xml');
      const coordsNodes = xml.querySelectorAll('coordinates');
      const points = [];
      coordsNodes.forEach(node => {
        const raw = node.textContent.trim();
        const items = raw.split(/\s+/);
        items.forEach(it => {
          const parts = it.split(',');
          if (parts.length >= 2) {
            const lon = parseFloat(parts[0]);
            const lat = parseFloat(parts[1]);
            if (!isNaN(lon) && !isNaN(lat)) points.push([lat, lon]);
          }
        });
      });
    if (points.length) {
        this.dibujarEnSVG(points);  // fallback obligatorio
        this.insertarEnGoogleMaps(points); // para Google Maps
    }
    } catch (e) {
      console.error(e);
    }
  }

  /** Dibuja una línea y marcador de origen sencilla dentro del svg de fallback */
  dibujarEnSVG(points) {
    if (!this.svg) return;
    while (this.svg.firstChild) this.svg.removeChild(this.svg.firstChild);

    const w = this.svg.clientWidth || 600;
    const h = this.svg.clientHeight || 400;
    this.svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
    const lats = points.map(p => p[0]);
    const lons = points.map(p => p[1]);
    const minLat = Math.min(...lats), maxLat = Math.max(...lats);
    const minLon = Math.min(...lons), maxLon = Math.max(...lons);
    const pad = 20;
    const sx = (w - pad*2) / ((maxLon - minLon) || 1);
    const sy = (h - pad*2) / ((maxLat - minLat) || 1);

    const toXY = (p) => {
      const x = pad + (p[1] - minLon) * sx;
      const y = h - (pad + (p[0] - minLat) * sy);
      return [x, y];
    };

    const pointsAttr = points.map(p => toXY(p).join(',')).join(' ');
    const poly = document.createElementNS('http://www.w3.org/2000/svg','polyline');
    poly.setAttribute('points', pointsAttr);
    poly.setAttribute('fill', 'none');
    poly.setAttribute('stroke', '#d00');
    poly.setAttribute('stroke-width', '3');
    this.svg.appendChild(poly);

    const originXY = toXY(points[0]);
    const circ = document.createElementNS('http://www.w3.org/2000/svg','circle');
    circ.setAttribute('cx', originXY[0]);
    circ.setAttribute('cy', originXY[1]);
    circ.setAttribute('r', 6);
    circ.setAttribute('fill', '#0055aa');
    this.svg.appendChild(circ);
  }

  insertarEnGoogleMaps(points) {
  if (!map) return;

  const path = points.map(p => ({ lat: p[0], lng: p[1] }));

  const polyline = new google.maps.Polyline({
    path: path,
    strokeColor: "#FF0000",
    strokeOpacity: 1.0,
    strokeWeight: 3,
    map: map
  });

  new google.maps.Marker({
    position: path[0],
    map: map,
    title: "Inicio del circuito"
  });

  // Ajustar mapa al circuito
  const bounds = new google.maps.LatLngBounds();
  path.forEach(pt => bounds.extend(pt));
  map.fitBounds(bounds);
}

}

// ------------------------------
// GOOGLE MAPS: creación del mapa
// ------------------------------
let map;

function iniciarMapa() {
  const centroInicial = { lat: 47.21995421616413, lng:  14.76488816433875};

  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 14,
    center: centroInicial,
    mapTypeId: "terrain"
  });

  console.log("Mapa cargado correctamente");
}


document.addEventListener('DOMContentLoaded', () => {
  new Circuito();
  new CargadorSVG();
  new CargadorKML();
});
