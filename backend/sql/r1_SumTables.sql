
DROP TABLE IF EXISTS ak_attribute_value_stat;
DROP TABLE IF EXISTS ak_attribute_stat;
DROP TABLE IF EXISTS ak_class_stat;

CREATE TABLE ak_class_stat(
   class_stat_id INT UNSIGNED NOT NULL,
   class_id INT UNSIGNED NOT NULL,
   stat INT NOT NULL DEFAULT 0,
   PRIMARY KEY (class_stat_id),
   UNIQUE INDEX unique_constraint (class_id),
   FOREIGN KEY (class_id) REFERENCES ak_class (class_id) ON DELETE CASCADE
);

CREATE TABLE ak_attribute_stat(
   attribute_stat_id INT UNSIGNED NOT NULL,
   class_id INT UNSIGNED NOT NULL,
   attribute_id INT UNSIGNED NOT NULL,
   stat INT NOT NULL DEFAULT 0,
   PRIMARY KEY (attribute_stat_id),
   UNIQUE INDEX unique_constraint (class_id, attribute_id),
   INDEX attribute_stat_attribute_id (attribute_id),
   FOREIGN KEY (class_id) REFERENCES ak_class (class_id) ON DELETE CASCADE,
   FOREIGN KEY (attribute_id) REFERENCES ak_attribute (attribute_id) ON DELETE CASCADE
);

CREATE TABLE ak_attribute_value_stat(
   attribute_value_stat_id INT UNSIGNED NOT NULL,
   class_id INT UNSIGNED NOT NULL,
   attribute_value_id INT UNSIGNED NOT NULL,
   stat INT NOT NULL DEFAULT 0,
   PRIMARY KEY (attribute_value_stat_id),
   UNIQUE INDEX unique_constraint (class_id, attribute_value_id),
   INDEX attribute_value_stat_attribute_id (attribute_value_id),
   FOREIGN KEY (class_id) REFERENCES ak_class (class_id) ON DELETE CASCADE,
   FOREIGN KEY (attribute_value_id) REFERENCES ak_attribute_value (attribute_value_id) ON DELETE CASCADE
);
