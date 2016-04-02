
DROP TABLE IF EXISTS ak_current_round_stat;
DROP TABLE IF EXISTS ak_search_round_answer;
DROP TABLE IF EXISTS ak_search_round;
DROP TABLE IF EXISTS ak_class_object;
DROP TABLE IF EXISTS ak_object_attribute_value;
DROP TABLE IF EXISTS ak_class_attribute_value;
DROP TABLE IF EXISTS ak_attribute_value;
DROP TABLE IF EXISTS ak_object_description_history;
DROP TABLE IF EXISTS ak_classes_relation;
DROP TABLE IF EXISTS ak_classes_description_history;
DROP TABLE IF EXISTS ak_class;
DROP TABLE IF EXISTS ak_object;
DROP TABLE IF EXISTS ak_attribute;

CREATE TABLE ak_attribute (
  attribute_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  attribute_name VARCHAR(255) NOT NULL, 
  attribute_description TEXT NULL, 
  PRIMARY KEY(attribute_id),
  UNIQUE INDEX unique_constarint (attribute_name ASC)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_class (
  class_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  class_name VARCHAR(250) NOT NULL, 
  class_description TEXT NULL, 
  PRIMARY KEY(class_id),
  UNIQUE INDEX unique_constarint (class_name ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE ak_classes_description_history ( 
  classes_description_history_id INT UNSIGNED NOT NULL AUTO_INCREMENT, 
  class_id INT UNSIGNED NOT NULL,
  modification_time DATETIME NOT NULL,
  old_class_description TEXT NULL, 
  new_class_description TEXT NULL, 
  PRIMARY KEY(classes_description_history_id), 
  INDEX class_id (class_id), 
  CONSTRAINT ak_classes_description_history_class_fk
    FOREIGN KEY (class_id)
    REFERENCES ak_class (class_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_classes_relation ( 
  classes_relation_id INT UNSIGNED NOT NULL AUTO_INCREMENT, 
  source_class_id INT UNSIGNED NOT NULL,
  target_class_id INT UNSIGNED NOT NULL,
  classes_relation ENUM('equivalent to','subclass of') NOT NULL,
  PRIMARY KEY(classes_relation_id), 
  INDEX source_class_id (source_class_id), 
  INDEX target_class_id (target_class_id), 
  CONSTRAINT ak_classes_relation_source_class_fk
    FOREIGN KEY (source_class_id)
    REFERENCES ak_class (class_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT ak_classes_relation_target_class_fk
    FOREIGN KEY (target_class_id)
    REFERENCES ak_class (class_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_object (
  object_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  object_name VARCHAR(255) NULL, 
  object_comment TEXT NULL, 
  PRIMARY KEY(object_id),
  UNIQUE INDEX unique_constarint (object_name ASC)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_object_description_history ( 
  object_description_history_id INT UNSIGNED NOT NULL AUTO_INCREMENT, 
  object_id INT UNSIGNED NOT NULL,
  modification_time DATETIME NOT NULL,
  old_class_description TEXT NULL, 
  new_class_description TEXT NULL, 
  PRIMARY KEY(object_description_history_id), 
  INDEX object_id (object_id), 
  CONSTRAINT ak_object_description_history_object_fk
    FOREIGN KEY (object_id)
    REFERENCES ak_object (object_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_attribute_value (
  attribute_value_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  attribute_id INT UNSIGNED NOT NULL, 
  attribute_value VARCHAR(255) NULL,
  attribute_value_class_id INT UNSIGNED NULL, 
  attribute_value_object_id INT UNSIGNED NULL,
  PRIMARY KEY(attribute_value_id),
  INDEX attribute_id (attribute_id),
  INDEX attribute_value_class_id (attribute_value_class_id), 
  INDEX attribute_value_object_id (attribute_value_object_id),
  UNIQUE INDEX unique_constarint (attribute_id ASC, attribute_value ASC, attribute_value_class_id ASC, attribute_value_object_id ASC),
  CONSTRAINT ak_attribute_value_attribute_fk
    FOREIGN KEY (attribute_id)
    REFERENCES ak_attribute (attribute_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT ak_attribute_value_object_fk
    FOREIGN KEY (attribute_value_object_id)
    REFERENCES ak_object (object_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT ak_attribute_value_ak_class_fk
    FOREIGN KEY (attribute_value_class_id)
    REFERENCES ak_class (class_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_class_attribute_value (
  class_attribute_value_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  class_id INT UNSIGNED NOT NULL, 
  attribute_value_id INT UNSIGNED NOT NULL, 
  PRIMARY KEY(class_attribute_value_id),
--  INDEX class_id (class_id),
  INDEX attribute_value_id (attribute_value_id), 
  UNIQUE INDEX unique_constarint (class_id ASC, attribute_value_id ASC),
  CONSTRAINT ak_class_attribute_value_attribute_value_fk
    FOREIGN KEY (attribute_value_id)
    REFERENCES ak_attribute_value (attribute_value_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT ak_class_attribute_value_class_fk
    FOREIGN KEY (class_id)
    REFERENCES ak_class (class_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_object_attribute_value (
  object_attribute_value_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  object_id INT UNSIGNED NOT NULL, 
  attribute_value_id INT UNSIGNED NOT NULL,
  PRIMARY KEY(object_attribute_value_id),
--  INDEX object_id (object_id), 
  INDEX attribute_value_id (attribute_value_id), 
  UNIQUE INDEX unique_constarint (object_id ASC, attribute_value_id ASC),
  CONSTRAINT ak_object_attribute_value_attribute_value_fk
    FOREIGN KEY (attribute_value_id)
    REFERENCES ak_attribute_value (attribute_value_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT ak_object_attribute_value_object_fk
    FOREIGN KEY (object_id)
    REFERENCES ak_object (object_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_class_object (
  class_object_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  class_id INT UNSIGNED NOT NULL,
  object_id INT UNSIGNED NOT NULL, 
  PRIMARY KEY(class_object_id),
--  INDEX class_id (class_id), 
  INDEX object_id (object_id), 
  UNIQUE INDEX unique_constarint (class_id ASC, object_id ASC),
  CONSTRAINT ak_class_object_object_fk
    FOREIGN KEY (object_id)
    REFERENCES ak_object (object_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT ak_class_object_class_fk
    FOREIGN KEY (class_id)
    REFERENCES ak_class (class_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_search_round (
  search_round_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  class_id INT UNSIGNED NOT NULL,
  time_round DATETIME NOT NULL, -- ???
  PRIMARY KEY(search_round_id),
--  UNIQUE INDEX unique_constarint (class_id ASC, time_round ASC),
  CONSTRAINT ak_search_round_class_fk
    FOREIGN KEY (class_id)
    REFERENCES ak_class (class_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_search_round_answer (
  search_round_answer_id INT UNSIGNED NOT NULL, 
  search_round_id INT UNSIGNED NOT NULL,
  attribute_id INT UNSIGNED NOT NULL, -- !!!
  attribute_value_id INT UNSIGNED NOT NULL,
  UNIQUE INDEX unique_constarint (search_round_id ASC, attribute_id ASC),
  INDEX search_round_id (search_round_id ASC), 
  INDEX attribute_value_id (attribute_value_id ASC),
  CONSTRAINT ak_search_round_answer_search_round_fk
    FOREIGN KEY (search_round_id)
    REFERENCES ak_search_round (search_round_id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT ak_search_round_answer_attribute_value_fk
    FOREIGN KEY (attribute_value_id)
    REFERENCES ak_attribute_value (attribute_value_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT ak_search_round_answer_attribute_fk
    FOREIGN KEY (attribute_id)
    REFERENCES ak_attribute (attribute_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

CREATE TABLE ak_current_round_stat ( 
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  class_id INT UNSIGNED NOT NULL, 
  search_round_id INT NULL, 
  attribute_stat DOUBLE NULL, 
  class_stat DOUBLE NULL, 
  multiplication DOUBLE NULL, 
  end_stat DOUBLE NULL, 
  PRIMARY KEY(id),
  INDEX class_id (class_id),
  CONSTRAINT ak_current_round_stat_class_fk
    FOREIGN KEY (class_id)
    REFERENCES ak_class (class_id)
    ON DELETE NO ACTION -- ???
    ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;

