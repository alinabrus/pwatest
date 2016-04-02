DELIMITER $$

DROP PROCEDURE IF EXISTS sp_calc_class$$

CREATE PROCEDURE sp_calc_class (
--  IN in_session_id VARCHAR(40)
)
BEGIN
   DECLARE done INT;
   DECLARE Vclass_id INT;
   DECLARE Vclass_stat_id INT;
   DECLARE Vstat INT;

   DECLARE cur_class CURSOR FOR
   SELECT sr.class_id, s.class_stat_id, COUNT(sr.search_round_id)
      FROM ak_search_round sr
      LEFT JOIN ak_class_stat s ON (s.class_id = sr.class_id)
      GROUP BY sr.class_id, s.class_id;

   DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

   SET done = 0;

   OPEN cur_class;

   REPEAT
      FETCH cur_class INTO Vclass_id, Vclass_stat_id, Vstat;

      IF NOT done THEN
         IF Vclass_stat_id IS NULL THEN
 	         INSERT INTO ak_class_stat (class_id, stat) VALUES ( Vclass_id, Vstat);
	      ELSE
            UPDATE ak_class_stat SET stat = Vstat WHERE (class_stat_id = Vclass_stat_id);
         END IF;
      END IF;

   UNTIL done END REPEAT;

   CLOSE cur_class;

END$$

DELIMITER ;

