CREATE DATABASE IF NOT EXISTS MotoGP_Usabilidad;
USE MotoGP_Usabilidad;

CREATE TABLE IF NOT EXISTS Genero (
    id_genero INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Profesion (
    id_profesion INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Dispositivo (
    id_dispositivo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Inserts básicos
INSERT IGNORE INTO Dispositivo (nombre) VALUES ('Ordenador'), ('Tableta'), ('Teléfono');
INSERT IGNORE INTO Genero (nombre) VALUES ('Hombre'), ('Mujer'), ('Otro');

-- Tabla Usuarios (AHORA CON AUTO_INCREMENT)
CREATE TABLE IF NOT EXISTS Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY, 
    edad INT NOT NULL,
    pericia INT NOT NULL CHECK (pericia BETWEEN 0 AND 10),
    id_profesion INT NOT NULL,
    id_genero INT NOT NULL,
    FOREIGN KEY (id_profesion) REFERENCES Profesion(id_profesion),
    FOREIGN KEY (id_genero) REFERENCES Genero(id_genero)
);

-- Tabla Resultados Test
CREATE TABLE IF NOT EXISTS Resultados_Test (
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
);

-- Tabla Observaciones Facilitador
CREATE TABLE IF NOT EXISTS Observaciones_Facilitador (
    id_usuario INT,
    id_dispositivo INT,
    comentarios_facilitador TEXT NOT NULL,
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- Tabla Respuestas
CREATE TABLE IF NOT EXISTS Respuestas_Cuestionario (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_dispositivo INT NOT NULL,
    numero_pregunta INT NOT NULL,
    texto_respuesta TEXT NOT NULL,
    FOREIGN KEY (id_usuario, id_dispositivo) REFERENCES Resultados_Test(id_usuario, id_dispositivo)
);