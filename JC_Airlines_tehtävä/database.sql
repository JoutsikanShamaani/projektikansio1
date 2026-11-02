CREATE DATABASE IF NOT EXISTS lennot
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE lennot;

CREATE TABLE IF NOT EXISTS lennot (
    LentoID INT(11) NOT NULL AUTO_INCREMENT,
    Kohdemaa VARCHAR(255) NOT NULL,
    Kaupunki VARCHAR(255) NOT NULL,
    Aika VARCHAR(255) NOT NULL,
    Kone VARCHAR(20) NOT NULL,
    LipunHinta DECIMAL(10,2) NOT NULL,
    VapaatPaikat INT(11) NOT NULL,
    PRIMARY KEY (LentoID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO lennot
    (LentoID, Kohdemaa, Kaupunki, Aika, Kone, LipunHinta, VapaatPaikat)
VALUES
    (1, 'Suomi', 'Oulu', 'Aamu', 'Q400', 65.50, 6),
    (2, 'Suomi', 'Oulu', 'Ilta', 'Q400', 65.75, 15),
    (3, 'Suomi', 'Helsinki', 'Päivä', 'A220-300', 74.55, 50),
    (4, 'Suomi', 'Rovaniemi', 'Päivä', 'Q400', 78.94, 3),
    (5, 'Norja', 'Oslo', 'Aamu', 'Q400', 125.45, 40),
    (6, 'Norja', 'Oslo', 'Aamu', 'A220-300', 132.53, 14),
    (7, 'Norja', 'Bergen', 'Päivä', 'Q400', 86.45, 23),
    (8, 'Norja', 'Bergen', 'Aamu', 'Q400', 75.88, 3),
    (9, 'Ruotsi', 'Arlanda', 'Päivä', 'Q400', 98.12, 20),
    (10, 'Ruotsi', 'Arlanda', 'Aamu', 'Q400', 99.49, 12),
    (11, 'Ruotsi', 'Arlanda', 'Päivä', 'A220-300', 102.24, 50),
    (12, 'Suomi', 'Helsinki', 'Päivä', 'Q400', 78.50, 60),
    (13, 'Suomi', 'Rovaniemi', 'Päivä', 'Q400', 88.34, 16),
    (14, 'Suomi', 'Helsinki', 'Päivä', 'A220-300', 87.32, 5),
    (15, 'Norja', 'Bergen', 'Päivä', 'A220-100', 199.93, 7),
    (16, 'Norja', 'Bergen', 'Ilta', 'A220-300', 213.05, 8),
    (17, 'Suomi', 'Helsinki', 'Aamu', 'Q400', 81.20, 16),
    (18, 'Suomi', 'Helsinki', 'Aamu', 'A220-100', 99.00, 20),
    (19, 'Suomi', 'Oulu', 'Päivä', 'A220-100', 234.40, 7),
    (20, 'Norja', 'Bergen', 'Ilta', 'A220-100', 134.90, 30),
    (21, 'Norja', 'Bergen', 'Päivä', 'A220-300', 144.10, 8),
    (22, 'Ruotsi', 'Göteborg', 'Ilta', 'A220-300', 55.00, 34),
    (23, 'Ruotsi', 'Göteborg', 'Aamu', 'A220-300', 61.00, 12),
    (24, 'Islanti', 'Keflaki', 'Ilta', 'A220-300', 63.00, 15),
    (25, 'Ruotsi', 'Malmö', 'Ilta', 'Q400', 75.00, 32),
    (26, 'Ruotsi', 'Malmö', 'Aamu', 'A220-300', 82.00, 12),
    (27, 'Norja', 'Trondheim', 'Ilta', 'A220-300', 81.00, 16),
    (28, 'Tanska ', 'Kööpenhamina', 'Ilta', 'A220-300', 91.00, 3),
    (29, 'Ruotsi', 'Göteborg', 'Ilta', 'A220-300', 55.00, 34),
    (30, 'Tanska', 'Billund', 'Ilta', 'A220-300', 61.00, 12),
    (31, 'Islanti', 'Keflaki', 'Ilta', 'Q400', 63.00, 11),
    (32, 'Tanska', 'Billund', 'Aamu', 'Q400', 75.00, 32),
    (33, 'Tanska', 'Kööpenhamina', 'Ilta', 'A220-300', 82.00, 12),
    (34, 'Norja', 'Trondheim', 'Ilta', 'Q400', 81.00, 16),
    (35, 'Tanska ', 'Kööpenhamina', 'Ilta', 'A220-300', 91.00, 33);

CREATE TABLE IF NOT EXISTS tilaus (
    id INT(11) NOT NULL AUTO_INCREMENT,
    flight_id INT(11) NOT NULL,
    trip_type ENUM('one_way', 'round_trip') NOT NULL,
    departure_date DATE NOT NULL,
    return_date DATE NULL,
    departure_period VARCHAR(20) NOT NULL,
    destination_city VARCHAR(255) NOT NULL,
    ticket_count INT(11) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_address VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL COMMENT 'password_hash()-arvo, ei selväkielinen sähköposti',
    customer_phone VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX (flight_id),
    CONSTRAINT fk_tilaus_flight
        FOREIGN KEY (flight_id) REFERENCES lennot (LentoID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
