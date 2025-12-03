drop table if exists user_info
create table user_info(
    id varchar(12) primary key not null,
    profesion varchar(48) not null,
    edad integer check (edad >= 0),
    genero varchar(1) check (genero like 'M' or genero like 'F'),
    pericia integer check (pericia >= 0 AND pericia <= 10)
);

drop table if exists test_info
create table test_info(
    idUsuario varchar(12) primary key not null,
    dispositivo varchar(48) not null check (LOWER(dispotivo) like 'ordenador' OR LOWER(dispositivo) like 'tableta' OR LOWER(dispositivo) like 'telefono'),
    tiempo_tardado int not null,
    completado boolean not null,
    comentarios varchar(512) not null,
    propuestas varchar(256) not null,
    valoracion int check (valoracion >= 0 AND valoracion <= 10),
    CONSTRAINT FK_USUARIO_TEST_INFO FOREIGN KEY (idUsuario) REFERENCES user_info(id) 
);

drop table if exists consideration
create table consideration(
    idUsuario varchar(12) not null,
    comentarios varchar(512) not null,
    CONSTRAINT PK_CONSIDERATION_IDUSUARIO PRIMARY KEY (idUsuario),
    CONSTRAINT FK_CONSIDERATION_IDUSUARIO FOREIGN KEY (idUsuario) REFERENCES user_info(id)
);