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
                                          owner_id INT NOT NULL,
                                          name VARCHAR(100) NOT NULL,
                                          description TEXT NOT NULL,
                                          location VARCHAR(255) NOT NULL,
                                          price_per_hour DECIMAL(8,2) NOT NULL,
                                          created_at DATETIME NOT NULL,
                                          FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS reservations (
                                            id INT AUTO_INCREMENT PRIMARY KEY,
                                            user_id INT NOT NULL,
                                            facility_id INT NOT NULL,
                                            date DATE NOT NULL,
                                            start_time TIME NOT NULL,
                                            end_time TIME NOT NULL,
                                            status ENUM('pending', 'confirmed', 'cancelled', 'paid') NOT NULL DEFAULT 'pending',
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

create table facility_availability (
                                       id int auto_increment primary key,
                                       facility_id int not null,
                                       day_of_week enum('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') not null,
                                       open_time time not null,
                                       close_time time not null,
                                       is_open boolean default true,
                                       created_at datetime default current_timestamp,
                                       updated_at datetime default current_timestamp on update current_timestamp,
                                       foreign key (facility_id) references facilities(id) on delete cascade
);

CREATE TABLE IF NOT EXISTS facility_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    data LONGBLOB NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
);
