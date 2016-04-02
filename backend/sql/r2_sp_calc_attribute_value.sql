DELIMITER $$

DROP PROCEDURE IF EXISTS sp_calc_attribute_value$$

CREATE PROCEDURE sp_calc_attribute_value()
BEGIN
   DECLARE done INT;
   DECLARE Vclass_id INT;
   DECLARE Vattribute_value_id INT;
   DECLARE Vattribute_value_stat_id INT;
   DECLARE Vstat INT;

   DECLARE cur_attribute_value CURSOR FOR
   SELECT sr.class_id, sra.attribute_value_id, as.attribute_value_stat_id, COUNT(sra.search_round_answer_id)
      FROM ak_search_round sr
      INNER JOIN ak_search_round_answer sra ON (sr.search_round_id = sra.search_round_id)

      LEFT JOIN ak_attribute_value_stat `as` ON (as.class_id = sr.class_id) AND (as.attribute_value_id = sra.attribute_value_id)
      GROUP BY sr.class_id, sra.attribute_value_id, as.attribute_value_stat_id;

   DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

   SET done = 0;

   OPEN cur_attribute_value;

   REPEAT
      FETCH cur_attribute_value INTO Vclass_id, Vattribute_value_id, Vattribute_value_stat_id, Vstat;

      IF NOT done THEN
         IF Vattribute_value_stat_id IS NULL THEN
 	         INSERT INTO ak_attribute_value_stat (class_id, attribute_value_id, stat) VALUES (Vclass_id, attribute_value_id, Vstat);
	      ELSE
            UPDATE ak_attribute_value_stat SET stat = Vstat WHERE (attribute_value_stat_id = Vattribute_value_stat_id);
         END IF;
      END IF;

   UNTIL done END REPEAT;

   CLOSE cur_attribute_value;

END$$

DELIMITER ;

