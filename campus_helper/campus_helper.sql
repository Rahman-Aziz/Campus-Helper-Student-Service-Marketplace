-- ============================================================
-- Campus Helper Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS campus_helper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_helper;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    university VARCHAR(150) DEFAULT NULL,
    role ENUM('buyer', 'seller', 'both', 'admin') DEFAULT 'both',
    balance DECIMAL(10,2) DEFAULT 0.00,
    is_verified TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Service categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT NULL,
    description TEXT DEFAULT NULL
);

-- Services (gigs) table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    delivery_days INT NOT NULL DEFAULT 3,
    thumbnail VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'paused', 'deleted') DEFAULT 'active',
    total_orders INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    seller_earnings DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','paid','in_progress','completed','cancelled','disputed') DEFAULT 'pending',
    requirements TEXT DEFAULT NULL,
    delivery_date DATE DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50) DEFAULT 'simulated',
    transaction_ref VARCHAR(100) DEFAULT NULL,
    status ENUM('pending','success','failed','refunded') DEFAULT 'pending',
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (payer_id) REFERENCES users(id)
);

-- Messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    service_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    reviewer_id INT NOT NULL,
    seller_id INT NOT NULL,
    service_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (reviewer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Support/Help tickets
CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('general','scam_report','payment_issue','other') DEFAULT 'general',
    status ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    staff_response TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- Seed Data
-- ============================================================

INSERT INTO categories (name, slug, icon, description) VALUES
('Academic Writing', 'academic-writing', '📝', 'Essays, reports, research papers'),
('Programming & Tech', 'programming', '💻', 'Coding, web dev, apps'),
('Design & Creative', 'design', '🎨', 'Logos, posters, UI/UX'),
('Tutoring', 'tutoring', '📚', 'Subject tutoring and coaching'),
('Translation', 'translation', '🌐', 'Language translation services'),
('Video & Animation', 'video', '🎬', 'Video editing and animation'),
('Data & Research', 'data', '📊', 'Data analysis and research'),
('Other Services', 'other', '⚡', 'Any other campus services');

-- Demo users (password = 'password123' hashed)
INSERT INTO users (username, email, password, full_name, university, role, is_verified) VALUES
('ali_hassan', 'ali@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ali Hassan', 'University of Malaya', 'both', 1),
('sarah_chen', 'sarah@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Chen', 'UTM', 'both', 1),
('raj_kumar', 'raj@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Raj Kumar', 'UPM', 'seller', 1);

-- Demo services
INSERT INTO services (seller_id, category_id, title, description, price, delivery_days) VALUES
(1, 1, 'Professional Essay Writing (1000 words)', 'I will write high-quality academic essays for any subject. Plagiarism-free, properly cited, and well-structured.', 45.00, 3),
(2, 2, 'Build You a React Website', 'Full responsive website using React. Clean code, mobile-friendly, deployed and ready to use.', 120.00, 7),
(3, 4, 'Math & Statistics Tutoring (1 Hour)', 'One-on-one tutoring session via video call. All levels from Form 5 to university.', 30.00, 1),
(1, 3, 'Logo Design for Student Projects', 'Modern logo design with 3 revisions included. PNG + AI files delivered.', 35.00, 2),
(2, 7, 'Data Analysis with Python/Excel', 'I will clean, analyze and visualize your dataset. Include charts and written summary.', 60.00, 4);
