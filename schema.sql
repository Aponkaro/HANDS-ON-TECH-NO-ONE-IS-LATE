CREATE DATABASE IF NOT EXISTS handsontech_db;
USE handsontech_db;

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    debt_balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Print Jobs Table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(150) NOT NULL,
    customer_id INT NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) DEFAULT 0.00,
    total_price DECIMAL(10,2) DEFAULT 0.00,
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Initial Default Customer
INSERT INTO customers (name, phone, email) 
VALUES ('Walk-in Customer', '0246116269', 'info@handsontech.com')
ON DUPLICATE KEY UPDATE id=id;