<?php
function jc_get_db(): ?PDO
{
    $host = '127.0.0.1';
    $db = 'lennot';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

function jc_fallback_flights(): array
{
    return [
        ['id' => 1, 'flight_code' => 'JC 101', 'departure_city' => 'Helsinki', 'destination_city' => 'Oulu', 'departure_period' => 'Aamu', 'available_seats' => 6, 'price' => 65.50],
        ['id' => 2, 'flight_code' => 'JC 102', 'departure_city' => 'Helsinki', 'destination_city' => 'Oulu', 'departure_period' => 'Ilta', 'available_seats' => 15, 'price' => 65.75],
        ['id' => 3, 'flight_code' => 'JC 103', 'departure_city' => 'Helsinki', 'destination_city' => 'Helsinki', 'departure_period' => 'Päivä', 'available_seats' => 50, 'price' => 74.55],
        ['id' => 4, 'flight_code' => 'JC 104', 'departure_city' => 'Helsinki', 'destination_city' => 'Rovaniemi', 'departure_period' => 'Päivä', 'available_seats' => 3, 'price' => 78.94],
        ['id' => 5, 'flight_code' => 'JC 105', 'departure_city' => 'Helsinki', 'destination_city' => 'Oslo', 'departure_period' => 'Aamu', 'available_seats' => 40, 'price' => 125.45],
        ['id' => 6, 'flight_code' => 'JC 106', 'departure_city' => 'Helsinki', 'destination_city' => 'Bergen', 'departure_period' => 'Päivä', 'available_seats' => 23, 'price' => 86.45],
    ];
}

function jc_get_flights(array $filters = []): array
{
    $pdo = jc_get_db();

    if ($pdo) {
        $sql = 'SELECT LentoID AS id, Kohdemaa AS country, Kaupunki AS destination_city, Aika AS departure_period, Kone AS flight_code, LipunHinta AS price, VapaatPaikat AS available_seats FROM lennot WHERE 1=1';
        $params = [];

        if (!empty($filters['destination_city'])) {
            $sql .= ' AND Kaupunki = :destination_city';
            $params[':destination_city'] = $filters['destination_city'];
        }

        if (!empty($filters['departure_period'])) {
            $sql .= ' AND Aika = :departure_period';
            $params[':departure_period'] = ucfirst(trim((string)$filters['departure_period']));
        }

        $sql .= ' ORDER BY LentoID ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['departure_city'] = 'Helsinki';
            $row['price'] = (float)$row['price'];
        }
        unset($row);

        return $rows;
    }

    $flights = jc_fallback_flights();

    if (!empty($filters['destination_city'])) {
        $flights = array_values(array_filter($flights, fn($row) => $row['destination_city'] === $filters['destination_city']));
    }
    if (!empty($filters['departure_period'])) {
        $flights = array_values(array_filter($flights, fn($row) => strtolower($row['departure_period']) === strtolower($filters['departure_period'])));
    }

    return $flights;
}

function jc_create_order(array $data): array
{
    $pdo = jc_get_db();

    if ($pdo) {
        $sql = 'INSERT INTO orders (departure_date, departure_period, destination_city, ticket_count, customer_name, customer_address, customer_email, customer_phone, email_hash, created_at)
                VALUES (:departure_date, :departure_period, :destination_city, :ticket_count, :customer_name, :customer_address, :customer_email, :customer_phone, :email_hash, NOW())';

        $hash = hash_hmac('sha256', trim($data['customer_email']), 'jc-airlines-salt');
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':departure_date' => $data['departure_date'],
            ':departure_period' => $data['departure_period'],
            ':destination_city' => $data['destination_city'],
            ':ticket_count' => (int)$data['ticket_count'],
            ':customer_name' => $data['customer_name'],
            ':customer_address' => $data['customer_address'],
            ':customer_email' => $data['customer_email'],
            ':customer_phone' => $data['customer_phone'],
            ':email_hash' => $hash,
        ]);

        return ['status' => 'success', 'message' => 'Tilaus tallennettiin onnistuneesti.'];
    }

    return ['status' => 'success', 'message' => 'Tietokanta ei ole käytössä, mutta tilaus on simuloitu onnistuneesti.'];
}

function jc_city_list(): array
{
    $pdo = jc_get_db();

    if ($pdo) {
        $stmt = $pdo->query('SELECT DISTINCT Kaupunki AS city FROM lennot ORDER BY Kaupunki ASC');
        $rows = $stmt->fetchAll();
        $cities = array_map(fn($row) => $row['city'], $rows);
        return $cities ?: ['Oulu', 'Helsinki', 'Rovaniemi', 'Oslo', 'Bergen', 'Arlanda', 'Göteborg', 'Keflaki', 'Malmö', 'Trondheim', 'Kööpenhamina', 'Billund'];
    }

    return ['Oulu', 'Helsinki', 'Rovaniemi', 'Oslo', 'Bergen', 'Arlanda', 'Göteborg', 'Keflaki', 'Malmö', 'Trondheim', 'Kööpenhamina', 'Billund'];
}
