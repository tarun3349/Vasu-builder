-- KTS Aquarium and Pets E-commerce Database Schema
CREATE DATABASE IF NOT EXISTS kts_aquarium;
USE kts_aquarium;

-- Admin table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image_url VARCHAR(500),
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Customers table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    full_name VARCHAR(255),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'received', 'shipped', 'delivered') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_address TEXT,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart table (for guest users)
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO admins (email, password) VALUES ('admin@ktsaquarium.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Aquariums', 'Various sizes and types of aquariums'),
('Fish', 'Freshwater and saltwater fish'),
('Accessories', 'Filters, heaters, decorations, and more'),
('Food', 'Fish food and supplements'),
('Plants', 'Aquatic plants for aquariums');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, stock_quantity) VALUES 
('Premium Glass Aquarium 50L', 'High-quality glass aquarium perfect for beginners', 2999.00, 1, 10),
('Goldfish - Comet', 'Beautiful orange comet goldfish', 150.00, 2, 25),
('Aquarium Filter System', 'Powerful filtration system for clean water', 899.00, 3, 15),
('Tropical Fish Food', 'Nutritious food for tropical fish', 199.00, 4, 50),
('Java Moss Plant', 'Easy-to-grow aquatic plant', 99.00, 5, 30);