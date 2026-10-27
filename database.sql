CREATE DATABASE IF NOT EXISTS GuitarShop
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE GuitarShop;

CREATE TABLE IF NOT EXISTS categories (
    categoryID INT NOT NULL AUTO_INCREMENT,
    categoryName VARCHAR(255) NOT NULL,
    PRIMARY KEY (categoryID),
    UNIQUE KEY uq_categories_name (categoryName)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    productID INT NOT NULL AUTO_INCREMENT,
    categoryID INT NOT NULL,
    productCode VARCHAR(10) NOT NULL,
    productName VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    listPrice DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (productID),
    UNIQUE KEY uq_products_code (productCode),
    CONSTRAINT fk_products_categories FOREIGN KEY (categoryID)
        REFERENCES categories (categoryID)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Thêm danh mục và dữ liệu mẫu mà không xóa hoặc ghi đè dữ liệu hiện có.
INSERT IGNORE INTO categories (categoryName) VALUES
    ('Acoustic Guitars'),
    ('Electric Guitars'),
    ('Classical Guitars');

INSERT IGNORE INTO products (categoryID, productCode, productName, description, listPrice)
SELECT categoryID, 'fendertele', 'Fender Telecaster',
       'A classic electric guitar with a bright, clear tone.', 899.99
FROM categories
WHERE categoryName IN ('Electric Guitars', 'Guitars')
ORDER BY (categoryName = 'Electric Guitars') DESC
LIMIT 1;

INSERT IGNORE INTO products (categoryID, productCode, productName, description, listPrice)
SELECT categoryID, 'yamaha830', 'Yamaha FG830',
       'A steel-string acoustic guitar with a solid spruce top.', 399.99
FROM categories
WHERE categoryName IN ('Acoustic Guitars', 'Guitars')
ORDER BY (categoryName = 'Acoustic Guitars') DESC
LIMIT 1;
