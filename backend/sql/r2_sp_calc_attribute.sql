DELIMITER $$

DROP PROCEDURE IF EXISTS sp_calc_attribute$$

CREATE PROCEDURE sp_calc_attribute()
BEGIN
   DECLARE done INT;
   DECLARE Vclass_id INT;
   DECLARE Vattribute_id INT;
   DECLARE Vattribute_stat_id INT;
   DECLARE Vstat INT;

   DECLARE cur_attribute CURSOR FOR
   SELECT sr.class_id, av.attribute_id, as.attribute_stat_id, COUNT(av.attribute_value_id)
      FROM ak_search_round sr
      INNER JOIN ak_search_round_answer sra ON (sr.search_round_id = sra.search_round_id)
      INNER JOIN ak_attribute_value av ON (sra.attribute_value_id = av.attribute_value_id)

      LEFT JOIN ak_attribute_stat `as` ON (as.class_id = sr.class_id) AND (as.attribute_id = sr.attribute_id)
      GROUP BY sr.class_id, av.attribute_id, as.attribute_stat_id;

   DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

   SET done = 0;

   OPEN cur_attribute;

   REPEAT
      FETCH cur_attribute INTO Vclass_id, Vattribute_id, Vattribute_stat_id, Vstat;

      IF NOT done THEN
         IF Vattribute_stat_id IS NULL THEN
 	         INSERT INTO ak_attribute_stat (class_id, attribute_id, stat) VALUES (Vclass_id, Vattribute_id, Vstat);
	      ELSE
            UPDATE ak_attribute_stat SET stat = Vstat WHERE (attribute_stat_id = Vattribute_stat_id);
         END IF;
      END IF;

   UNTIL done END REPEAT;

   CLOSE cur_attribute;

END$$

DELIMITER ;

