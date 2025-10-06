-- KTS Aquarium and Pets Database Schema
CREATE DATABASE IF NOT EXISTS kts_aquarium;
USE kts_aquarium;

-- Users table for customers and admin
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(200) NOT NULL,
    product_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image_url VARCHAR(500),
    stock_quantity INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    order_status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order items table
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Cart table for guest users
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    product_id INT,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Insert default admin user
INSERT INTO users (name, email, mobile, password, user_type) VALUES 
('Admin', 'admin@ktsaquarium.com', '9597203715', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample categories
INSERT INTO categories (category_name, category_description) VALUES 
('Aquariums', 'Various sizes and types of aquariums'),
('Fish', 'Freshwater and saltwater fish'),
('Fish Food', 'High-quality fish food and supplements'),
('Aquarium Equipment', 'Filters, heaters, pumps, and accessories'),
('Plants', 'Live and artificial aquarium plants'),
('Accessories', 'Decorations, gravel, and other accessories');

-- Insert sample products
INSERT INTO products (product_name, product_description, price, category_id, stock_quantity) VALUES 
('Premium Glass Aquarium 20L', 'High-quality glass aquarium perfect for beginners', 2500.00, 1, 10),
('Goldfish - Comet', 'Beautiful orange comet goldfish', 150.00, 2, 25),
('Tropical Fish Food', 'Nutritious food for tropical fish', 200.00, 3, 50),
('Aquarium Filter 1000L/H', 'Powerful filter for medium to large aquariums', 1200.00, 4, 15),
('Live Java Moss', 'Easy to grow live plant for aquariums', 100.00, 5, 30),
('Colorful Gravel Mix', 'Decorative gravel in various colors', 300.00, 6, 40);