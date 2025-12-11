<?php
class Configuracion {

    private $conn;
    private $dbName = "UO301831_DB"; 

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function reiniciarBD() {
        $tablas = ["respuestas", "consideration", "test_info", "user_info", 
                   "profesion", "genero", "dispositivo"];

        $this->conn->query("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($tablas as $t) {
            $this->conn->query("DELETE FROM $t");
            $this->conn->query("ALTER TABLE $t AUTO_INCREMENT = 1");
        }

        $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");

        return "Base de datos reiniciada correctamente.";
    }
    public function eliminarBD() {
        $sql = "DROP DATABASE IF EXISTS {$this->dbName}";
        
        if ($this->conn->query($sql) === TRUE) {
            return "Base de datos eliminada correctamente.";
        }
        return "Error al eliminar la base de datos: " . $this->conn->error;
    }
    public function exportarCSV() {
        $filename = "export_usabilidad_" . date("Ymd_His") . ".csv";
        if (!file_exists("../export")) {
            mkdir("../export", 0777, true);
        }
        $filepath = "../export/" . $filename;

        $file = fopen($filepath, "w");

        fputs($file, "\xEF\xBB\xBF");

        $sep = ";";

        fputcsv($file, [
            "Usuario",
            "Profesión",
            "Edad",
            "Género",
            "Pericia",
            "Dispositivo",
            "Tiempo (s)",
            "Completado (1=Si, 0=No)",
            "Comentarios",
            "Propuestas",
            "Valoración"
        ], $sep);

        $sql = "
            SELECT 
                u.id, 
                COALESCE(p.nombre, 'Sin definir') AS profesion, 
                u.edad, 
                COALESCE(g.nombre, 'Sin definir') AS genero, 
                u.pericia,
                COALESCE(d.nombre, 'Sin definir') AS dispositivo, 
                t.tiempo_tardado, 
                COALESCE(t.completado, 0) as completado, 
                t.comentarios, 
                t.propuestas, 
                t.valoracion
            FROM user_info u
            LEFT JOIN profesion p ON u.id_profesion = p.id_profesion
            LEFT JOIN genero g ON u.id_genero = g.id_genero
            LEFT JOIN test_info t ON u.id = t.idUsuario
            LEFT JOIN dispositivo d ON t.id_dispositivo = d.id_dispositivo
        ";

        $resultado = $this->conn->query($sql);

        while ($fila = $resultado->fetch_assoc()) {
            if(isset($fila['comentarios'])) $fila['comentarios'] = str_replace(["\r", "\n"], " ", $fila['comentarios']);
            if(isset($fila['propuestas'])) $fila['propuestas'] = str_replace(["\r", "\n"], " ", $fila['propuestas']);

            fputcsv($file, $fila, $sep);
        }

        fclose($file);

        return "Datos exportados correctamente en: $filename";
    }
}
?>