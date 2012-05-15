create table roles (
	role varchar(64) not null primary key,
	role_args text not null
) engine InnoDB, default charset = utf8, collate = utf8_unicode_ci;

create table user_roles (
	login varchar(64) not null,
	role varchar(64) not null,
	primary key(login, role),
	role_args text not null
) engine InnoDB, default charset = utf8, collate = utf8_unicode_ci;

create table users (
	login varchar(64) primary key,
	first_name varchar(255) not null,
	last_name varchar(255) not null,
	email varchar(255) not null,
	admin tinyint(1) not null default 0,
	active tinyint(1) not null default 0,
	activation_key varchar(255) not null,
	password varchar(40) not null
)  engine InnoDB, default charset = utf8, collate = utf8_unicode_ci;