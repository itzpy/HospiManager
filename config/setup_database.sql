-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Categories Table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Inventory Table
CREATE TABLE IF NOT EXISTS inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    quantity INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Insert some initial data
INSERT IGNORE INTO users (first_name, last_name, email, password, role) VALUES 
('Admin', 'User', 'admin@hospital.com', '$2y$10$Qg4XwUqmqKqAJHs2LzfKOOJWfqMOGx4LmZbqUuIELhNkWLFxRvhyS', 'admin'),
('Staff', 'User', 'staff@hospital.com', '$2y$10$Qg4XwUqmqKqAJHs2LzfKOOJWfqMOGx4LmZbqUuIELhNkWLFxRvhyS', 'staff');

-- Insert some initial categories
INSERT IGNORE INTO categories (name, description) VALUES 
('Medical Supplies', 'Essential medical equipment and consumables'),
('Pharmaceuticals', 'Medications and drug inventory');

-- Insert some initial inventory items
INSERT IGNORE INTO inventory (name, category_id, quantity, unit_price, description) VALUES 
('Surgical Mask', 1, 1000, 0.50, 'Disposable surgical masks'),
('Surgical Gloves', 1, 500, 0.25, 'Latex surgical gloves'),
('Paracetamol', 2, 200, 2.00, 'Pain relief medication');
