class Ciudad {
    #ciudad
    #pais
    #gentilicio
    #puntoCentral
    #cantidadPoblacion

    constructor(ciudad, pais, gentilicio) {
        this.#ciudad = ciudad;
        this.#gentilicio = gentilicio;
        this.#pais = pais;
        this.#puntoCentral = null;
        this.#cantidadPoblacion = null;
    }

    initializeGeo(cantidadPoblacion, puntoCentral) {
        this.#cantidadPoblacion = cantidadPoblacion;
        this.#puntoCentral = this.#parseCoordinates(puntoCentral);
    }

    // Método auxiliar para convertir coordenadas DMS a decimal
    #parseCoordinates(coordString) {
        const parts = coordString.split(', ');
        const latDMS = parts[0].trim();  
        const lonDMS = parts[1].trim(); 
        const lat = this.#dmsToDecimal(latDMS);
        const lon = this.#dmsToDecimal(lonDMS);
        return { lat, lon };
    }

    #dmsToDecimal(dms) {
        const regex = /(\d+)°(\d+)′(\d+)″([NSWE])/;
        const match = dms.match(regex);
        if (!match) return 0;
        let degrees = parseInt(match[1]);
        let minutes = parseInt(match[2]);
        let seconds = parseInt(match[3]);
        const direction = match[4];
        let decimal = degrees + minutes / 60 + seconds / 3600;
        if (direction === 'S' || direction === 'W') decimal = -decimal;
        return decimal;
    }

    cityToString() {
        return this.#ciudad;
    }

    countryToString() {
        return this.#pais;
    }

    buildDemographicInfo() {
        return `
            <ul>
                <li>Gentilicio: ${this.#gentilicio}</li>
                <li>Población: ${this.#cantidadPoblacion}</li>
            </ul>
        `;
    }

    writeCoordinates() {
        const p = document.createElement("p");
        p.innerHTML = `Coordenadas del punto central: ${this.#puntoCentral.lat}, ${this.#puntoCentral.lon}`;
        document.getElementById("infoCiudad").appendChild(p);
    }

    // Obtener datos meteorológicos del día de la carrera
    getMeteorologiaCarrera(fecha) {
        let url = "https://archive-api.open-meteo.com/v1/archive?latitude=" + this.#puntoCentral.lat
                + "&longitude=" + this.#puntoCentral.lon
                + "&start_date=" + fecha
                + "&end_date=" + fecha
                + "&hourly=temperature_2m,apparent_temperature,precipitation,relative_humidity_2m,windspeed_10m,winddirection_10m"
                + "&daily=sunrise,sunset"
                + "&timezone=Europe/Berlin";

        $.ajax({
            url: url,
            dataType: "json",
            success: (data) => {
                this.procesarJSONCarrera(data);
            },
            error: () => {
                alert("Error al cargar datos meteorológicos de la carrera");
            }
        });
    }

    // Procesar JSON de la carrera
    procesarJSONCarrera(json) {
        let contenedor = $("<section></section>");
        contenedor.append("<h3>Meteorología Carrera</h3>");

        // Datos diarios (sunrise, sunset)
        contenedor.append("<p>Salida del sol: " + json.daily.sunrise[0] + "</p>");
        contenedor.append("<p>Puesta del sol: " + json.daily.sunset[0] + "</p>");

        // Datos horarios (ejemplo: primera hora del día)
        contenedor.append("<p>Temperatura (2m): " + json.hourly.temperature_2m[0] + " °C</p>");
        contenedor.append("<p>Sensación térmica: " + json.hourly.apparent_temperature[0] + " °C</p>");
        contenedor.append("<p>Lluvia: " + json.hourly.precipitation[0] + " mm</p>");
        contenedor.append("<p>Humedad relativa: " + json.hourly.relative_humidity_2m[0] + " %</p>");
        contenedor.append("<p>Viento: " + json.hourly.windspeed_10m[0] + " km/h dirección " + json.hourly.winddirection_10m[0] + "°</p>");

        // Añadir al contenedor correcto
        $("#meteoCarrera").append(contenedor);
    }

    // Obtener datos meteorológicos de los días de entrenamientos
    getMeteorologiaEntrenos(fechas) {
        let start = fechas[0];
        let end = fechas[fechas.length - 1];

        let url = "https://archive-api.open-meteo.com/v1/archive?latitude=" + this.#puntoCentral.lat
                + "&longitude=" + this.#puntoCentral.lon
                + "&start_date=" + start
                + "&end_date=" + end
                + "&hourly=temperature_2m,precipitation,windspeed_10m,relative_humidity_2m"
                + "&timezone=Europe/Berlin";

        $.ajax({
            url: url,
            dataType: "json",
            success: (data) => {
                this.procesarJSONEntrenos(data, fechas);
            },
            error: () => {
                alert("Error al cargar datos meteorológicos de entrenamientos");
            }
        });
    }

    // Procesar JSON de entrenamientos y calcular medias
    procesarJSONEntrenos(json, fechas) {
        let contenedor = $("<section></section>");
        contenedor.append("<h3>Meteorología Entrenamientos</h3>");

        // Calcular medias por día
        fechas.forEach((fecha) => {
            let indices = json.hourly.time.map((t, i) => t.startsWith(fecha) ? i : -1).filter(i => i >= 0);

            let temp = indices.map(i => json.hourly.temperature_2m[i]);
            let lluvia = indices.map(i => json.hourly.precipitation[i]);
            let viento = indices.map(i => json.hourly.windspeed_10m[i]);
            let humedad = indices.map(i => json.hourly.relative_humidity_2m[i]);

            function media(arr) { return (arr.reduce((a, b) => a + b, 0) / arr.length).toFixed(2); }

            contenedor.append("<p>Día " + fecha + ": Temp media " + media(temp) + " °C, Lluvia media " + media(lluvia) + " mm, Viento medio " + media(viento) + " km/h, Humedad media " + media(humedad) + " %</p>");
        });

        // Añadir al contenedor correcto
        $("#meteoEntrenos").append(contenedor);
    }
}
