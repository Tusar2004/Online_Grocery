-- Create the database
CREATE DATABASE IF NOT EXISTS online_grocery;
USE online_grocery;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Create transactions table
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL
);

-- Create transaction_items table
CREATE TABLE IF NOT EXISTS transaction_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Insert sample data
INSERT INTO categories (category_name, description) VALUES
('Fruits', 'Fresh fruits and berries'),
('Vegetables', 'Organic vegetables'),
('Dairy', 'Milk, cheese, and other dairy products'),
('Bakery', 'Bread, cakes, and pastries'),
('Meat', 'Fresh meat and poultry');

INSERT INTO products (product_name, category_id, price, stock_quantity, description, image_path) VALUES
('Apple', 1, 0.99, 100, 'Fresh red apples', 'https://cdn.pixabay.com/photo/2016/01/05/13/58/apple-1122537_640.jpg'),
('Banana', 1, 0.59, 150, 'Ripe bananas', 'https://cdn.pixabay.com/photo/2017/06/27/22/21/banana-2449019_640.jpg'),
('Carrot', 2, 0.79, 80, 'Organic carrots', 'https://cdn.pixabay.com/photo/2018/08/31/19/13/carrots-3645370_640.jpg'),
('Milk', 3, 2.49, 50, 'Whole milk 1L', 'https://cdn.pixabay.com/photo/2017/07/05/15/41/milk-2474993_640.jpg'),
('Bread', 4, 1.99, 40, 'Whole wheat bread', 'https://cdn.pixabay.com/photo/2014/07/22/09/59/bread-399286_640.jpg');

INSERT INTO transactions (transaction_date, total_amount) VALUES
(NOW() - INTERVAL 5 DAY, 15.95),
(NOW() - INTERVAL 3 DAY, 8.47),
(NOW() - INTERVAL 1 DAY, 23.50);

INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price) VALUES
(1, 1, 5, 0.99),
(1, 3, 2, 0.79),
(1, 4, 1, 2.49),
(2, 2, 3, 0.59),
(2, 5, 2, 1.99),
(3, 1, 10, 0.99),
(3, 4, 3, 2.49),
(3, 5, 1, 1.99);