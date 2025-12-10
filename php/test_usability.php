<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Test de Usabilidad – MotoGP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css"/>
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css"/>
    <link rel="icon" href="multimedia/iconoMotoGP.ico"/>
</head>

<body>
<header>
    <h1>Prueba de Usabilidad</h1>
</header>

<main>
    <p><strong>Instrucciones:</strong> Rellena tus datos personales. Cuando estés listo, pulsa <em>Iniciar prueba</em>. Al finalizar las preguntas, pulsa <em>Terminar prueba</em> para que el observador anote sus comentarios.</p>

    <form id="startForm" method="POST" action="cronometro.php" target="cronoframe">
        <button type="submit" name="arrancar" class="boton-accion">Iniciar prueba</button>
    </form>


    <form id="formTest" method="POST" action="save_test.php">

        <fieldset>
            <legend>Datos del participante</legend>
            <div class="fila">
                <label>Identificador (1–12):</label>
                <input type="number" name="idUsuario" min="1" max="12" required>
            </div>
            <div class="fila">
                <label>Profesión:</label>
                <input type="text" name="profesion" required>
            </div>
            <div class="fila">
                <label>Edad:</label>
                <input type="number" name="edad" min="0" required>
            </div>
            <div class="fila">
                <label>Género:</label>
                <select name="genero" required>
                    <option value="">-- seleccionar --</option>
                    <option value="M">Hombre</option>
                    <option value="F">Mujer</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="fila">
                <label>Pericia informática (0–10):</label>
                <input type="number" name="pericia" min="0" max="10" required>
            </div>
            <div class="fila">
                <label>Dispositivo:</label>
                <select name="dispositivo" required>
                    <option value="">-- seleccionar --</option>
                    <option value="ordenador">Ordenador</option>
                    <option value="tableta">Tableta</option>
                    <option value="telefono">Teléfono</option>
                </select>
            </div>
             <div class="fila">
                <label>Valoración App (0–10):</label>
                <input type="number" name="valoracion" min="0" max="10" required>
            </div>
        </fieldset>

        <fieldset id="preguntas" class="hidden">
            <legend>Cuestionario sobre el Proyecto</legend>

            <div class="fila"><label>1. Longitud y anchura del circuito</label><input type="text" name="q1" required></div>
            <div class="fila"><label>2. Localidad y país del circuito</label><input type="text" name="q2" required></div>
            <div class="fila"><label>3. Número de vueltas programadas</label><input type="number" name="q3" required></div>
            <div class="fila"><label>4. Fecha y hora de la carrera</label><input type="text" name="q4" placeholder="AAAA-MM-DD HH:MM:SS" required></div>
            <div class="fila"><label>5. Patrocinador del Gran Premio</label><input type="text" name="q5" required></div>
            <div class="fila"><label>6. Piloto ganador  </label><input type="text" name="q6" required></div>
            <div class="fila"><label>7. Segundo puesto</label><input type="text" name="q7" required></div>
            <div class="fila"><label>8. Tercer puesto</label><input type="text" name="q8" required></div>
            <div class="fila"><label>9. Diferencia de puntos (1º y 2º)</label><input type="number" name="q9" required></div>
            <div class="fila"><label>10. Fuentes citadas en bibliografía</label><input type="text" name="q10" required></div>
        </fieldset>

        <fieldset id="campoObservador" class="hidden">
            <legend>Área del Observador</legend>
            <p>Comentarios adicionales sobre la realización de la prueba:</p>
            <textarea name="comentarios_observador" rows="5" style="width: 100%;"></textarea>
        </fieldset>

        <button id="btnGuardar" class="hidden" type="submit">Guardar prueba en Base de Datos</button>

    </form>


    <form id="stopForm" method="POST" action="cronometro.php" target="cronoframe" class="hidden">
        <button type="submit" name="parar" class="boton-accion">Terminar prueba</button>
    </form>


    <iframe name="cronoframe" style="display:none;"></iframe>

</main>

<script>
    const startForm = document.getElementById('startForm');
    const stopForm = document.getElementById('stopForm');
    const formTest = document.getElementById('formTest');
    const preguntas = document.getElementById('preguntas');
    const campoObservador = document.getElementById('campoObservador');
    const btnGuardar = document.getElementById('btnGuardar');

    startForm.addEventListener('submit', () => {
        startForm.classList.add('hidden');
        preguntas.classList.remove('hidden');
        stopForm.classList.remove('hidden');
    });

    stopForm.addEventListener('submit', (event) => {
        let todasContestadas = true;
        const inputsPreguntas = preguntas.querySelectorAll('input'); 
        
        inputsPreguntas.forEach(input => {
            if (input.value.trim() === '') {
                todasContestadas = false;
                input.style.borderColor = "red";
            } else {
                input.style.borderColor = "";
            }
        });
        if (!todasContestadas) {
            event.preventDefault();
            alert("Por favor, responde a las 10 preguntas antes de terminar la prueba. El tiempo sigue corriendo.");
            return; 
        }

        stopForm.classList.add('hidden');
        
        preguntas.classList.add('bloqueado');
        inputsPreguntas.forEach(input => input.readOnly = true);

        campoObservador.classList.remove('hidden');
        btnGuardar.classList.remove('hidden');
    });
</script>

</body>
</html>