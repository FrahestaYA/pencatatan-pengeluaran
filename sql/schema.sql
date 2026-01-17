CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- NULL for default categories, set for custom ones
    name VARCHAR(50) NOT NULL,
    type ENUM('expense', 'income') DEFAULT 'expense',
    icon VARCHAR(50) DEFAULT 'circle',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    month VARCHAR(7) NOT NULL, -- Format: YYYY-MM
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_budget (user_id, category_id, month)
);

-- Seed Default Categories
INSERT INTO categories (user_id, name, type, icon) VALUES 
(NULL, 'Makan', 'expense', 'utensils'),
(NULL, 'Transportasi', 'expense', 'bus'),
(NULL, 'Tagihan', 'expense', 'file-invoice'),
(NULL, 'Hiburan', 'expense', 'film'),
(NULL, 'Kesehatan', 'expense', 'heartbeat'),
(NULL, 'Belanja', 'expense', 'shopping-cart'),
(NULL, 'Gaji', 'income', 'money-bill-wave'),
(NULL, 'Bonus', 'income', 'star')
ON DUPLICATE KEY UPDATE name=name;
