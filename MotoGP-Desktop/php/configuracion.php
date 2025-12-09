<?php
$host = "localhost";
$user = "DBUSER2025";
$pass = "DBPSWD2025";
$db = "UO301831_DB";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    exit("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>

<?php
class Configuracion {

    private $conn;
    private $dbName = "UO301831_DB"; 

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ----------------------------------------------------
       REINICIAR BASE DE DATOS (vaciar datos, NO eliminar tablas)
       ---------------------------------------------------- */
    public function reiniciarBD() {
        $tablas = ["consideration", "test_info", "user_info", 
                    "profesion", "genero", "dispositivo"];

        foreach ($tablas as $t) {
            $this->conn->query("DELETE FROM $t");
        }

        return "Base de datos reiniciada correctamente.";
    }

    /* ----------------------------------------------------
       ELIMINAR BASE DE DATOS COMPLETA
       ---------------------------------------------------- */
    public function eliminarBD() {
        $sql = "DROP DATABASE IF EXISTS {$this->dbName}";
        
        if ($this->conn->query($sql) === TRUE) {
            return "Base de datos eliminada correctamente.";
        }
        return "Error al eliminar la base de datos: " . $this->conn->error;
    }

    /* ----------------------------------------------------
       EXPORTAR A CSV (los datos de las pruebas)
       ---------------------------------------------------- */
    public function exportarCSV() {
        $filename = "export_usabilidad_" . date("Ymd_His") . ".csv";
        $filepath = "../export/" . $filename;

        if (!file_exists("../export")) {
            mkdir("../export");
        }

        $file = fopen($filepath, "w");

        fputcsv($file, [
            "Usuario",
            "Profesión",
            "Edad",
            "Género",
            "Pericia",
            "Dispositivo",
            "Tiempo",
            "Completado",
            "Comentarios",
            "Mejoras",
            "Valoración"
        ]);

        $sql = "
            SELECT 
                u.id, p.nombre AS profesion, u.edad, g.nombre AS genero, u.pericia,
                d.nombre AS dispositivo, 
                t.tiempo_tardado, t.completado, t.comentarios, t.propuestas, t.valoracion
            FROM user_info u
            LEFT JOIN profesion p ON u.id_profesion = p.id_profesion
            LEFT JOIN genero g ON u.id_genero = g.id_genero
            LEFT JOIN test_info t ON u.id = t.idUsuario
            LEFT JOIN dispositivo d ON t.id_dispositivo = d.id_dispositivo;
        ";

        $resultado = $this->conn->query($sql);

        while ($fila = $resultado->fetch_assoc()) {
            fputcsv($file, $fila);
        }

        fclose($file);

        return "Datos exportados en: $filename";
    }
}
?>
