create table plazos (
    id serial primary key autoincrement,
    idventa integer not null,
    fecha date not null,
    vence date not null,
    idconcepto integer not null,
    idusuario integer not null,
    cia integer not null,
    poliza varchar(4) not null,
    tipoplazo integer not null,
    created_at timestamp default now(),
    updated_at timestamp default now()
);

create table observaciones (
    id serial primary key autoincrement,
    idventa integer not null,
    fecha date not null,
    idconcepto integer not null,
    idusuario integer not null,
    cia integer not null,
    poliza varchar(4) not null,
    tipoplazo integer not null,
    created_at timestamp default now(),
    updated_at timestamp default now()
);
