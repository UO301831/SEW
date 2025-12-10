-- Crear base de datos
CREATE DATABASE IF NOT EXISTS UO301831_DB;

USE UO301831_DB;

DROP TABLE IF EXISTS respuestas;
DROP TABLE IF EXISTS consideration;
DROP TABLE IF EXISTS test_info;
DROP TABLE IF EXISTS user_info;
DROP TABLE IF EXISTS dispositivo;
DROP TABLE IF EXISTS profesion;
DROP TABLE IF EXISTS genero;




DROP TABLE IF EXISTS dispositivo;
CREATE TABLE dispositivo (
    id_dispositivo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL UNIQUE
);

INSERT INTO dispositivo (nombre) VALUES
('ordenador'),
('tableta'),
('telefono');

DROP TABLE IF EXISTS profesion;
CREATE TABLE profesion (
    id_profesion INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(48) NOT NULL UNIQUE
);

DROP TABLE IF EXISTS genero;
CREATE TABLE genero (
    id_genero INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(15) NOT NULL UNIQUE
);

INSERT INTO genero (nombre) VALUES ('M'), ('F'), ('Otro');

-- TABLA PRINCIPAL: USUARIO


DROP TABLE IF EXISTS user_info;
CREATE TABLE user_info (
    id VARCHAR(12) PRIMARY KEY NOT NULL,
    id_profesion INT NOT NULL,
    edad INT NOT NULL CHECK (edad >= 0),
    id_genero INT NOT NULL,
    pericia INT NOT NULL CHECK (pericia >= 0 AND pericia <= 10),

    FOREIGN KEY (id_profesion) REFERENCES profesion(id_profesion),
    FOREIGN KEY (id_genero) REFERENCES genero(id_genero)
);

-- TABLA PRINCIPAL: RESULTADOS DEL TEST


DROP TABLE IF EXISTS test_info;
CREATE TABLE test_info (
    idUsuario VARCHAR(12) NOT NULL,
    id_dispositivo INT NOT NULL,
    tiempo_tardado INT NOT NULL,
    completado BOOLEAN NOT NULL,
    comentarios VARCHAR(512),
    propuestas VARCHAR(512),
    valoracion INT CHECK (valoracion >= 0 AND valoracion <= 10),

    PRIMARY KEY (idUsuario, id_dispositivo),
    FOREIGN KEY (idUsuario) REFERENCES user_info(id),
    FOREIGN KEY (id_dispositivo) REFERENCES dispositivo(id_dispositivo)
);


-- TABLA PRINCIPAL: OBSERVACIONES DEL FACILITADOR


DROP TABLE IF EXISTS consideration;
CREATE TABLE consideration (
    idUsuario VARCHAR(12) NOT NULL,
    comentarios VARCHAR(512) NOT NULL,

    PRIMARY KEY (idUsuario),
    FOREIGN KEY (idUsuario) REFERENCES user_info(id)
);

DROP TABLE IF EXISTS respuestas;
CREATE TABLE IF NOT EXISTS respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario VARCHAR(12) NOT NULL,
    pregunta_num TINYINT NOT NULL,
    respuesta TEXT NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES user_info(id) ON DELETE CASCADE
);
