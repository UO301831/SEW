/* Alejandro Requena Roncero UO301831 */

// Variable global para el mapa
var mapaDinamico;

class Circuito {
    constructor() {
        // Selector específico sin usar IDs
        this.inputHTML = document.querySelector('main > section:nth-of-type(1) input[accept=".html"]');

        // Tarea 2: Comprobación de soporte
        if (!this.comprobarApiFile()) {
            this.mostrarErrorSoporte();
            return;
        }

        // Listener
        if (this.inputHTML) {
            this.inputHTML.addEventListener('change', (e) => this.leerArchivoHTML(e));
        }
    }

    comprobarApiFile() {
        return !!(window.File && window.FileReader && window.FileList && window.Blob);
    }

    mostrarErrorSoporte() {
        const mensaje = document.createElement("p");
        mensaje.textContent = "Tu navegador no soporta la API File.";
        const contenedor = document.querySelector('main > section:nth-of-type(1)');
        if (contenedor) contenedor.appendChild(mensaje);
    }

    leerArchivoHTML(evt) {
        const archivo = evt.target.files[0];
        if (!archivo) return;

        const lector = new FileReader();
        lector.onload = (e) => {
            this._procesarHTML(e.target.result);
        };
        lector.readAsText(archivo);
    }

    _procesarHTML(textoHTML) {
        try {
            const parser = new DOMParser();
            const docXML = parser.parseFromString(textoHTML, "text/html");

            // Seleccionamos las secciones de TU página (destino)
            const seccionesPagina = document.querySelectorAll('main > section');

            // --- CORRECCIÓN CLAVE ---
            // Tu archivo generado por Python tiene la info dentro de <section> tags.
            // Buscamos directamente todos los 'section' del archivo subido.
            // Esto evita coger el <header> (banner negro) o el <main> completo.
            const seccionesSubidas = docXML.querySelectorAll('section');

            seccionesSubidas.forEach(seccionSubida => {
                // Buscamos el título H2 dentro de la sección subida
                const tituloSubido = seccionSubida.querySelector('h2');
                
                if (tituloSubido) {
                    const textoTitulo = tituloSubido.textContent.trim();

                    // Buscamos la coincidencia en la página destino
                    // Empezamos en i=1 para saltar la sección "Archivos del circuito"
                    for (let i = 1; i < seccionesPagina.length; i++) {
                        const seccionPagina = seccionesPagina[i];
                        const tituloPagina = seccionPagina.querySelector('h2');

                        if (tituloPagina && tituloPagina.textContent.trim() === textoTitulo) {
                            // ¡COINCIDENCIA!
                            // Reemplazamos el contenido.
                            // Como el origen ya trae el <h2> correcto, podemos sustituir todo el innerHTML.
                            seccionPagina.innerHTML = seccionSubida.innerHTML;
                            break; // Pasamos a la siguiente sección subida
                        }
                    }
                }
            });

        } catch (error) {
            console.error("Error al procesar el archivo HTML:", error);
        }
    }
}

class CargadorSVG {
    constructor() {
        this.input = document.querySelector('main > section:nth-of-type(1) input[accept=".svg"]');
        this.container = document.querySelector('main > section:nth-of-type(8)');

        if (this.input) {
            this.input.addEventListener('change', (e) => this.leerArchivoSVG(e));
        }
    }

    leerArchivoSVG(evt) {
        const file = evt.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = () => {
            this.insertarSVG(reader.result);
        };
        reader.readAsText(file);
    }

    insertarSVG(svgText) {
        if (!this.container) return;
        
        // Mantenemos el título H2 original
        const titulo = this.container.querySelector('h2');
        this.container.innerHTML = ""; 
        if(titulo) this.container.appendChild(titulo);
        
        // Añadimos el SVG después
        this.container.innerHTML += svgText;
        
        const svgElement = this.container.querySelector('svg');
        if (svgElement) {
            svgElement.setAttribute('width', '100%');
            svgElement.setAttribute('height', 'auto');
        }
    }
}

class CargadorKML {
    constructor() {
        this.input = document.querySelector('main > section:nth-of-type(1) input[accept=".kml,.xml"]');
        
        if (this.input) {
            this.input.addEventListener('change', (e) => this.leerArchivoKML(e));
        }
    }

    leerArchivoKML(evt) {
        const file = evt.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = () => {
            this.procesarKML(reader.result);
        };
        reader.readAsText(file);
    }

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
                        if (!isNaN(lon) && !isNaN(lat)) {
                            points.push({ lat: lat, lng: lon });
                        }
                    }
                });
            });

            if (points.length > 0) {
                this.insertarEnGoogleMaps(points);
            }
        } catch (e) {
            console.error("Error procesando KML", e);
        }
    }

    insertarEnGoogleMaps(puntos) {
        if (!mapaDinamico) {
            // Este mensaje saldrá si la API Key falla o no ha cargado
            console.error("El mapa no está disponible (Revisa la API Key).");
            return;
        }

        const circuitoLine = new google.maps.Polyline({
            path: puntos,
            geodesic: true,
            strokeColor: '#FF0000',
            strokeOpacity: 1.0,
            strokeWeight: 4
        });

        circuitoLine.setMap(mapaDinamico);

        const bounds = new google.maps.LatLngBounds();
        puntos.forEach(p => bounds.extend(p));
        mapaDinamico.fitBounds(bounds);

        new google.maps.Marker({
            position: puntos[0],
            map: mapaDinamico,
            title: "Inicio Circuito"
        });
    }
}

// Callback global de Google Maps
function iniciarMapa() {
    // Buscamos el div dentro de la sección 9 (Mapa del circuito)
    const contenedorMapa = document.querySelector("main > section:nth-of-type(9) > div");

    if (contenedorMapa) {
        const centro = { lat: 40.416, lng: -3.703 }; 
        
        mapaDinamico = new google.maps.Map(contenedorMapa, {
            zoom: 8,
            center: centro,
            mapTypeId: 'terrain'
        });
        console.log("Mapa cargado correctamente.");
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Circuito();
    new CargadorSVG();
    new CargadorKML();
});