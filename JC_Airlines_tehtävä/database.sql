CREATE DATABASE IF NOT EXISTS jc_airlines;
USE jc_airlines;

CREATE TABLE IF NOT EXISTS flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_code VARCHAR(20) NOT NULL,
    departure_city VARCHAR(50) NOT NULL,
    destination_city VARCHAR(50) NOT NULL,
    departure_date DATE NOT NULL,
    departure_period ENUM('aamu', 'päivä', 'ilta') NOT NULL,
    available_seats INT NOT NULL DEFAULT 30,
    price DECIMAL(8,2) NOT NULL DEFAULT 89.00
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departure_date DATE NOT NULL,
    departure_period VARCHAR(20) NOT NULL,
    destination_city VARCHAR(50) NOT NULL,
    ticket_count INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_address VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    email_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO flights (flight_code, departure_city, destination_city, departure_date, departure_period, available_seats, price) VALUES
('JC101', 'Helsinki', 'Tampere', '2026-10-23', 'aamu', 28, 79.00),
('JC102', 'Helsinki', 'Turku', '2026-10-23', 'päivä', 34, 89.00),
('JC103', 'Helsinki', 'Rovaniemi', '2026-10-23', 'ilta', 19, 149.00),
('JC104', 'Tampere', 'Helsinki', '2026-10-23', 'aamu', 32, 82.00),
('JC105', 'Turku', 'Helsinki', '2026-10-23', 'ilta', 26, 76.00),
('JC106', 'Rovaniemi', 'Helsinki', '2026-10-23', 'päivä', 21, 142.00),
('JC201', 'Helsinki', 'Kuopio', '2026-10-24', 'aamu', 24, 94.00),
('JC202', 'Helsinki', 'Joensuu', '2026-10-24', 'päivä', 29, 101.00),
('JC203', 'Tampere', 'Oulu', '2026-10-24', 'ilta', 22, 118.00),
('JC204', 'Vaasa', 'Helsinki', '2026-10-24', 'päivä', 20, 88.00);
