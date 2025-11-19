// Archivo: js/noticias.js
class Noticias {
    constructor(busqueda) {
        this.busqueda = busqueda; // término de búsqueda (ej. "MotoGP")
        this.url = "https://api.thenewsapi.com/v1/news/all";
        this.apiKey = "0yurf8SkyBm8HO3sglLVpzBq0jUTm36LuTX2ez5n"; 
    }
    async buscar() {
        try {
            const response = await $.ajax({
                url: `${this.url}?api_token=${this.apiKey}&search=${this.busqueda}&language=es`,
                method: "GET",
                dataType: "json"
            });
            this.procesarInformacion(response);
        } catch (error) {
            console.error("Error al obtener noticias:", error);
            $("#noticias").append("<p>Error al cargar noticias.</p>");
        }
    }

    procesarInformacion(json) {
        let contenedor = $("<section></section>");
        contenedor.append("<h3>Noticias MotoGP</h3>");

        if (json.data && json.data.length > 0) {
            json.data.slice(0, 5).forEach(noticia => {
                let articulo = $("<article></article>");
                articulo.append("<h4>" + noticia.title + "</h4>");
                articulo.append("<p>" + (noticia.description || "Sin descripción") + "</p>");
                articulo.append("<p><a href='" + noticia.url + "' target='_blank'>Leer más</a></p>");
                articulo.append("<p>Fuente: " + noticia.source + "</p>");
                contenedor.append(articulo);
            });
        } else {
            contenedor.append("<p>No se encontraron noticias sobre MotoGP.</p>");
        }

        $("#noticias").append(contenedor);
    }
}
