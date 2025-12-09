class Carrusel {
    constructor(busqueda) {
        this.busqueda = busqueda;
        this.actual = 0;
        this.maximo = 4;
        this.fotos = [];
    }

    getFotografias() {
        var flickrAPI = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";

        $.getJSON(flickrAPI, {
            tags: this.busqueda,
            tagmode: "any",
            format: "json"
        })
        .done((data) => {
            this.procesarJSONFotografias(data);

            if (this.fotos.length > 0) {
                this.mostrarFotografias();
            } else {
                alert("No se encontraron fotos para la búsqueda: " + this.busqueda);
            }
        })
        .fail(() => {
            alert("Error al cargar imágenes de Flickr");
        });
    }

    procesarJSONFotografias(json) {
        this.fotos = [];

        $.each(json.items, (i, item) => {
            this.fotos.push(item.media.m.replace("_m.jpg", "_z.jpg"));
            if (i === this.maximo) return false;
        });
    }

    mostrarFotografias() {
        $("#imagenCarrusel").attr("src", this.fotos[this.actual]);
        setInterval(this.cambiarFotografia.bind(this), 3000);
    }

    cambiarFotografia() {
        if (this.fotos.length === 0) return;
        this.actual = (this.actual + 1) % this.fotos.length;
        $("#imagenCarrusel").attr("src", this.fotos[this.actual]);
    }
}
