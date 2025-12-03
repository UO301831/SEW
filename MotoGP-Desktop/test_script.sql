drop table if exists user_info
create table user_info(
    codigo varchar(12) primary key not null,
    profesion varchar(48) not null,
    edad integer check (edad >= 0),
    genero varchar(1) check (genero like 'M' or genero like 'F'),
    pericia integer check (pericia >= 0 AND pericia <= 10)
);

drop table if exists test_info
create table test_info(
    
    tiempo_tardado ... not null,
    completado boolean not null,
    
);