-- Create database
CREATE DATABASE IF NOT EXISTS electronic_ordering_system;
USE electronic_ordering_system;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order details table
CREATE TABLE order_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert default admin user (password: Admin1234)
-- Use password_hash('Admin1234', PASSWORD_DEFAULT) to get the hash
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@eos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample products with Tanzanian Shillings (TZS) prices
INSERT INTO products (name, description, price, stock_quantity, category) VALUES
('iPhone 15', 'Latest Apple smartphone', 2500000, 50, 'Mobile Phones'),
('Samsung Galaxy S24', 'Android flagship phone', 2200000, 30, 'Mobile Phones'),
('Tecno Spark 20', 'Budget smartphone', 450000, 100, 'Mobile Phones'),
('Infinix Hot 40', 'Gaming smartphone', 750000, 80, 'Mobile Phones'),
('MacBook Pro', 'Apple laptop for professionals', 5000000, 20, 'Laptops'),
('Dell XPS 13', 'Windows ultrabook', 3250000, 25, 'Laptops'),
('HP Pavilion', 'Student laptop', 1200000, 40, 'Laptops'),
('Lenovo ThinkPad', 'Business laptop', 2800000, 35, 'Laptops'),
('AirPods Pro', 'Wireless noise cancelling earbuds', 625000, 100, 'Accessories'),
('Logitech Mouse', 'Wireless computer mouse', 125000, 200, 'Accessories'),
('Keyboard USB', 'Computer keyboard', 85000, 150, 'Accessories'),
('Power Bank 20000mAh', 'Portable charger', 120000, 120, 'Accessories'),
('Samsung Monitor 24"', 'Full HD monitor', 1000000, 40, 'Monitors'),
('LG Monitor 27"', '4K monitor', 1500000, 25, 'Monitors'),
('Apple Watch', 'Smartwatch with health features', 1000000, 60, 'Wearables'),
('Mi Band 7', 'Fitness tracker', 150000, 200, 'Wearables');

-- Insert sample customer
INSERT INTO users (username, email, password, role) VALUES 
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Insert sample order
INSERT INTO orders (user_id, total_amount, status) VALUES 
(2, 3125000, 'delivered');

INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES
(1, 1, 1, 2500000, 2500000),
(1, 9, 1, 625000, 625000);