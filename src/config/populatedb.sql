-- POPULATE DATABASE TEST DATA
-- ===========================================================================

USE sport_reservations;
-- Insert 10 users (2 owners + 8 regular users)
INSERT INTO users (login, name, password, role, created_at) VALUES
-- Owners
('owner1', 'Jan Kowalski', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'owner', '2025-01-01 10:00:00'),
('owner2', 'Maria Nowak', '$2y$10$Q7ZpM8xY9nN2kLmJoPqRVe3tVhWrSsXxYzAbCdEfGhIjKlMnOpQrS', 'owner', '2025-01-02 11:00:00'),
-- Regular users
('user1', 'Adam Lewandowski', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-03 09:00:00'),
('user2', 'Ewa Szymańska', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-04 10:30:00'),
('user3', 'Piotr Kaminski', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-05 14:00:00'),
('user4', 'Anna Głowska', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-06 08:00:00'),
('user5', 'Michał Wróbel', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-07 15:30:00'),
('user6', 'Katarzyna Borek', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-08 11:00:00'),
('user7', 'Krzysztof Mazur', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-09 13:00:00'),
('user8', 'Olga Gizińska', '$2y$10$K5.7hVjqWjBXMzqMqA0YceK8n3Jq.dBvZZZQ4XqjU.Z5V5qJ2ZKQi', 'user', '2025-01-10 09:30:00');

-- Insert 10 facilities (5 per owner)
-- OWNER 1 facilities (owner_id = 1)
INSERT INTO facilities (owner_id, name, description, location, price_per_hour, created_at) VALUES
(1, 'Korty tenisowe Centrum', 'Nowoczesne korty tenisowe z profesjonalnym oświetleniem', 'Warszawa, ul. Sportowa 1', 85.00, '2025-01-01 12:00:00'),
(1, 'Hala piłkarska 5x5', 'Piłkarz futsal z boiska syntetycznym', 'Warszawa, al. Jerozolimskie 50', 120.00, '2025-01-02 10:00:00'),
(1, 'Siłownia Fit Club', 'Nowoczesna siłownia z pełnym wyposażeniem', 'Warszawa, ul. Piekna 25', 50.00, '2025-01-03 14:00:00'),
(1, 'Baseny publiczne', 'Kompleks basenów ze zjeżdżalnią i sauna', 'Warszawa, ul. Aqua 10', 35.00, '2025-01-04 09:00:00'),
(1, 'Boisko do badmintona', 'Trzy profesjonalne korty badmintonowe', 'Warszawa, ul. Sportowa 5', 45.00, '2025-01-05 11:00:00'),

-- OWNER 2 facilities (owner_id = 2)
(2, 'Kurs jazdy konnej', 'Hipodrom z instruktorem zawodowym', 'Piaseczno, ul. Leśna 15', 150.00, '2025-01-06 10:00:00'),
(2, 'Squash Club Premium', 'Cztery nowoczesne korty do squasha', 'Piaseczno, ul. Centrum 30', 95.00, '2025-01-07 13:00:00'),
(2, 'Yoga Studio Zen', 'Studio jogi z instruktorem', 'Piaseczno, ul. Harmonia 8', 60.00, '2025-01-08 09:00:00'),
(2, 'Strzelnica sportowa', 'Strzelnica nowoczesna z celowaniem laserowym', 'Piaseczno, ul. Strzelectwa 20', 75.00, '2025-01-09 11:00:00'),
(2, 'Pump Track BMX', 'Tor do jazdy na rowerze BMX i skateboard', 'Piaseczno, ul. Przygody 12', 40.00, '2025-01-10 10:00:00');

-- Insert facility availability (each facility open 8:00-20:00)
INSERT INTO facility_availability (facility_id, day_of_week, open_time, close_time, is_open) VALUES
-- Facility 1
(1, 'monday', '08:00:00', '20:00:00', TRUE),
(1, 'tuesday', '08:00:00', '20:00:00', TRUE),
(1, 'wednesday', '08:00:00', '20:00:00', TRUE),
(1, 'thursday', '08:00:00', '20:00:00', TRUE),
(1, 'friday', '08:00:00', '22:00:00', TRUE),
(1, 'saturday', '09:00:00', '21:00:00', TRUE),
(1, 'sunday', '09:00:00', '20:00:00', TRUE),
-- Facility 2
(2, 'monday', '08:00:00', '20:00:00', TRUE),
(2, 'tuesday', '08:00:00', '20:00:00', TRUE),
(2, 'wednesday', '08:00:00', '20:00:00', TRUE),
(2, 'thursday', '08:00:00', '20:00:00', TRUE),
(2, 'friday', '08:00:00', '22:00:00', TRUE),
(2, 'saturday', '09:00:00', '21:00:00', TRUE),
(2, 'sunday', '09:00:00', '20:00:00', TRUE),
-- Facility 3
(3, 'monday', '06:00:00', '22:00:00', TRUE),
(3, 'tuesday', '06:00:00', '22:00:00', TRUE),
(3, 'wednesday', '06:00:00', '22:00:00', TRUE),
(3, 'thursday', '06:00:00', '22:00:00', TRUE),
(3, 'friday', '06:00:00', '23:00:00', TRUE),
(3, 'saturday', '07:00:00', '23:00:00', TRUE),
(3, 'sunday', '08:00:00', '22:00:00', TRUE),
-- Facility 4
(4, 'monday', '07:00:00', '19:00:00', TRUE),
(4, 'tuesday', '07:00:00', '19:00:00', TRUE),
(4, 'wednesday', '07:00:00', '19:00:00', TRUE),
(4, 'thursday', '07:00:00', '19:00:00', TRUE),
(4, 'friday', '07:00:00', '20:00:00', TRUE),
(4, 'saturday', '08:00:00', '20:00:00', TRUE),
(4, 'sunday', '08:00:00', '19:00:00', TRUE),
-- Facility 5
(5, 'monday', '08:00:00', '21:00:00', TRUE),
(5, 'tuesday', '08:00:00', '21:00:00', TRUE),
(5, 'wednesday', '08:00:00', '21:00:00', TRUE),
(5, 'thursday', '08:00:00', '21:00:00', TRUE),
(5, 'friday', '08:00:00', '22:00:00', TRUE),
(5, 'saturday', '09:00:00', '22:00:00', TRUE),
(5, 'sunday', '09:00:00', '21:00:00', TRUE),
-- Facility 6
(6, 'monday', '09:00:00', '18:00:00', TRUE),
(6, 'tuesday', '09:00:00', '18:00:00', TRUE),
(6, 'wednesday', '09:00:00', '18:00:00', TRUE),
(6, 'thursday', '09:00:00', '18:00:00', TRUE),
(6, 'friday', '09:00:00', '19:00:00', TRUE),
(6, 'saturday', '08:00:00', '19:00:00', TRUE),
(6, 'sunday', '08:00:00', '18:00:00', TRUE),
-- Facility 7
(7, 'monday', '08:00:00', '20:00:00', TRUE),
(7, 'tuesday', '08:00:00', '20:00:00', TRUE),
(7, 'wednesday', '08:00:00', '20:00:00', TRUE),
(7, 'thursday', '08:00:00', '20:00:00', TRUE),
(7, 'friday', '08:00:00', '22:00:00', TRUE),
(7, 'saturday', '09:00:00', '22:00:00', TRUE),
(7, 'sunday', '09:00:00', '20:00:00', TRUE),
-- Facility 8
(8, 'monday', '07:00:00', '21:00:00', TRUE),
(8, 'tuesday', '07:00:00', '21:00:00', TRUE),
(8, 'wednesday', '07:00:00', '21:00:00', TRUE),
(8, 'thursday', '07:00:00', '21:00:00', TRUE),
(8, 'friday', '07:00:00', '22:00:00', TRUE),
(8, 'saturday', '08:00:00', '21:00:00', TRUE),
(8, 'sunday', '08:00:00', '21:00:00', TRUE),
-- Facility 9
(9, 'monday', '08:00:00', '19:00:00', TRUE),
(9, 'tuesday', '08:00:00', '19:00:00', TRUE),
(9, 'wednesday', '08:00:00', '19:00:00', TRUE),
(9, 'thursday', '08:00:00', '19:00:00', TRUE),
(9, 'friday', '08:00:00', '20:00:00', TRUE),
(9, 'saturday', '09:00:00', '20:00:00', TRUE),
(9, 'sunday', '09:00:00', '19:00:00', TRUE),
-- Facility 10
(10, 'monday', '09:00:00', '20:00:00', TRUE),
(10, 'tuesday', '09:00:00', '20:00:00', TRUE),
(10, 'wednesday', '09:00:00', '20:00:00', TRUE),
(10, 'thursday', '09:00:00', '20:00:00', TRUE),
(10, 'friday', '09:00:00', '21:00:00', TRUE),
(10, 'saturday', '10:00:00', '21:00:00', TRUE),
(10, 'sunday', '10:00:00', '20:00:00', TRUE);

-- Insert example reservations
INSERT INTO reservations (user_id, facility_id, date, start_time, end_time, status, total_price, persons, max_persons, accept_guests, created_at) VALUES
-- Facility 1 reservations
(3, 1, '2025-01-20', '09:00:00', '11:00:00', 'confirmed', 170.00, 2, 4, FALSE, '2025-01-15 10:00:00'),
(4, 1, '2025-01-20', '14:00:00', '16:00:00', 'paid', 170.00, 1, 4, FALSE, '2025-01-14 15:30:00'),
(5, 1, '2025-01-21', '10:00:00', '12:00:00', 'confirmed', 170.00, 2, 4, FALSE, '2025-01-15 11:00:00'),

-- Facility 2 reservations
(6, 2, '2025-01-19', '17:00:00', '19:00:00', 'confirmed', 240.00, 4, 6, TRUE, '2025-01-14 08:00:00'),
(7, 2, '2025-01-22', '18:00:00', '20:00:00', 'paid', 240.00, 3, 6, FALSE, '2025-01-15 12:00:00'),

-- Facility 3 reservations
(3, 3, '2025-01-18', '07:00:00', '09:00:00', 'paid', 100.00, 1, 20, FALSE, '2025-01-12 14:00:00'),
(8, 3, '2025-01-20', '18:00:00', '20:00:00', 'confirmed', 100.00, 2, 20, FALSE, '2025-01-15 09:00:00'),

-- Facility 4 reservations
(4, 4, '2025-01-19', '15:00:00', '17:00:00', 'confirmed', 70.00, 2, 10, FALSE, '2025-01-13 10:00:00'),
(9, 4, '2025-01-21', '08:00:00', '10:00:00', 'paid', 70.00, 1, 10, FALSE, '2025-01-15 11:30:00'),

-- Facility 5 reservations
(5, 5, '2025-01-20', '19:00:00', '21:00:00', 'confirmed', 90.00, 2, 4, FALSE, '2025-01-14 16:00:00'),
(6, 5, '2025-01-22', '14:00:00', '16:00:00', 'paid', 90.00, 1, 4, FALSE, '2025-01-15 13:00:00'),

-- Facility 6 reservations
(7, 6, '2025-01-18', '10:00:00', '12:00:00', 'confirmed', 300.00, 1, 2, FALSE, '2025-01-12 09:00:00'),
(8, 6, '2025-01-20', '14:00:00', '15:00:00', 'paid', 150.00, 1, 2, FALSE, '2025-01-15 10:30:00'),

-- Facility 7 reservations
(3, 7, '2025-01-19', '18:00:00', '20:00:00', 'confirmed', 120.00, 2, 15, FALSE, '2025-01-14 11:00:00'),
(9, 7, '2025-01-22', '09:00:00', '11:00:00', 'paid', 120.00, 1, 15, FALSE, '2025-01-15 14:00:00'),

-- Facility 8 reservations
(4, 8, '2025-01-18', '08:00:00', '10:00:00', 'confirmed', 120.00, 1, 5, FALSE, '2025-01-13 14:30:00'),
(5, 8, '2025-01-21', '19:00:00', '21:00:00', 'paid', 120.00, 2, 5, FALSE, '2025-01-15 15:00:00'),

-- Facility 9 reservations
(6, 9, '2025-01-20', '09:00:00', '11:00:00', 'confirmed', 150.00, 1, 8, FALSE, '2025-01-14 12:00:00'),
(7, 9, '2025-01-19', '16:00:00', '18:00:00', 'paid', 150.00, 2, 8, FALSE, '2025-01-13 16:30:00'),

-- Facility 10 reservations
(8, 10, '2025-01-21', '15:00:00', '17:00:00', 'confirmed', 80.00, 2, 6, FALSE, '2025-01-15 09:00:00'),
(9, 10, '2025-01-22', '10:00:00', '12:00:00', 'paid', 80.00, 1, 6, FALSE, '2025-01-15 16:00:00');

-- Insert facility reviews
INSERT INTO facility_reviews (user_id, facility_id, rating, comment, created_at) VALUES
-- Reviews for Facility 1
(3, 1, 5, 'Świetne obiekty, profesjonalna obsługa! Polecam!', '2025-01-20 12:00:00'),
(4, 1, 4, 'Dobre korty, trochę drogo ale warte ceny', '2025-01-20 17:00:00'),
(5, 1, 5, 'Najlepsze korty tenisowe w mieście!', '2025-01-21 13:00:00'),

-- Reviews for Facility 2
(6, 2, 4, 'Piękna hala, dobra atmosfera', '2025-01-19 20:00:00'),
(7, 2, 5, 'Świetne dla drużyny, polecam!', '2025-01-22 21:00:00'),

-- Reviews for Facility 3
(3, 3, 5, 'Dobrze wyposażona siłownia, czysty sprzęt', '2025-01-18 10:00:00'),
(8, 3, 4, 'Dobra siłownia, czasem pełno ludzi', '2025-01-20 21:00:00'),

-- Reviews for Facility 4
(4, 4, 5, 'Cudowne baseny, idealne dla rodzin', '2025-01-19 18:00:00'),
(9, 4, 4, 'Czysty basen, brakuje tylko jacuzzi', '2025-01-21 11:00:00'),

-- Reviews for Facility 5
(5, 5, 5, 'Świetne korty do badmintona, profesjonalnie', '2025-01-20 22:00:00'),
(6, 5, 5, 'Najlepsza hala badmintonowa jaką znam', '2025-01-22 17:00:00'),

-- Reviews for Facility 6
(7, 6, 5, 'Kurs jazdy konnej godny polecenia!', '2025-01-18 13:00:00'),
(8, 6, 4, 'Instruktor kompetentny, konie miłe', '2025-01-20 16:00:00'),

-- Reviews for Facility 7
(3, 7, 4, 'Dobre korty squasha, przyjemna atmosfera', '2025-01-19 21:00:00'),
(9, 7, 5, 'Perfekcyjnie utrzymane obiekty!', '2025-01-22 12:00:00'),

-- Reviews for Facility 8
(4, 8, 5, 'Relaksujący studia jogi, świetny instruktor', '2025-01-18 11:00:00'),
(5, 8, 5, 'Doskonałe miejsce do medytacji i jogi', '2025-01-21 22:00:00'),

-- Reviews for Facility 9
(6, 9, 5, 'Profesjonalna strzelnica, sprzęt klasy światowej', '2025-01-20 12:00:00'),
(7, 9, 4, 'Strzelnica spełnia oczekiwania', '2025-01-19 19:00:00'),

-- Reviews for Facility 10
(8, 10, 5, 'Fantastyczny tor BMX dla całej rodziny!', '2025-01-21 18:00:00'),
(9, 10, 5, 'Świetna zabawa na pump tracku!', '2025-01-22 13:00:00');

-- Insert payments for 'paid' reservations
INSERT INTO payments (reservation_id, amount, method, status, paid_at) VALUES
(2, 170.00, 'card', 'paid', '2025-01-20 16:30:00'),
(5, 240.00, 'transfer', 'paid', '2025-01-22 19:00:00'),
(7, 100.00, 'card', 'paid', '2025-01-18 08:00:00'),
(9, 70.00, 'card', 'paid', '2025-01-21 09:30:00'),
(11, 90.00, 'transfer', 'paid', '2025-01-22 15:30:00'),
(13, 150.00, 'card', 'paid', '2025-01-20 15:00:00'),
(15, 120.00, 'cash', 'paid', '2025-01-22 10:30:00'),
(17, 120.00, 'card', 'paid', '2025-01-21 20:00:00'),
(19, 150.00, 'transfer', 'paid', '2025-01-19 17:30:00'),
(21, 80.00, 'card', 'paid', '2025-01-22 12:00:00');

-- Insert facility likes
INSERT INTO facility_likes (user_id, facility_id, created_at) VALUES
(3, 1, '2025-01-15 10:30:00'),
(4, 1, '2025-01-14 15:45:00'),
(5, 1, '2025-01-15 11:15:00'),
(6, 2, '2025-01-14 08:30:00'),
(7, 2, '2025-01-15 12:30:00'),
(3, 3, '2025-01-12 14:30:00'),
(8, 3, '2025-01-15 09:30:00'),
(4, 4, '2025-01-13 10:30:00'),
(9, 4, '2025-01-15 11:45:00'),
(5, 5, '2025-01-14 16:15:00'),
(6, 5, '2025-01-15 13:15:00'),
(7, 6, '2025-01-12 09:30:00'),
(8, 6, '2025-01-15 10:45:00'),
(3, 7, '2025-01-14 11:30:00'),
(9, 7, '2025-01-15 14:15:00'),
(4, 8, '2025-01-13 14:45:00'),
(5, 8, '2025-01-15 15:15:00'),
(6, 9, '2025-01-14 12:15:00'),
(7, 9, '2025-01-13 16:45:00'),
(8, 10, '2025-01-15 09:15:00'),
(9, 10, '2025-01-15 16:15:00');
