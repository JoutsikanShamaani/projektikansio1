<?php
const JC_DEPARTURE_CITY = 'Tampere';

function jc_get_db(): ?PDO
{
    static $connection;
    static $attempted = false;

    if ($attempted) {
        return $connection;
    }

    $attempted = true;
    $host = '127.0.0.1';
    $db = 'lennot';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $connection = $pdo;
    } catch (PDOException $e) {
        error_log('JC Airlines database connection failed: ' . $e->getMessage());
    }

    return $connection;
}

function jc_get_flights(array $filters = []): array
{
    $pdo = jc_get_db();

    if (!$pdo) {
        return [];
    }

    $sql = 'SELECT LentoID AS id, Kohdemaa AS country, Kaupunki AS destination_city, Aika AS departure_period, Kone AS flight_code, LipunHinta AS price, VapaatPaikat AS available_seats FROM lennot WHERE VapaatPaikat > 0';
    $params = [];

    if (!empty($filters['destination_city'])) {
        $sql .= ' AND Kaupunki = :destination_city';
        $params[':destination_city'] = $filters['destination_city'];
    }

    if (!empty($filters['departure_period'])) {
        $sql .= ' AND Aika = :departure_period';
        $params[':departure_period'] = ucfirst(trim((string)$filters['departure_period']));
    }

    if (!empty($filters['ticket_count'])) {
        $seatMultiplier = ($filters['trip_type'] ?? 'one_way') === 'round_trip' ? 2 : 1;
        $sql .= ' AND VapaatPaikat >= :ticket_count';
        $params[':ticket_count'] = max(1, (int)$filters['ticket_count']) * $seatMultiplier;
    }

    $sql .= ' ORDER BY LentoID ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$flight) {
        $flight['departure_city'] = JC_DEPARTURE_CITY;
        $flight['departure_date'] = $filters['departure_date'] ?? date('Y-m-d');
        $flight['price'] = (float)$flight['price'];
    }
    unset($flight);

    return $rows;
}

function jc_create_order(array $data): array
{
    $pdo = jc_get_db();

    if (!$pdo) {
        return ['status' => 'error', 'message' => 'Tietokantaan ei saatu yhteyttä. Tilausta ei tallennettu.'];
    }

    try {
        $pdo->beginTransaction();

        $flightStmt = $pdo->prepare(
            'SELECT Kaupunki AS destination_city, Aika AS departure_period, VapaatPaikat AS available_seats
             FROM lennot WHERE LentoID = :flight_id FOR UPDATE'
        );
        $flightStmt->execute([':flight_id' => (int)$data['flight_id']]);
        $flight = $flightStmt->fetch();

        $seatMultiplier = $data['trip_type'] === 'round_trip' ? 2 : 1;
        $seatsToReserve = (int)$data['ticket_count'] * $seatMultiplier;

        if (!$flight || (int)$flight['available_seats'] < $seatsToReserve) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'Lennolla ei ole riittävästi vapaita paikkoja. Valitse toinen lento tai pienempi matkustajamäärä.'];
        }

        $updateStmt = $pdo->prepare(
            'UPDATE lennot SET VapaatPaikat = VapaatPaikat - :seats_to_subtract
             WHERE LentoID = :flight_id AND VapaatPaikat >= :minimum_seats'
        );
        $updateStmt->execute([
            ':seats_to_subtract' => $seatsToReserve,
            ':flight_id' => (int)$data['flight_id'],
            ':minimum_seats' => $seatsToReserve,
        ]);

        if ($updateStmt->rowCount() !== 1) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'Paikkamäärä muuttui varauksen aikana. Tarkista lennon vapaat paikat ja yritä uudelleen.'];
        }

        $emailHash = password_hash(trim($data['customer_email']), PASSWORD_DEFAULT);
        $orderStmt = $pdo->prepare(
            'INSERT INTO tilaus (flight_id, trip_type, departure_date, return_date, departure_period, destination_city, ticket_count, customer_name, customer_address, customer_email, customer_phone, created_at)
             VALUES (:flight_id, :trip_type, :departure_date, :return_date, :departure_period, :destination_city, :ticket_count, :customer_name, :customer_address, :customer_email, :customer_phone, NOW())'
        );
        $orderStmt->execute([
            ':flight_id' => (int)$data['flight_id'],
            ':trip_type' => $data['trip_type'],
            ':departure_date' => $data['departure_date'],
            ':return_date' => $data['return_date'] !== '' ? $data['return_date'] : null,
            ':departure_period' => $flight['departure_period'],
            ':destination_city' => $flight['destination_city'],
            ':ticket_count' => (int)$data['ticket_count'],
            ':customer_name' => $data['customer_name'],
            ':customer_address' => $data['customer_address'],
            ':customer_email' => $emailHash,
            ':customer_phone' => $data['customer_phone'],
        ]);

        $orderId = (int)$pdo->lastInsertId();
        $pdo->commit();
        return [
            'status' => 'success',
            'message' => 'Tilaus vahvistettiin ja lentolippu tallennettiin.',
            'order_id' => $orderId,
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('JC Airlines order failed: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Tilauksen tallennus epäonnistui tietokantavirheen vuoksi.'];
    }
}

function jc_city_list(): array
{
    $pdo = jc_get_db();

    if (!$pdo) {
        return [];
    }

    $stmt = $pdo->query('SELECT DISTINCT Kaupunki AS city FROM lennot ORDER BY Kaupunki ASC');
    $rows = $stmt->fetchAll();
    return array_map(function ($row) {
        return trim($row['city']);
    }, $rows);
}

function jc_get_flight(int $flightId): ?array
{
    $pdo = jc_get_db();

    if (!$pdo) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT LentoID AS id, Kohdemaa AS country, Kaupunki AS destination_city, Aika AS departure_period,
                Kone AS aircraft, LipunHinta AS price, VapaatPaikat AS available_seats
         FROM lennot WHERE LentoID = :flight_id'
    );
    $stmt->execute([':flight_id' => $flightId]);
    $flight = $stmt->fetch();

    if (!$flight) {
        return null;
    }

    $flight['departure_city'] = JC_DEPARTURE_CITY;
    $flight['price'] = (float)$flight['price'];
    return $flight;
}

function jc_get_destinations(): array
{
    $pdo = jc_get_db();

    if (!$pdo) {
        return [];
    }

    $stmt = $pdo->query(
        'SELECT TRIM(Kohdemaa) AS country, Kaupunki AS city, COUNT(*) AS flight_count
         FROM lennot GROUP BY TRIM(Kohdemaa), Kaupunki ORDER BY TRIM(Kohdemaa), Kaupunki'
    );
    return $stmt->fetchAll();
}

function jc_get_aircraft(): array
{
    $pdo = jc_get_db();

    if (!$pdo) {
        return [];
    }

    $stmt = $pdo->query('SELECT DISTINCT Kone AS name FROM lennot ORDER BY Kone');
    return array_map(function ($row) {
        return $row['name'];
    }, $stmt->fetchAll());
}
