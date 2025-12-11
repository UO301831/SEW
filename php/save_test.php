<?php
session_start();
require_once 'config.php'; 

header('Content-Type: application/json; charset=utf-8');

function json_err($m){ echo json_encode(['status'=>'error','message'=>$m]); exit; }
function json_ok($m){ echo json_encode(['status'=>'ok','message'=>$m]); exit; }

$idUsuario = $_POST['idUsuario'] ?? '';
$profesion = $_POST['profesion'] ?? 'Desconocida';
$edad      = $_POST['edad'] ?? 0;
$genero    = $_POST['genero'] ?? 'Otro'; 
$pericia   = $_POST['pericia'] ?? 0;
$dispositivo = $_POST['dispositivo'] ?? '';
$valoracion  = isset($_POST['valoracion']) ? intval($_POST['valoracion']) : null;

if (!$idUsuario || !$dispositivo) json_err('Faltan datos obligatorios (Usuario o Dispositivo)');


for ($i = 1; $i <= 10; $i++) {
    $campo = 'q' . $i;
    if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
        json_err("Error: Debes contestar a la pregunta número $i para guardar la prueba.");
    }
}

$completado = 1; 

// CÁLCULO DE TIEMPO
if (!isset($_SESSION['crono_transcurrido'])) {
    if (isset($_SESSION['crono_inicio']) && $_SESSION['crono_inicio'] > 0) {
        $trans = microtime(true) - $_SESSION['crono_inicio'];
    } else {
        $trans = 0;
    }
} else {
    $trans = floatval($_SESSION['crono_transcurrido']);
}
$tiempo_seg = intval(round($trans));

$comentarios_usuario = $_POST['comentarios_usuario'] ?? null;
$propuestas_usuario = $_POST['propuestas_usuario'] ?? null;
$comentarios_observador = $_POST['comentarios_observador'] ?? null;

$conn->begin_transaction();

try { 
    $stmt = $conn->prepare("SELECT id_dispositivo FROM dispositivo WHERE nombre = ?");
    $stmt->bind_param('s', $dispositivo);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $id_disp = $row['id_dispositivo'];
    } else {
        $ins = $conn->prepare("INSERT INTO dispositivo (nombre) VALUES (?)");
        $ins->bind_param('s', $dispositivo);
        $ins->execute();
        $id_disp = $ins->insert_id;
        $ins->close();
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id_profesion FROM profesion WHERE nombre = ?");
    $stmt->bind_param('s', $profesion);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $id_prof = $row['id_profesion'];
    } else {
        $ins = $conn->prepare("INSERT INTO profesion (nombre) VALUES (?)");
        $ins->bind_param('s', $profesion);
        $ins->execute();
        $id_prof = $ins->insert_id;
        $ins->close();
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id_genero FROM genero WHERE nombre = ?");
    $stmt->bind_param('s', $genero);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $id_gen = $row['id_genero'];
    } else {
        $ins = $conn->prepare("INSERT INTO genero (nombre) VALUES (?)");
        $ins->bind_param('s', $genero);
        $ins->execute();
        $id_gen = $ins->insert_id;
        $ins->close();
    }
    $stmt->close();

    // ---------------------------------------------------------
    // PASO B: INSERTAR O ACTUALIZAR EL USUARIO (Padre)
    // ---------------------------------------------------------
    $sqlUser = "INSERT INTO user_info (id, id_profesion, edad, id_genero, pericia) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                id_profesion=VALUES(id_profesion), 
                edad=VALUES(edad), 
                id_genero=VALUES(id_genero), 
                pericia=VALUES(pericia)";
                
    $stmt = $conn->prepare($sqlUser);
    $stmt->bind_param('siiii', $idUsuario, $id_prof, $edad, $id_gen, $pericia);
    $stmt->execute();
    $stmt->close();

    // ---------------------------------------------------------
    // PASO C: INSERTAR TEST_INFO (Hijo)
    // ---------------------------------------------------------
    // Aquí guardamos $completado que definimos como 1 arriba
    $sqlTest = "INSERT INTO test_info (idUsuario, id_dispositivo, tiempo_tardado, completado, comentarios, propuestas, valoracion) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                tiempo_tardado=VALUES(tiempo_tardado), 
                completado=VALUES(completado), 
                comentarios=VALUES(comentarios), 
                propuestas=VALUES(propuestas), 
                valoracion=VALUES(valoracion)";

    $stmt = $conn->prepare($sqlTest);
    // Nota: El orden de bind_param es: idUser(s), idDisp(i), tiempo(i), compl(i), com(s), prop(s), val(i) -> siissii
    $stmt->bind_param('siissii', $idUsuario, $id_disp, $tiempo_seg, $completado, $comentarios_usuario, $propuestas_usuario, $valoracion);
    $stmt->execute();
    $stmt->close();

    // Insertar observación del facilitador
    if ($comentarios_observador && trim($comentarios_observador) !== '') {
        $sqlObs = "INSERT INTO consideration (idUsuario, comentarios) 
                   VALUES (?, ?) 
                   ON DUPLICATE KEY UPDATE comentarios=VALUES(comentarios)";
        $stmt = $conn->prepare($sqlObs);
        $stmt->bind_param('ss', $idUsuario, $comentarios_observador);
        $stmt->execute();
        $stmt->close();
    }

    // Gestionar respuestas (Borrar antiguas e insertar nuevas)
    $stmtDel = $conn->prepare("DELETE FROM respuestas WHERE idUsuario = ?");
    $stmtDel->bind_param('s', $idUsuario);
    $stmtDel->execute();
    $stmtDel->close();

    for ($i = 1; $i <= 10; $i++) {
        $field = 'q' . $i;
        $ans = $_POST[$field] ?? '';
        // Aunque validamos arriba, aseguramos inserción
        if ($ans !== '') {
             $stmt = $conn->prepare("INSERT INTO respuestas (idUsuario, pregunta_num, respuesta) VALUES (?, ?, ?)");
             $stmt->bind_param('sis', $idUsuario, $i, $ans);
             $stmt->execute();
             $stmt->close();
        }
    }

    $conn->commit();

    // Limpiar sesión
    unset($_SESSION['crono_inicio']);
    unset($_SESSION['crono_transcurrido']);

    json_ok('Prueba guardada correctamente');

} catch (Exception $e) {
    $conn->rollback();
    json_err('Error guardando: ' . $e->getMessage());
}
?>