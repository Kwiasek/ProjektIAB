-- database.sql

DROP DATABASE IF EXISTS sport_reservations;
CREATE DATABASE sport_reservations CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sport_reservations;

CREATE TABLE IF NOT EXISTS users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     login VARCHAR(100) NOT NULL,
     name VARCHAR(100) NOT NULL,
     password VARCHAR(255) NOT NULL,
     role ENUM('user', 'admin', 'owner') NOT NULL DEFAULT 'user',
     created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS facilities (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      description TEXT NOT NULL,
      location VARCHAR(255) NOT NULL,
      price_per_hour DECIMAL(8,2) NOT NULL,
      image_url VARCHAR(255) NOT NULL,
      created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    facility_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    total_price DECIMAL(8,2) NOT NULL,
    persons INT NOT NULL,
    max_persons INT,
    accept_guests BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (facility_id) REFERENCES facilities(id)
);

CREATE TABLE IF NOT EXISTS facility_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    facility_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (facility_id) REFERENCES facilities(id)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    amount DECIMAL(8,2) NOT NULL,
    method ENUM('cash', 'card', 'transfer') NOT NULL,
    status ENUM('pending', 'paid', 'failed') NOT NULL,
    paid_at DATETIME,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

CREATE TABLE IF NOT EXISTS user_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewer_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS user_contacts (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     email VARCHAR(100) NOT NULL,
     phone VARCHAR(20) NOT NULL,
     FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS guest_requests (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      reservation_id INT NOT NULL,
      status ENUM('pending', 'accepted', 'canceled') NOT NULL DEFAULT 'pending',
      created_at DATETIME NOT NULL,
      FOREIGN KEY (user_id) REFERENCES users(id),
      FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

-- 🔹 Wstawienie przykładowych danych
INSERT INTO users (login, name, password, role, created_at) VALUES
('admin', 'Admin', '$2y$10$examplehashadmin', 'admin', NOW()),
('owner1', 'Właściciel 1', '$2y$10$examplehashowner', 'owner', NOW()),
('user1', 'Użytkownik 1', '$2y$10$examplehashuser', 'user', NOW());

INSERT INTO facilities (name, description, location, price_per_hour, image_url, created_at) VALUES
('Boisko Orlik', 'Boisko do piłki nożnej ze sztuczną murawą', 'Łódź, ul. Sportowa 12', 120.00, '/images/orlik.jpg', NOW()),
('Kort Tenisowy Centrum', 'Profesjonalny kort z oświetleniem', 'Łódź, ul. Rakietowa 5', 90.00, '/images/tenis.jpg', NOW()),
('Basen Miejski', 'Kryty basen z szatniami i sauną', 'Łódź, ul. Wodna 2', 60.00, '/images/basen.jpg', NOW());
