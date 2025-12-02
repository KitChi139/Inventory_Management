-- Inventory Management System Database Setup
-- Run this SQL script in your MySQL/MariaDB database

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Table: userroles
CREATE TABLE IF NOT EXISTS userroles (
    roleID INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(50) NOT NULL UNIQUE
);

-- Insert default roles
INSERT IGNORE INTO userroles (roleName) VALUES 
    ('Admin'),
    ('Employee'),
    ('Supplier');

-- Table: email
CREATE TABLE IF NOT EXISTS email (
    emailID INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE
);

-- Table: employee
CREATE TABLE IF NOT EXISTS employee (
    empID INT AUTO_INCREMENT PRIMARY KEY,
    empNum VARCHAR(50),
    empName VARCHAR(255) NOT NULL
);

-- Table: company
CREATE TABLE IF NOT EXISTS company (
    comID INT AUTO_INCREMENT PRIMARY KEY,
    comName VARCHAR(255) NOT NULL,
    comPerson VARCHAR(255)
);

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    roleID INT NOT NULL,
    status VARCHAR(50) DEFAULT 'Active',
    FOREIGN KEY (roleID) REFERENCES userroles(roleID)
);

-- Table: userinfo
CREATE TABLE IF NOT EXISTS userinfo (
    infoID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    empID INT NULL,
    comID INT NULL,
    emailID INT NOT NULL,
    cont_num VARCHAR(50),
    FOREIGN KEY (userID) REFERENCES users(userID),
    FOREIGN KEY (empID) REFERENCES employee(empID),
    FOREIGN KEY (comID) REFERENCES company(comID),
    FOREIGN KEY (emailID) REFERENCES email(emailID)
);

-- Table: categories
CREATE TABLE IF NOT EXISTS categories (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    Category_Name VARCHAR(255) NOT NULL UNIQUE
);

-- Table: units
CREATE TABLE IF NOT EXISTS units (
    UnitID INT AUTO_INCREMENT PRIMARY KEY,
    UnitName VARCHAR(50) NOT NULL UNIQUE
);

-- Table: products
CREATE TABLE IF NOT EXISTS products (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    ProductName VARCHAR(255) NOT NULL,
    CategoryID INT,
    UnitID INT,
    Price DECIMAL(10, 2) DEFAULT 0.00,
    Min_stock INT DEFAULT 5,
    Max_stock INT DEFAULT 100,
    FOREIGN KEY (CategoryID) REFERENCES categories(CategoryID),
    FOREIGN KEY (UnitID) REFERENCES units(UnitID)
);

-- Table: inventory
CREATE TABLE IF NOT EXISTS inventory (
    InventoryID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    SKU VARCHAR(100),
    BatchNum VARCHAR(100),
    Quantity INT DEFAULT 0,
    ExpirationDate DATE NULL,
    Status VARCHAR(50) DEFAULT 'In Stock',
    FOREIGN KEY (ProductID) REFERENCES products(ProductID)
);

-- Table: suppliers
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    category VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Active'
);

-- Table: requests
CREATE TABLE IF NOT EXISTS requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    quantity INT NOT NULL,
    requester VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_approved TIMESTAMP NULL,
    FOREIGN KEY (ProductID) REFERENCES products(ProductID)
);

-- Table: messages
CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    header VARCHAR(255),
    supplier VARCHAR(255),
    preview TEXT,
    batch VARCHAR(100),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_sent TIMESTAMP NULL,
    status VARCHAR(50) DEFAULT 'Pending'
);

-- Table: notifications
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50),
    title VARCHAR(255),
    details TEXT,
    link VARCHAR(255),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0
);

-- Insert some sample data (optional)
-- Sample categories
INSERT IGNORE INTO categories (Category_Name) VALUES 
    ('Medications'),
    ('Medical Supplies'),
    ('Equipment'),
    ('Consumables');

-- Sample units
INSERT IGNORE INTO units (UnitName) VALUES 
    ('Box'),
    ('Piece'),
    ('Bottle'),
    ('Pack'),
    ('Unit');

