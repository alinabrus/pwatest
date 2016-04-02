DROP TABLE IF EXISTS es_media;
DROP TABLE IF EXISTS es_good_stake;
DROP TABLE IF EXISTS es_forum_messages;
DROP TABLE IF EXISTS es_forum_subjects_object;
DROP TABLE IF EXISTS es_forum_subjects;
DROP TABLE IF EXISTS es_forums;
DROP TABLE IF EXISTS es_goods ;


CREATE TABLE es_goods (                                          
  goods_id INT NOT NULL AUTO_INCREMENT,
  users_id int(11) unsigned NOT NULL,
  goods_name VARCHAR(200) NOT NULL,
  the_count DECIMAL(20,6) NOT NULL DEFAULT 1,
	PRIMARY KEY (goods_id),
	UNIQUE INDEX unique_constraint (goods_name ASC, users_id ASC),
	INDEX users_id (users_id),
  CONSTRAINT es_goods_users_fk
    FOREIGN KEY (users_id )
    REFERENCES es_user_ion_users (id )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE es_forums (                                          
  forums_id INT NOT NULL AUTO_INCREMENT,
  user_groups_id int(11) unsigned NULL,
  users_id int(11) unsigned NOT NULL,
  forum_name VARCHAR(200) NOT NULL,
  category ENUM('forum', 'sale', 'blog', 'group', 'user', 'object', 'class', 'media') NOT NULL,
	PRIMARY KEY (forums_id),
	INDEX user_groups_id (user_groups_id ASC),
	INDEX users_id (users_id),
	UNIQUE INDEX forum_name (category, forum_name, user_groups_id, users_id),
  CONSTRAINT es_forums_user_groups_fk
    FOREIGN KEY (user_groups_id )
    REFERENCES es_user_ion_users_groups (id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_forums_users_fk
    FOREIGN KEY (users_id )
    REFERENCES es_user_ion_users (id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE es_forum_subjects (                                          
	forum_subjects_id INT NOT NULL AUTO_INCREMENT,
	forums_id INT NOT NULL,
	goods_id INT NULL,
	forum_subjects_name VARCHAR(200) NOT NULL,
	view_counter int(5) NOT NULL DEFAULT '0',
	PRIMARY KEY (forum_subjects_id),
	UNIQUE INDEX unique_constraint (forum_subjects_name, forums_id),
	INDEX forums_id (forums_id),
	INDEX goods_id (goods_id),
  CONSTRAINT es_forum_subjects_forums_fk
    FOREIGN KEY (forums_id )
    REFERENCES es_forums (forums_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_forum_subjects_goods_fk
    FOREIGN KEY (goods_id )
    REFERENCES es_goods (goods_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE es_forum_subjects_object (
  forum_subjects_object_id INT NOT NULL AUTO_INCREMENT,
  forum_subjects_id INT NOT NULL,
  object_id INT unsigned NOT NULL, 
  PRIMARY KEY(forum_subjects_object_id),
  INDEX object_id (object_id), 
  UNIQUE INDEX unique_constarint (forum_subjects_id ASC, object_id ASC),
  CONSTRAINT es_forum_subjects_object_object_fk
    FOREIGN KEY (object_id)
    REFERENCES ak_object (object_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT es_forum_subjects_object_forum_subjects_fk
    FOREIGN KEY (forum_subjects_id)
    REFERENCES es_forum_subjects (forum_subjects_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE es_forum_messages (                                          
  forum_messages_id INT NOT NULL AUTO_INCREMENT,
  forum_subjects_id INT NOT NULL,
  users_id int(11) unsigned NOT NULL,
  messages TEXT NOT NULL,
  created_time DATETIME NOT NULL,
	PRIMARY KEY (forum_messages_id),
--	UNIQUE INDEX a (),
	INDEX forum_subjects_id (forum_subjects_id),
	INDEX users_id (users_id),
	INDEX created_time (created_time),
  CONSTRAINT es_forum_subjects_forum_subjects_fk
    FOREIGN KEY (forum_subjects_id )
    REFERENCES es_forum_subjects (forum_subjects_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_forum_subjects_users_fk
    FOREIGN KEY (users_id )
    REFERENCES es_user_ion_users (id )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE es_good_stake (                                          
  good_sales_id INT NOT NULL AUTO_INCREMENT,
  goods_id INT NOT NULL,
  forum_messages_id INT NOT NULL,
  the_price DECIMAL(20,2) NOT NULL,
	PRIMARY KEY (good_sales_id),
	INDEX goods_id (goods_id),
	UNIQUE INDEX forum_messages_id (forum_messages_id),
  CONSTRAINT es_good_stake_goods_fk
    FOREIGN KEY (goods_id )
    REFERENCES es_goods (goods_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_good_stake_forum_messages_fk
    FOREIGN KEY (forum_messages_id )
    REFERENCES es_forum_messages (forum_messages_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE es_media (                                          
  media_id INT NOT NULL AUTO_INCREMENT,
  user_groups_id int(11) unsigned NULL,
  users_id int(11) unsigned NULL,
  goods_id INT NULL,
  forum_messages_id INT NULL,
  media_name VARCHAR(255) NOT NULL,
  media_link VARCHAR(1024) NOT NULL,
	PRIMARY KEY (media_id),
	UNIQUE INDEX unique_constraint (media_name ASC, user_groups_id ASC, users_id ASC, goods_id ASC, forum_messages_id ASC),
  INDEX forum_messages_id (forum_messages_id),
	INDEX user_groups_id (user_groups_id ASC),
	INDEX users_id (users_id),
	INDEX goods_id (goods_id),
  CONSTRAINT es_media_user_groups_fk
    FOREIGN KEY (user_groups_id )
    REFERENCES es_user_ion_users_groups (id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_media_users_fk
    FOREIGN KEY (users_id )
    REFERENCES es_user_ion_users (id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_media_goods_fk
    FOREIGN KEY (goods_id )
    REFERENCES es_goods (goods_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT es_media_forum_messages_fk
    FOREIGN KEY (forum_messages_id )
    REFERENCES es_forum_messages (forum_messages_id )
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;
