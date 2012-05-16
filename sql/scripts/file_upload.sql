drop table if exists file_upload_classes;
create table file_upload_classes (
	class varchar(255) not null primary key,
	title varchar(255) not null unique,
	icon varchar(255) not null,
	extension varchar(16) not null
) engine InnoDB;
insert into file_upload_classes values
('Oxygen_Common_FileUpload_XLS','Excel file','xls','xls'),
('Oxygen_Common_FileUpload_XML','Simple XML','xml','xml'),
('Oxygen_Common_FileUpload_CSV','CSV file','csv','csv'),
('Oxygen_Common_FileUpload_JSON','JSON file','json','json'),
('Oxygen_Common_FileUpload_YAML','YAML file','yaml','haml');

drop table if exists file_upload_formats;
create table file_upload_formats (
	id int not null primary key,
	title varchar(255) not null,
	handler_class varchar(255) not null,
	handler_args text not null,
	index(handler_class)
) engine InnoDB;
insert into file_upload_formats values
(1,'Excel file','Oxygen_Common_FileUpload_XLS','{}'),
(2,'XML file','Oxygen_Common_FileUpload_XML','{"inferSchema":true}'),
(3,'CSV file (Comma)','Oxygen_Common_FileUpload_CSV','{"delimiter":",", "enclosure":"\\""}, "escape":"\\\\"'),
(4,'CSV file (Semicolon)','Oxygen_Common_FileUpload_CSV','{"delimiter":";", "enclosure":"\\""}, "escape":"\\\\"'),
(5,'CSV file (Tilde)','Oxygen_Common_FileUpload_CSV','{"delimiter":"~", "enclosure":""}, "escape":"\\\\"');

drop table if exists file_upload_files;
create table file_upload_files (
	id int not null primary key,
	file_name varchar(255) not null,
   uploader_id int not null,
	uploaded_at int unsigned not null,
	format_id int not null,
	skip_leading int not null,
	skip_trailing int not null,
	header_row int not null,
	index(format_id)
) engine InnoDB;

drop table if exists file_upload_rows;
create table file_upload_rows (
	id int not null primary key,
	file_id int not null,
	row_number int not null,
	original_text text not null,
	parsed_json text not null,
	parse_status tinyint(1) not null,
	column_count int not null,
	unique(file_id, row_number),
	index(file_id)
) engine InnoDB;

