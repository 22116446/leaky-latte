-- Total orders
SELECT COUNT(*) FROM orders;

-- Total revenue
SELECT SUM(total_price) FROM orders;

-- customer who spent more that average ;

SELECT customer_email, SUM(total_price) AS total_spent
FROM orders
GROUP BY customer_email
HAVING SUM(total_price) >
(
    SELECT AVG(customer_total)
    FROM (
        SELECT SUM(total_price) AS customer_total
        FROM orders
        GROUP BY customer_email
    ) AS totals
);

--most frequently ordered;

SELECT product_name, SUM(quantity) AS total_ordered
FROM order_items
GROUP BY product_name
HAVING SUM(quantity) =
(
    SELECT MAX(total_qty)
    FROM (
        SELECT SUM(quantity) AS total_qty
        FROM order_items
        GROUP BY product_name
    ) AS q
);


--Orders whose total is ABOVE the overall average order value
SELECT id, customer_email, total_price
FROM orders
WHERE total_price >
(
    SELECT AVG(total_price)
    FROM orders
);
--customers ho placed more orders than the average customer
SELECT customer_email, COUNT(*) AS order_count
FROM orders
GROUP BY customer_email
HAVING COUNT(*) >
(
    SELECT AVG(order_count)
    FROM (
        SELECT COUNT(*) AS order_count
        FROM orders
        GROUP BY customer_email
    ) AS counts
);
--products that ere never ordered
SELECT name
FROM menu_items
WHERE name NOT IN
(
    SELECT DISTINCT product_name
    FROM order_items
);

