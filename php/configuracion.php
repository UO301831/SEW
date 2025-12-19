<?php
require_once "config.php";

class Configuracion {
    
    protected $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        if ($this->conn->connect_error) {
            die("Error de conexión al servidor: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    public function crearBaseDatos() {
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
        if ($this->conn->query($sql) === TRUE) {
            $this->conn->select_db(DB_NAME);
            return $this->crearTablas();
        } else {
            return "Error creando la BD: " . $this->conn->error;
        }
    }

    private function crearTablas() {
        // Arrays de queries copiados y adaptados de tu script SQL
        $queries = [];

        // 1. Tablas independientes (Lookups)
        $queries[] = "CREATE TABLE IF NOT EXISTS Genero (
            id_genero INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) NOT NULL UNIQUE
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS Profesion (
            id_profesion INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL UNIQUE
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS Dispositivo (
            id_dispositivo INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) NOT NULL UNIQUE
        )";

        // 2. Tabla Usuarios
        $queries[] = "CREATE TABLE IF NOT EXISTS Usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY, 
            edad INT NOT NULL,
            pericia INT NOT NULL CHECK (pericia BETWEEN 0 AND 10),
            id_profesion INT NOT NULL,
            id_genero INT NOT NULL,
            FOREIGN KEY (id_profesion) REFERENCES Profesion(id_profesion),
            FOREIGN KEY (id_genero) REFERENCES Genero(id_genero)
        )";

        // 3. Resultados Test
        $queries[] = "CREATE TABLE IF NOT EXISTS Resultados_Test (
            id_usuario INT,
            id_dispositivo INT,
            tiempo_segundos INT NOT NULL,
            completado BOOLEAN NOT NULL DEFAULT FALSE,
            comentarios_usuario TEXT,
            propuestas_usuario TEXT,
            valoracion_usuario INT CHECK (valoracion_usuario BETWEEN 0 AND 10),
            PRIMARY KEY (id_usuario, id_dispositivo),
            FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario),
            FOREIGN KEY (id_dispositivo) REFERENCES Dispositivo(id_dispositivo)
        )";

        // 4. Observaciones Facilitador
        $queries[] = "CREATE TABLE IF NOT EXISTS Observaciones_Facilitador (
            id_usuario INT,
            id_dispositivo INT, -- Opcional según tu script, pero bueno para integridad
            comentarios_facilitador TEXT NOT NULL,
            PRIMARY KEY (id_usuario),
            FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
        )";

        // 5. Respuestas Cuestionario
        $queries[] = "CREATE TABLE IF NOT EXISTS Respuestas_Cuestionario (
            id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            id_dispositivo INT NOT NULL,
            numero_pregunta INT NOT NULL,
            texto_respuesta TEXT NOT NULL,
            FOREIGN KEY (id_usuario, id_dispositivo) REFERENCES Resultados_Test(id_usuario, id_dispositivo)
        )";

        // Inserts básicos
        $inserts = [
            "INSERT IGNORE INTO Dispositivo (nombre) VALUES ('Ordenador'), ('Tableta'), ('Teléfono')",
            "INSERT IGNORE INTO Genero (nombre) VALUES ('Hombre'), ('Mujer'), ('Otro')"
        ];

        // Ejecutar creación de tablas
        foreach ($queries as $sql) {
            if (!$this->conn->query($sql)) return "Error creando tabla: " . $this->conn->error;
        }

        // Ejecutar inserts iniciales
        foreach ($inserts as $sql) {
            $this->conn->query($sql);
        }

        return "Base de datos configurada correctamente";
    }

    public function eliminarBaseDatos() {
        $sql = "DROP DATABASE IF EXISTS " . DB_NAME;
        if ($this->conn->query($sql)) {
            return "Base de datos eliminada correctamente.";
        } else {
            return "Error eliminando BD: " . $this->conn->error;
        }
    }

    public function reiniciarDatos() {
        if (!$this->conn->select_db(DB_NAME)) return "Error: BD no existe.";

        $this->conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Lista de tablas actualizada
        $tablas = ["Respuestas_Cuestionario", "Observaciones_Facilitador", "Resultados_Test", "Usuarios", "Profesion", "Genero", "Dispositivo"];
        
        foreach ($tablas as $tabla) {
            $this->conn->query("DELETE FROM $tabla");
            $this->conn->query("ALTER TABLE $tabla AUTO_INCREMENT = 1");
        }

        // Reinsertar valores por defecto necesarios
        $this->conn->query("INSERT IGNORE INTO Dispositivo (nombre) VALUES ('Ordenador'), ('Tableta'), ('Teléfono')");
        $this->conn->query("INSERT IGNORE INTO Genero (nombre) VALUES ('Hombre'), ('Mujer'), ('Otro')");

        $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");
        return "Datos reiniciados. Tablas vacías.";
    }

    public function exportarCSV() {
        if (!$this->conn->select_db(DB_NAME)) return "Error: BD no seleccionada.";

        $filename = "export_motogp_" . date("Ymd_His") . ".csv";
        $dir = "../export";
        if (!file_exists($dir)) mkdir($dir, 0777, true);
        
        $filepath = $dir . "/" . $filename;
        $file = fopen($filepath, "w");
        fputs($file, "\xEF\xBB\xBF");
        $sep = ";";

        fputcsv($file, [
            "ID Usuario", "Edad", "Género", "Profesión", "Pericia", 
            "Dispositivo", "Tiempo (s)", "Completado", 
            "Comentarios Usuario", "Propuestas", "Valoración", "Obs. Facilitador"
        ], $sep);

        $sql = "SELECT 
                u.id_usuario, 
                u.edad, 
                g.nombre as genero,
                p.nombre as profesion,
                u.pericia,
                d.nombre as dispositivo,
                r.tiempo_segundos,
                r.completado,
                r.comentarios_usuario,
                r.propuestas_usuario,
                r.valoracion_usuario,
                o.comentarios_facilitador
            FROM Usuarios u
            LEFT JOIN Genero g ON u.id_genero = g.id_genero
            LEFT JOIN Profesion p ON u.id_profesion = p.id_profesion
            LEFT JOIN Resultados_Test r ON u.id_usuario = r.id_usuario
            LEFT JOIN Dispositivo d ON r.id_dispositivo = d.id_dispositivo
            LEFT JOIN Observaciones_Facilitador o ON u.id_usuario = o.id_usuario";

        $resultado = $this->conn->query($sql);

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                foreach ($fila as $key => $val) {
                    $fila[$key] = str_replace(["\r", "\n"], " ", $val);
                }
                fputcsv($file, $fila, $sep);
            }
        } else {
            fclose($file);
            return "Error SQL: " . $this->conn->error;
        }

        fclose($file);
        return "Exportación completada: $filename";
    }
}
?>