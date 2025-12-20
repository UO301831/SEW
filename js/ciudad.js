class Ciudad {
    #ciudad;
    #pais;
    #gentilicio;
    #puntoCentral;
    #cantidadPoblacion;

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

    #parseCoordinates(coordString) {
        const parts = coordString.split(',');
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

    // Escribe info básica de la ciudad en la 1ª sección
    writeCityInfo() {
        const contenedor = document.querySelector("main > section:nth-of-type(1)");
        if (!contenedor) return;

        const pCiudad = document.createElement("p");
        pCiudad.innerHTML = `Ciudad: ${this.cityToString()}`;
        contenedor.appendChild(pCiudad);

        const pPais = document.createElement("p");
        pPais.innerHTML = `País: ${this.countryToString()}`;
        contenedor.appendChild(pPais);

        const sectionInfo = document.createElement("section");
        sectionInfo.innerHTML = this.buildDemographicInfo();
        contenedor.appendChild(sectionInfo);
    }

    // Escribe coordenadas en la 1ª sección
    writeCoordinates() {
        const contenedor = document.querySelector("main > section:nth-of-type(1)");
        if (!contenedor) return;

        const p = document.createElement("p");
        p.innerHTML = `Coordenadas del punto central: ${this.#puntoCentral.lat.toFixed(4)}, ${this.#puntoCentral.lon.toFixed(4)}`;
        contenedor.appendChild(p);
    }

    // Tarea 3: Obtener datos meteorológicos del día de la carrera
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

    // Procesar JSON de la carrera y mostrar en HTML
    procesarJSONCarrera(json) {
        
        let contenedor = $("main > section:nth-of-type(2)");
        
        let articulo = $("<article></article>");
        articulo.append('<strong>Día 2025-08-17:</strong>');
        // Datos diarios
        articulo.append("<p>Salida del sol: " + this.#formatearHora(json.daily.sunrise[0]) + "</p>");
        articulo.append("<p>Puesta del sol: " + this.#formatearHora(json.daily.sunset[0]) + "</p>");

        // Datos horarios
        const horaIndex = 14; 
        articulo.append("<p>Hora de referencia: 14:00</p>");
        articulo.append("<p>Temperatura (2m): " + json.hourly.temperature_2m[horaIndex] + " " + json.hourly_units.temperature_2m + "</p>");
        articulo.append("<p>Sensación térmica: " + json.hourly.apparent_temperature[horaIndex] + " " + json.hourly_units.apparent_temperature + "</p>");
        articulo.append("<p>Lluvia: " + json.hourly.precipitation[horaIndex] + " " + json.hourly_units.precipitation + "</p>");
        articulo.append("<p>Humedad relativa: " + json.hourly.relative_humidity_2m[horaIndex] + " " + json.hourly_units.relative_humidity_2m + "</p>");
        articulo.append("<p>Viento: " + json.hourly.windspeed_10m[horaIndex] + " " + json.hourly_units.windspeed_10m + " dirección " + json.hourly.winddirection_10m[horaIndex] + json.hourly_units.winddirection_10m + "</p>");

        contenedor.append(articulo);
    }

    //Obtener datos meteorológicos de entrenamientos
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

    // Procesar JSON de entrenamientos (medias) y mostrar en HTML
    procesarJSONEntrenos(json, fechas) {
        let contenedor = $("main > section:nth-of-type(3)");
        
        let articulo = $("<article></article>");

        fechas.forEach((fecha) => {
            let indices = json.hourly.time.map((t, i) => t.startsWith(fecha) ? i : -1).filter(i => i >= 0);

            if (indices.length > 0) {
                let temp = indices.map(i => json.hourly.temperature_2m[i]);
                let lluvia = indices.map(i => json.hourly.precipitation[i]);
                let viento = indices.map(i => json.hourly.windspeed_10m[i]);
                let humedad = indices.map(i => json.hourly.relative_humidity_2m[i]);

                const media = (arr) => (arr.reduce((a, b) => a + b, 0) / arr.length).toFixed(2);

                let infoDia = $("<p></p>");
                infoDia.html(`<strong>Día ${fecha}:</strong>
                    Temp media: ${media(temp)} ${json.hourly_units.temperature_2m}, 
                    Lluvia media: ${media(lluvia)} ${json.hourly_units.precipitation}, 
                    Viento medio: ${media(viento)} ${json.hourly_units.windspeed_10m}, 
                    Humedad media: ${media(humedad)} ${json.hourly_units.relative_humidity_2m}`);
                
                articulo.append(infoDia);
            }
        });

        contenedor.append(articulo);
    }

    #formatearHora(fechaISO) {
        const fecha = new Date(fechaISO);
        const horas = fecha.getHours().toString().padStart(2, "0");
        const minutos = fecha.getMinutes().toString().padStart(2, "0");
        return `${horas}:${minutos} h`;
    }
}