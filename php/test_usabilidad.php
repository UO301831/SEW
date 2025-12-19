<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php'; 
require_once 'claseCronometro.php';

class TestUsabilidad {
    
    private $conn;
    private $crono;
    
    private $preguntas = [
        1 => "¿Quién es el autor de la aplicación MotoGP Desktop?",
        2 => "¿En qué ciudad nació el piloto Jack Miller?",
        3 => "¿Qué dorsal utiliza Jack Miller?",
        4 => "¿Cuál es la longitud del circuito Red Bull Ring (en metros)?",
        5 => "¿En qué localidad próxima se sitúa el circuito?",
        6 => "¿Qué tiempo marcó el vencedor Marc Márquez en 2025?",
        7 => "¿Cuántos puntos tiene Francesco Bagnaia en la clasificación?",
        8 => "Según la ayuda, ¿qué contiene la sección 'Meteorología'?",
        9 => "¿Qué otro juego hay disponible aparte del Cronómetro?",
        10 => "¿Con qué equipo corrió Jack Miller entre 2018 y 2022?"
    ];

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Error BD: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
        
        $this->crono = new Cronometro();

        if (isset($_SESSION['crono_inicio'])) {
             $this->crono->setEstado($_SESSION['crono_inicio'], 0);
        }
    }

    public function gestionar() {
        if (isset($_POST['iniciar_test'])) {
            return $this->procesarInicioTest();
        }
        if (isset($_POST['terminar_prueba'])) {
            $tiempoTotal = $this->crono->parar();
            unset($_SESSION['crono_inicio']);
            return $this->procesarRespuestasUsuario($tiempoTotal);
        }
        if (isset($_POST['guardar_observador'])) {
            return $this->procesarComentariosObservador();
        }

        if (isset($_GET['estado']) && $_GET['estado'] == 'observador') {
            $this->renderFormularioObservador();
        } elseif (isset($_GET['estado']) && $_GET['estado'] == 'exito') {
            $this->renderMensajeExito();
        } elseif (isset($_SESSION['id_usuario_actual'])) {
            $this->renderFormularioPreguntas();
        } else {
            $this->renderFormularioDatosPersonales();
        }
    }

    // --- LÓGICA DE BASE DE DATOS ---

    private function procesarInicioTest() {
        // NOTA: Ya no recogemos $_POST['dni'] porque es automático
        $edad = intval($_POST['edad']);
        $nombreGenero = $_POST['genero'];
        $nombreProfesion = $_POST['profesion'];
        $pericia = intval($_POST['pericia']);
        $nombreDispositivo = $_POST['dispositivo']; 

        // 1. Obtener/Crear Profesión
        $stmt = $this->conn->prepare("SELECT id_profesion FROM Profesion WHERE nombre = ?");
        $stmt->bind_param("s", $nombreProfesion);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $idProfesion = $row['id_profesion'];
        } else {
            $stmtIns = $this->conn->prepare("INSERT INTO Profesion (nombre) VALUES (?)");
            $stmtIns->bind_param("s", $nombreProfesion);
            $stmtIns->execute();
            $idProfesion = $this->conn->insert_id;
            $stmtIns->close();
        }
        $stmt->close();

        // 2. Obtener Género
        $stmt = $this->conn->prepare("SELECT id_genero FROM Genero WHERE nombre = ?");
        $stmt->bind_param("s", $nombreGenero);
        $stmt->execute();
        $res = $stmt->get_result();
        $idGenero = ($row = $res->fetch_assoc()) ? $row['id_genero'] : 3; 
        $stmt->close();

        // 3. INSERTAR USUARIO (AUTO_INCREMENT)
        // Ya no insertamos el ID, dejamos que la BD lo cree
        $sql = "INSERT INTO Usuarios (edad, pericia, id_profesion, id_genero) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $edad, $pericia, $idProfesion, $idGenero);
        $stmt->execute();
        
        // RECUPERAMOS EL ID GENERADO
        $nuevoIdUsuario = $this->conn->insert_id;
        $stmt->close();

        // 4. Iniciar Sesión y Crono
        $this->crono->arrancar();
        $_SESSION['id_usuario_actual'] = $nuevoIdUsuario; // Guardamos el ID generado
        $_SESSION['nombre_dispositivo_actual'] = $nombreDispositivo;
        $_SESSION['crono_inicio'] = $this->crono->getInicio();

        header("Location: test_usabilidad.php");
        exit;
    }

    private function procesarRespuestasUsuario($tiempo) {
        $idUsuario = $_SESSION['id_usuario_actual'];
        $nombreDispositivo = $_SESSION['nombre_dispositivo_actual'];
        
        $comentarios = $_POST['comentarios_usuario'] ?? '';
        $propuestas = $_POST['propuestas_usuario'] ?? '';
        $valoracion = intval($_POST['valoracion_usuario'] ?? 0);

        // Obtener ID Dispositivo
        $idDispositivo = 1;
        $res = $this->conn->query("SELECT id_dispositivo FROM Dispositivo WHERE nombre = '$nombreDispositivo'");
        if ($row = $res->fetch_assoc()) $idDispositivo = $row['id_dispositivo'];

        // Insertar Resultados
        $sql = "INSERT INTO Resultados_Test 
                (id_usuario, id_dispositivo, tiempo_segundos, completado, comentarios_usuario, propuestas_usuario, valoracion_usuario) 
                VALUES (?, ?, ?, 1, ?, ?, ?)
                ON DUPLICATE KEY UPDATE tiempo_segundos=VALUES(tiempo_segundos), completado=1";
        
        $stmt = $this->conn->prepare($sql);
        $tiempoInt = intval($tiempo);
        $stmt->bind_param("iiissi", $idUsuario, $idDispositivo, $tiempoInt, $comentarios, $propuestas, $valoracion);
        $stmt->execute();
        $stmt->close();

        // Insertar Respuestas
        $stmtIns = $this->conn->prepare("INSERT INTO Respuestas_Cuestionario (id_usuario, id_dispositivo, numero_pregunta, texto_respuesta) VALUES (?, ?, ?, ?)");
        foreach ($this->preguntas as $i => $q) {
            $resp = $_POST["p$i"] ?? '';
            $stmtIns->bind_param("iiis", $idUsuario, $idDispositivo, $i, $resp);
            $stmtIns->execute();
        }
        $stmtIns->close();

        header("Location: test_usabilidad.php?estado=observador");
        exit;
    }

    private function procesarComentariosObservador() {
        $idUsuario = $_SESSION['id_usuario_actual'];
        $nombreDispositivo = $_SESSION['nombre_dispositivo_actual'];
        $comentariosObs = $_POST['comentarios_facilitador'];

        $idDispositivo = 1;
        $res = $this->conn->query("SELECT id_dispositivo FROM Dispositivo WHERE nombre = '$nombreDispositivo'");
        if ($row = $res->fetch_assoc()) $idDispositivo = $row['id_dispositivo'];

        $stmt = $this->conn->prepare("INSERT INTO Observaciones_Facilitador (id_usuario, id_dispositivo, comentarios_facilitador) VALUES (?, ?, ?)
                                      ON DUPLICATE KEY UPDATE comentarios_facilitador = VALUES(comentarios_facilitador)");
        $stmt->bind_param("iis", $idUsuario, $idDispositivo, $comentariosObs);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['id_usuario_actual']);
        unset($_SESSION['nombre_dispositivo_actual']);
        unset($_SESSION['crono_inicio']);

        header("Location: test_usabilidad.php?estado=exito");
        exit;
    }

    // --- VISTAS ---

    private function renderFormularioDatosPersonales() {
        echo '<section>
                <h2>Datos del Participante</h2>
                <form action="test_usabilidad.php" method="post">
                    <fieldset>
                        <legend>Perfil de Usuario</legend>
                        <label for="edad">Edad:</label>
                        <input type="number" id="edad" name="edad" min="0" required />
                        
                        <label for="genero">Género:</label>
                        <select id="genero" name="genero" required>
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                            <option value="Otro">Otro</option>
                        </select>
                        
                        <label for="profesion">Profesión:</label>
                        <input type="text" id="profesion" name="profesion" required maxlength="100"/>

                        <label for="pericia">Nivel de pericia (0-10):</label>
                        <input type="number" id="pericia" name="pericia" min="0" max="10" required />

                        <label for="dispositivo">Dispositivo de la prueba:</label>
                        <select id="dispositivo" name="dispositivo" required>
                            <option value="Ordenador">Ordenador</option>
                            <option value="Tableta">Tableta</option>
                            <option value="Teléfono">Teléfono</option>
                        </select>
                    </fieldset>
                    
                    <button type="submit" name="iniciar_test">Comenzar Prueba</button>
                </form>
              </section>';
    }

    private function renderFormularioPreguntas() {
        echo '<section>
                <h2>Cuestionario</h2>
                <form action="test_usabilidad.php" method="post">
                    <fieldset>
                        <legend>Tareas</legend>';
        foreach ($this->preguntas as $i => $texto) {
            echo "<p><label for='p$i'><strong>$i.</strong> $texto</label>
                  <input type='text' id='p$i' name='p$i' required autocomplete='off' /></p>";
        }
        echo '      </fieldset>
                    <fieldset>
                        <legend>Feedback del Usuario</legend>
                        <label for="valoracion">Valoración Global (0-10):</label>
                        <input type="number" name="valoracion_usuario" min="0" max="10" required />
                        
                        <label for="comentarios">Comentarios:</label>
                        <textarea name="comentarios_usuario" rows="3"></textarea>
                        
                        <label for="propuestas">Propuestas de Mejora:</label>
                        <textarea name="propuestas_usuario" rows="3"></textarea>
                    </fieldset>
                    
                    <button type="submit" name="terminar_prueba">Finalizar y Enviar</button>
                </form>
              </section>';
    }

    private function renderFormularioObservador() {
        $id = $_SESSION['id_usuario_actual'] ?? 'Desconocido';
        echo '<section>
                <h2>Zona del Observador</h2>
                <p>Evaluación del Usuario ID: <strong>' . htmlspecialchars($id) . '</strong></p>
                <form action="test_usabilidad.php" method="post">
                    <fieldset>
                         <legend>Notas del Facilitador</legend>
                         <label for="comentarios_obs">Observaciones:</label>
                         <textarea id="comentarios_obs" name="comentarios_facilitador" rows="5" required></textarea>
                    </fieldset>
                    <button type="submit" name="guardar_observador">Guardar Sesión</button>
                </form>
              </section>';
    }

    private function renderMensajeExito() {
        echo '<section>
                <h2>¡Prueba Finalizada!</h2>
                <p>Datos guardados correctamente en la base de datos.</p>
                <p><a href="../index.html">Volver al Inicio</a></p>
              </section>';
    }
}

$test = new TestUsabilidad();
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>MotoGP - Prueba de Usabilidad</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" href="../multimedia/iconoMotoGP.ico" />
</head>
<body>
    <main>
        <?php $test->gestionar(); ?>
    </main>
</body>
</html>