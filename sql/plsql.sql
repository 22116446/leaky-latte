DELIMITER //

CREATE TRIGGER before_order_insert
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
  SET NEW.created_at = NOW();
END//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE total_revenue()
BEGIN
  SELECT SUM(total_price) AS total_revenue FROM orders;
END//
DELIMITER ;

