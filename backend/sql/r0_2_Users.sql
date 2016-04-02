
DROP TABLE IF EXISTS es_user_relations;
DROP TABLE IF EXISTS es_user_attributes;
DROP TABLE IF EXISTS es_attributes;

DROP TABLE IF EXISTS es_user_ion_login_attempts;
DROP TABLE IF EXISTS es_user_ion_users_groups;
DROP TABLE IF EXISTS es_user_ion_groups;
DROP TABLE IF EXISTS es_user_ion_users;


################## CodeIgniter-Ion-Auth <<<

CREATE TABLE es_user_ion_groups (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(20) NOT NULL,
  description varchar(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX group_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO es_user_ion_groups (id, name, description) VALUES
     (1,'admin','Administrator'),
     (2,'user','User');

CREATE TABLE es_user_ion_users (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  ip_address varchar(15) NOT NULL,
  username varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  salt varchar(255) DEFAULT NULL,
  email varchar(100) NOT NULL,
  activation_code varchar(40) DEFAULT NULL,
  forgotten_password_code varchar(40) DEFAULT NULL,
  forgotten_password_time int(11) unsigned DEFAULT NULL,
  remember_code varchar(40) DEFAULT NULL,
  created_on int(11) unsigned NOT NULL,
  last_login int(11) unsigned DEFAULT NULL,
  active tinyint(1) unsigned DEFAULT NULL,
  first_name varchar(50) DEFAULT NULL,
  last_name varchar(50) DEFAULT NULL,
  company varchar(100) DEFAULT NULL,
  phone varchar(20) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO es_user_ion_users (id, ip_address, username, password, salt, email, activation_code, forgotten_password_code, created_on, last_login, active, first_name, last_name, company, phone) VALUES
     ('1','127.0.0.1','administrator','$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36','','admin@admin.com','',NULL,'1268889823','1268889823','1', 'Admin','istrator','ADMIN','0');

CREATE TABLE es_user_ion_users_groups (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  user_id int(11) unsigned NOT NULL,
  group_id mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY fk_es_user_ion_users_groups_user_id_idx (user_id),
  KEY fk_es_user_ion_users_groups_group_id_idx (group_id),
  CONSTRAINT uc_users_groups UNIQUE (user_id, group_id),
  CONSTRAINT fk_es_user_ion_users_groups_user_id FOREIGN KEY (user_id) REFERENCES es_user_ion_users (id) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT fk_es_user_ion_users_groups_group_id FOREIGN KEY (group_id) REFERENCES es_user_ion_groups (id) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO es_user_ion_users_groups (id, user_id, group_id) VALUES
     (1,1,1);


CREATE TABLE es_user_ion_login_attempts (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  ip_address varchar(15) NOT NULL,
  login varchar(100) NOT NULL,
  time int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

################## CodeIgniter-Ion-Auth >>>


CREATE TABLE es_attributes (                                          
  attributes_id INT NOT NULL AUTO_INCREMENT,                             
  attributes_name VARCHAR(200) NOT NULL,
	PRIMARY KEY (attributes_id),
	UNIQUE INDEX attributes_name (attributes_name)
) 
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE es_user_attributes (                                          
  user_attributes_id INT NOT NULL AUTO_INCREMENT,                             
  attributes_id INT NOT NULL,
  users_id int(11) unsigned NOT NULL,
  attribute_value VARCHAR(4000) NOT NULL,
	PRIMARY KEY (user_attributes_id),
	UNIQUE INDEX unique_constraint (attributes_id, users_id),
	INDEX users_id (users_id),
  CONSTRAINT es_user_attributes_attribute_fk
    FOREIGN KEY (attributes_id )
    REFERENCES es_attributes (attributes_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_user_attributes_user_fk
    FOREIGN KEY (users_id )
    REFERENCES es_user_ion_users (id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) 
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE es_user_relations (                                          
  user_relations_id INT NOT NULL AUTO_INCREMENT,                             
  users_id int(11) unsigned NOT NULL,
  to_users_id int(11) unsigned NOT NULL,
  attribute_value VARCHAR(4000) NOT NULL,
  relation_type ENUM('Friend', 'Follow'),
	PRIMARY KEY (user_relations_id),
	UNIQUE INDEX unique_constraint (users_id, to_users_id),
	INDEX to_users_id (to_users_id),
  CONSTRAINT es_user_relations_user_fk
    FOREIGN KEY (users_id )
    REFERENCES es_user_ion_users (id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_user_relations_to_user_fk
    FOREIGN KEY (users_id )
    REFERENCES es_user_ion_users (id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) 
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


