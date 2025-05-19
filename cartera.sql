CREATE TABLE `cias` (
  cia int(11) NOT NULL primary key AUTO_INCREMENT,
  razon varchar(70),
  direc varchar(70),
  direc2 varchar(70),
  nomfis varchar(70),
  telefono varchar(70),
  fax varchar(70),
  status varchar(1),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  rfc varchar(70)
);


create table vendedores (
	id integer not null primary key AUTO_INCREMENT,
	codigo	varchar(4),
	nombre	varchar(100),
	cia	integer,
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
	
	unique (cia, codigo)
);

create table clientes (
	id integer not null primary key AUTO_INCREMENT,
	codigo  varchar(10) not null,
	appat		varchar(100),
	apmat		varchar(100),
	nombre1		varchar(100),
	nombre2		varchar(100),
	direccion	varchar(100),
	idciudad		integer,
	rfc		varchar(20),
	codpostal	varchar(20),
	cia		integer,
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
	unique ( cia, codigo)
	
);


create table proveedor (
	id integer not null primary key AUTO_INCREMENT,
	codigo  varchar(10) not NULL,
	appat		varchar(100),
	apmat		varchar(100),
	nombre1		varchar(100),
	nombre2		varchar(100),
	direccion	varchar(100),
	idciudad		integer,
	rfc		varchar(20),
	codpostal	varchar(20),
	cia		integer,
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
	unique ( cia, codigo)
	
);



create table inven (
	idart 		integer not null primary key AUTO_INCREMENT,
	codigo		varchar(20),
	codbarras	varchar(20),
	descri		varchar(100),
	descrilarga	varchar(200),
	idlinea		integer,
	idgrupo		integer,
	preciovta	double precision,
	costou		double precision,
	series		varchar(1),
	inicial		integer,
	entran		integer,
	salen		integer,
	exist		integer,
	cia		integer,
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
	unique (cia, codigo)

);

CREATE TABLE almacenes (
	id integer not null primary key AUTO_INCREMENT,
  clave varchar(4) NOT NULL,
  nombre varchar(100) NOT NULL,
  direc varchar(100) NOT NULL,
  ciudad varchar(100) NOT NULL,
  estado varchar(100) NOT NULL,
  status varchar(1) NOT NULL,
  cia integer NOT NULL,
  unique  (cia, clave)
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
);


CREATE TABLE exist (
	id integer not null primary key AUTO_INCREMENT,
  idart		integer not null,
  idalm		integer not null,
  inicial	integer,
  entran	integer,
  salen		integer,
  exist		integer,
  cia		integer,
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
);


CREATE TABLE movart (
	id integer not null primary key AUTO_INCREMENT,
  idart		integer not null,
  idalm		integer not null,
  fecha		date,
  entosal	varchar(1),
  concepto	varchar(100),
  canti		integer,
  iddocto		integer,
  costou	double precision,
  importe	double precision,
  
  cia		integer,
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)

);

CREATE TABLE series (
	id integer not null primary key AUTO_INCREMENT,
  serie varchar(100) NOT NULL,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)
);


create table entradas (
	id integer not null primary key AUTO_INCREMENT,
	idalm integer,
	folio	integer,
	tipo	integer,
	idprove		integer,
	idrecibe	integer,
	fecha		date,
	factura		varchar(30),
	importe		double precision,
	iva		double precision,
	idusuario	integer,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)

);

create table renentra (
	id integer not null primary key AUTO_INCREMENT,
	identrada	integer,
	idrecibe	integer,
	idart		integer,
	conse		integer,
	canti		integer,
	piva		double precision,
	importe		double precision,
	iva		double precision,
	total		double precision,
	idusuario	integer,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)

);

create table seriesmov (
	id integer not null primary key AUTO_INCREMENT,
	idserie not null integer,
	tipo	integer,
	idrenentra	integer,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)

);

create table facturas (
	id integer not null primary key AUTO_INCREMENT,
	tipo	integer,
	folio	integer,
	serie	varchar(20),
	idcliente	integer,
	fecha	date,
	vence	date,
	idprove		integer,
	importe		double precision,
	iva		double precision,
	total		double precision,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)

);

create table renfac (
	id integer not null primary key AUTO_INCREMENT,
	idfactura integer,
	idart		integer,
	consec		integer,
	concepto	varchar(100),
	preciou		double precision
	costou		double precision
	canti		integer,
	tipo		integer,
	poriva		double precision,
	pordesc		double precision,
	importe		double precision,
	iva		double precision,
	total		double precision,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)

);

create table movclis (
	id integer not null primary key AUTO_INCREMENT,
	idcliente not null integer,
	tipo	integer,
	docto	integer,
	fecha	date,
	letra	integer,
	vence	integer,
	importe	double precision,
	saldo	double precision
	fechasaldo	date,
	idusuario	integer,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)

);

create table movpro (
	id integer not null primary key AUTO_INCREMENT,
	idprove not null integer,
	tipo	integer,
	docto	integer,
	fecha	date,
	letra	integer,
	vence	integer,
	importe	double precision,
	saldo	double precision
	fechasaldo	date,
	idusuario	integer,
  cia integer NOT NULL DEFAULT 1,
  updated_at timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  created_at timestamp(6) NOT NULL DEFAULT current_timestamp(6)

);

