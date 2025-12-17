INSERT INTO users (username, email, password_hash, role)
VALUES
('admin', 'admin@cafe.com', '1234', 'admin'),
('staff1', 'staff@cafe.com', '1234', 'staff'),
('john', 'john@mail.com', '1234', 'customer');
UPDATE orders SET total_price = 25.00 WHERE id = 1;
DELETE FROM order_items WHERE id = 1;

