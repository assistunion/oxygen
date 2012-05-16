drop table if exists auth_role_classes;
create table auth_role_classes (
    class varchar(255) not null,
    title varchar(255) not null unique,
    icon varchar(255) not null
) engine = InnoDB;
insert into auth_role_classes values
('Oxygen_Common_Auth_Role_Admin','Admin','user_red'),
('Oxygen_Common_Auth_Role_AdminAudit','Admin (Audit)','user_red'),
('Oxygen_Common_Auth_Role_User','User','user_blue'),
('Oxygen_Common_Auth_Role_UserAudit','User (Audit)','user_blue'),
('Oxygen_Common_Auth_Role_ScopeAdmin','Scope Admin','user_green'),
('Oxygen_Common_Auth_Role_ScopeAdminAudit','Scope Admin (Audit)','user_blue'),
('Oxygen_Common_Auth_Role_ScopeUser','Scope User','user_green'),
('Oxygen_Common_Auth_Role_ScopeUserAudit','Scope User (Audit)','user_blue');

drop table if exists auth_roles;
create table auth_roles (
	id int not null primary key,
	role varchar(64) unique,
   role_class varchar(255) not null,
	role_args text not null,
	creator_id int not null,
	modifier_id int not null,
	created_at int unsigned not null,
	modified_at int unsigned not null,
   index(role_class)
) engine = InnoDB;
insert into auth_roles values
(1,'Admin','Oxygen_Common_Auth_Role_Admin','{}',1,1,unix_timestamp(now()),unix_timestamp(now()));

drop table if exists auth_users;
create table auth_users (
	id int primary key,
	login varchar(64) unique,
	first_name varchar(255) not null,
	last_name varchar(255) not null,
	email varchar(255) not null,
	active tinyint(1) not null default 0,
	activation_key varchar(255) not null,
   creator_id int not null,
	modifier_id int not null,
	created_at int unsigned not null,
	modified_at int unsigned not null,
	password varchar(40) not null
)  engine InnoDB;
insert into auth_users values
(1,'admin','Oxygen', 'Admin', 'admin@example.com', 1,'',1,1,unix_timestamp(now()),unix_timestamp(now()),'');


drop table if exists auth_user_roles;
create table auth_user_roles (
	user_id int not null,
	role_id int not null,
	role_args text not null,
    creator_id int not null,
	modifier_id int not null,
	created_at int unsigned not null,
	modified_at int unsigned not null,
	primary key(user_id, role_id)
) engine InnoDB;
insert into auth_user_roles values
(1,1,'{}',1,1,unix_timestamp(now()),unix_timestamp(now()));


