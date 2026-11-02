<?php
require __DIR__ . '/db.php';

$destinationCities = jc_city_list();
$databaseAvailable = jc_get_db() !== null;
$today = date('Y-m-d');
$requestedDate = is_string($_GET['departure_date'] ?? null) ? $_GET['departure_date'] : $today;
$parsedDate = DateTime::createFromFormat('!Y-m-d', $requestedDate);
$departureDate = $parsedDate && $parsedDate->format('Y-m-d') === $requestedDate && $requestedDate >= $today
    ? $requestedDate
    : $today;
$requestedPeriod = is_string($_GET['departure_period'] ?? null) ? $_GET['departure_period'] : 'aamu';
$departurePeriod = in_array($requestedPeriod, ['aamu', 'päivä', 'ilta'], true) ? $requestedPeriod : 'aamu';
$requestedTripType = is_string($_GET['trip_type'] ?? null) ? $_GET['trip_type'] : 'one_way';
$tripType = in_array($requestedTripType, ['one_way', 'round_trip'], true) ? $requestedTripType : 'one_way';
$requestedReturnDate = is_string($_GET['return_date'] ?? null) ? $_GET['return_date'] : '';
$parsedReturnDate = DateTime::createFromFormat('!Y-m-d', $requestedReturnDate);
$returnDate = $parsedReturnDate && $parsedReturnDate->format('Y-m-d') === $requestedReturnDate && $requestedReturnDate > $departureDate
    ? $requestedReturnDate
    : '';
$searchError = $tripType === 'round_trip' && $returnDate === ''
    ? 'Valitse meno-paluumatkalle paluupäivä lähtöpäivän jälkeen.'
    : null;
$rawRequestedTicketCount = $_GET['ticket_count'] ?? 1;
$requestedTicketCount = filter_var(is_scalar($rawRequestedTicketCount) ? $rawRequestedTicketCount : 1, FILTER_VALIDATE_INT);
$ticketCount = $requestedTicketCount !== false ? max(1, min(9, $requestedTicketCount)) : 1;
$requestedDestination = is_string($_GET['destination_city'] ?? null) ? $_GET['destination_city'] : 'Oulu';
$destinationCity = in_array($requestedDestination, $destinationCities, true)
    ? $requestedDestination
    : (in_array('Oulu', $destinationCities, true) ? 'Oulu' : ($destinationCities[0] ?? ''));
$searchParams = [
    'departure_city' => JC_DEPARTURE_CITY,
    'destination_city' => $destinationCity,
    'departure_date' => $departureDate,
    'return_date' => $returnDate,
    'departure_period' => $departurePeriod,
    'trip_type' => $tripType,
    'ticket_count' => $ticketCount,
];

$bookingStatus = null;
$bookingError = null;
$bookingCancelled = false;
$pendingOrder = null;
$reviewFlight = null;
$ticketOrder = null;
$ticketFlight = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = is_string($_POST['booking_action'] ?? null) ? $_POST['booking_action'] : '';

    if ($action === 'cancel') {
        $bookingCancelled = true;
    } elseif ($action === 'review' || $action === 'confirm') {
        $rawFlightId = $_POST['flight_id'] ?? null;
        $rawTicketCount = $_POST['ticket_count'] ?? null;
        $postedFlightId = filter_var(is_scalar($rawFlightId) ? $rawFlightId : null, FILTER_VALIDATE_INT);
        $postedTicketCount = filter_var(is_scalar($rawTicketCount) ? $rawTicketCount : null, FILTER_VALIDATE_INT);
        $postedDate = is_string($_POST['departure_date'] ?? null) ? $_POST['departure_date'] : '';
        $postedTripType = is_string($_POST['trip_type'] ?? null) ? $_POST['trip_type'] : '';
        $postedReturnDate = is_string($_POST['return_date'] ?? null) ? $_POST['return_date'] : '';
        $parsedPostedDate = DateTime::createFromFormat('!Y-m-d', $postedDate);
        $parsedPostedReturnDate = DateTime::createFromFormat('!Y-m-d', $postedReturnDate);
        $postedName = is_string($_POST['customer_name'] ?? null) ? trim($_POST['customer_name']) : '';
        $postedAddress = is_string($_POST['customer_address'] ?? null) ? trim($_POST['customer_address']) : '';
        $postedEmail = is_string($_POST['customer_email'] ?? null) ? trim($_POST['customer_email']) : '';
        $postedPhone = is_string($_POST['customer_phone'] ?? null) ? trim($_POST['customer_phone']) : '';

        if (
            $postedFlightId === false || $postedFlightId < 1
            || $postedTicketCount === false || $postedTicketCount < 1 || $postedTicketCount > 9
            || !$parsedPostedDate || $parsedPostedDate->format('Y-m-d') !== $postedDate || $postedDate < $today
            || !in_array($postedTripType, ['one_way', 'round_trip'], true)
            || ($postedTripType === 'round_trip' && (!$parsedPostedReturnDate || $parsedPostedReturnDate->format('Y-m-d') !== $postedReturnDate || $postedReturnDate <= $postedDate))
            || $postedName === '' || $postedAddress === '' || $postedPhone === ''
            || !filter_var($postedEmail, FILTER_VALIDATE_EMAIL)
        ) {
            $bookingError = 'Tarkista lennon, päivämäärän ja yhteystietojen tiedot.';
        } else {
            $orderData = [
                'flight_id' => $postedFlightId,
                'trip_type' => $postedTripType,
                'departure_date' => $postedDate,
                'return_date' => $postedTripType === 'round_trip' ? $postedReturnDate : '',
                'ticket_count' => $postedTicketCount,
                'customer_name' => $postedName,
                'customer_address' => $postedAddress,
                'customer_email' => $postedEmail,
                'customer_phone' => $postedPhone,
            ];
            $flight = jc_get_flight($postedFlightId);

            if (!$flight) {
                $bookingError = 'Valittua lentoa ei löytynyt. Tee haku uudelleen.';
            } elseif ((int)$flight['available_seats'] < $postedTicketCount * ($postedTripType === 'round_trip' ? 2 : 1)) {
                $bookingError = 'Valitulla lennolla ei ole riittävästi vapaita paikkoja.';
            } else {
                $searchParams['destination_city'] = $flight['destination_city'];
                $searchParams['departure_period'] = strtolower($flight['departure_period']);
                $searchParams['departure_date'] = $postedDate;
                $searchParams['return_date'] = $postedTripType === 'round_trip' ? $postedReturnDate : '';
                $searchParams['trip_type'] = $postedTripType;
                $searchParams['ticket_count'] = $postedTicketCount;

                if ($action === 'review') {
                    $pendingOrder = $orderData;
                    $reviewFlight = $flight;
                } else {
                    $bookingStatus = jc_create_order($orderData);
                    if ($bookingStatus['status'] === 'success') {
                        $ticketOrder = $orderData;
                        $ticketFlight = $flight;
                        if (getenv('JC_SEND_EMAIL') === '1') {
                            $legs = $orderData['trip_type'] === 'round_trip' ? 2 : 1;
                            $ticketBody = implode(PHP_EOL, [
                                'JC Airlines - lentolippu',
                                'Tilausnumero: ' . $bookingStatus['order_id'],
                                'Matkustaja: ' . $orderData['customer_name'],
                                'Meno: ' . JC_DEPARTURE_CITY . ' -> ' . $flight['destination_city'],
                                'Lähtöpäivä ja aika: ' . $orderData['departure_date'] . ' ' . $flight['departure_period'],
                                'Lippuja: ' . $orderData['ticket_count'],
                                $orderData['trip_type'] === 'round_trip'
                                    ? 'Paluu: ' . $flight['destination_city'] . ' -> ' . JC_DEPARTURE_CITY . ', ' . $orderData['return_date']
                                    : '',
                                'Yhteensä: ' . number_format($flight['price'] * $orderData['ticket_count'] * $legs, 2, ',', ' ') . ' EUR',
                            ]);
                            $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8";
                            if (!mail($orderData['customer_email'], 'JC Airlines - lentolippu', $ticketBody, $headers)) {
                                error_log('JC Airlines ticket email could not be sent for order ' . $bookingStatus['order_id']);
                                $bookingStatus['message'] .= ' Tilaus tallentui, mutta sähköpostin lähetys epäonnistui.';
                            }
                        }
                    }
                }
            }
        }
    }
}

$flights = jc_get_flights($searchParams);
$minimumReturnDate = (new DateTime($searchParams['departure_date']))->modify('+1 day')->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Airlines | Etusivu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container navbar">
            <a href="index.php" class="brand" aria-label="JC Airlines etusivu">
                <span class="brand-mark">JC</span>
                <span>JC Airlines</span>
            </a>
            <nav class="nav-links" aria-label="Päävalikko">
                <a href="index.php" class="active">Etusivu</a>
                <a href="kalusto.php">Kalusto</a>
                <a href="kohteet.php">Kohteet</a>
                <a href="aspa.php">Asiakaspalvelu</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <h1>Lennä helpommin ja kauniimmin.</h1>
                    <p>JC Airlines yhdistää Suomen sisäiset ja kansainväliset reitit modernilla palvelulla, joustavilla lentoajoilla ja stressittömällä matkustuskokemuksella.</p>
                    <div class="hero-actions">
                        <a href="#haku" class="button primary">Hae lennot</a>
                        <a href="#varaus" class="button secondary">Varaa lippu</a>
                    </div>
                </div>

                <div class="search-panel" id="haku">
                    <h2>Hae lennot</h2>
                    <form action="index.php#lennot" method="get" class="booking-form">
                        <div class="form-grid">
                            <div class="field">
                                <label for="departure_city">Lähtöpaikka</label>
                                <input id="departure_city" type="text" value="<?= htmlspecialchars(JC_DEPARTURE_CITY) ?>" readonly>
                            </div>
                            <div class="field">
                                <label for="destination_city">Kohde</label>
                                <select id="destination_city" name="destination_city">
                                    <?php foreach ($destinationCities as $city): ?>
                                        <option value="<?= htmlspecialchars($city) ?>" <?= $searchParams['destination_city'] === $city ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label for="departure_date">Lähtöpäivä</label>
                                <input id="departure_date" name="departure_date" type="date" min="<?= $today ?>" value="<?= htmlspecialchars($searchParams['departure_date']) ?>">
                            </div>
                            <div class="field">
                                <label for="trip_type">Matkatyyppi</label>
                                <select id="trip_type" name="trip_type">
                                    <option value="one_way" <?= $searchParams['trip_type'] === 'one_way' ? 'selected' : '' ?>>Yhdensuuntainen</option>
                                    <option value="round_trip" <?= $searchParams['trip_type'] === 'round_trip' ? 'selected' : '' ?>>Meno-paluu</option>
                                </select>
                            </div>
                            <div class="field" id="search_return_date_field" <?= $searchParams['trip_type'] === 'round_trip' ? '' : 'hidden' ?>>
                                <label for="return_date">Paluupäivä</label>
                                <input id="return_date" name="return_date" type="date" min="<?= htmlspecialchars($minimumReturnDate) ?>" value="<?= htmlspecialchars($searchParams['return_date']) ?>">
                            </div>
                            <div class="field">
                                <label for="departure_period">Lennon ajankohta</label>
                                <select id="departure_period" name="departure_period">
                                    <option value="aamu" <?= $searchParams['departure_period'] === 'aamu' ? 'selected' : '' ?>>Aamu</option>
                                    <option value="päivä" <?= $searchParams['departure_period'] === 'päivä' ? 'selected' : '' ?>>Päivä</option>
                                    <option value="ilta" <?= $searchParams['departure_period'] === 'ilta' ? 'selected' : '' ?>>Ilta</option>
                                </select>
                            </div>
                            <div class="field full">
                                <label for="ticket_count">Lippujen määrä</label>
                                <input id="ticket_count" name="ticket_count" type="number" min="1" max="9" value="<?= (int)$searchParams['ticket_count'] ?>">
                            </div>
                        </div>
                        <div class="hero-actions" style="margin-top: 18px;">
                            <button type="submit" class="button primary">Hae vaihtoehtoja</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2>Suosituimmat reitit</h2>
                    <p>Tutustu tietokannan lentovaihtoehtoihin ja valitse itsellesi sopiva lähtöaika.</p>
                </div>
                <div class="feature-strip">
                    <div class="stat"><strong>35</strong>lentovaihtoehtoa</div>
                    <div class="stat"><strong>12</strong>kohdekaupunkia</div>
                    <div class="stat"><strong>3</strong>lähtöaikaa</div>
                    <div class="stat"><strong>1</strong>selkeä varausprosessi</div>
                </div>
            </div>
        </section>

        <section class="section" id="lennot" style="background: rgba(103, 209, 255, 0.04);">
            <div class="container">
                <div class="section-header">
                    <h2>Löydetyt lennot</h2>
                    <p>
                        <?= (int)count($flights) ?> lentoa reitille
                        <?= htmlspecialchars(JC_DEPARTURE_CITY . ' → ' . $searchParams['destination_city']) ?>,
                        <?= htmlspecialchars($searchParams['departure_date']) ?> ·
                        <?= htmlspecialchars(ucfirst($searchParams['departure_period'])) ?>.
                        Valitse lento varataksesi sen.
                    </p>
                </div>

                <?php if (!empty($bookingStatus['message'])): ?>
                    <div class="message <?= $bookingStatus['status'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($bookingStatus['message']) ?></div>
                <?php endif; ?>

                <?php if (!empty($bookingError)): ?>
                    <div class="message error"><?= htmlspecialchars($bookingError) ?></div>
                <?php endif; ?>

                <?php if (!empty($searchError)): ?>
                    <div class="message error"><?= htmlspecialchars($searchError) ?></div>
                <?php endif; ?>

                <?php if ($bookingCancelled): ?>
                    <div class="message">Tilaus peruttiin. Mitään ei tallennettu.</div>
                <?php endif; ?>

                <?php if (!$databaseAvailable): ?>
                    <div class="info-box">
                        <p>Yhteyttä lennot-tietokantaan ei saatu. Käynnistä MySQL XAMPPissa ja tuo database.sql phpMyAdminin kautta.</p>
                    </div>
                <?php elseif (!$flights): ?>
                    <div class="info-box">
                        <p>Valitsemillasi parametreilla ei löytynyt lennonvaihtoehtoja. Kokeile eri ajankohtaa tai kohdetta.</p>
                    </div>
                <?php else: ?>
                    <div class="flight-list">
                        <?php foreach ($flights as $flight): ?>
                            <article class="flight-item">
                                <div>
                                    <div class="flight-route"><?= htmlspecialchars($flight['departure_city']) ?> → <?= htmlspecialchars($flight['destination_city']) ?></div>
                                    <div class="flight-meta">
                                        <span>Kone: <?= htmlspecialchars($flight['flight_code']) ?></span>
                                        <span><?= htmlspecialchars($flight['country']) ?></span>
                                        <span><?= htmlspecialchars($flight['departure_date']) ?></span>
                                        <span><?= ucfirst(htmlspecialchars($flight['departure_period'])) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="badge">Vapaita paikkoja: <?= (int)$flight['available_seats'] ?></div>
                                </div>
                                <div class="price">
                                    <?= number_format((float)$flight['price'] * ($searchParams['trip_type'] === 'round_trip' ? 2 : 1), 2, ',', ' ') ?> €
                                    <small class="price-note">/ matkustaja</small>
                                </div>
                                <a href="#varaus" class="button primary" onclick="document.getElementById('booking_flight_id').value='<?= (int)$flight['id'] ?>'; document.getElementById('booking_departure_date').value='<?= htmlspecialchars($flight['departure_date'], ENT_QUOTES) ?>';">Valitse</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($ticketOrder && $ticketFlight): ?>
                    <div class="info-box ticket-preview">
                        <p class="eyebrow">Lentolipun esikatselu · sähköpostia ei lähetetä XAMPP-ympäristössä</p>
                        <h3>Tilaus #<?= (int)$bookingStatus['order_id'] ?> vahvistettu</h3>
                        <p><strong>Matkustaja:</strong> <?= htmlspecialchars($ticketOrder['customer_name']) ?></p>
                        <p><strong>Reitti:</strong> <?= htmlspecialchars(JC_DEPARTURE_CITY . ' → ' . $ticketFlight['destination_city']) ?></p>
                        <p><strong>Päivä ja aika:</strong> <?= htmlspecialchars($ticketOrder['departure_date'] . ' · ' . $ticketFlight['departure_period']) ?></p>
                        <?php if ($ticketOrder['trip_type'] === 'round_trip'): ?>
                            <p><strong>Paluureitti:</strong> <?= htmlspecialchars($ticketFlight['destination_city'] . ' → ' . JC_DEPARTURE_CITY) ?></p>
                            <p><strong>Paluupäivä:</strong> <?= htmlspecialchars($ticketOrder['return_date']) ?></p>
                        <?php endif; ?>
                        <p><strong>Lippuja:</strong> <?= (int)$ticketOrder['ticket_count'] ?></p>
                        <p><strong>Yhteensä:</strong> <?= number_format($ticketFlight['price'] * $ticketOrder['ticket_count'] * ($ticketOrder['trip_type'] === 'round_trip' ? 2 : 1), 2, ',', ' ') ?> €</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="varaus">
            <div class="container">
                <div class="section-header">
                    <h2>Varaa lippu</h2>
                    <p>Valitse lento, anna matkustajien ja tilaajan tiedot, tarkista yhteenveto ja vahvista tai peruuta tilaus.</p>
                </div>

                <div class="booking-box">
                    <?php if ($pendingOrder && $reviewFlight): ?>
                        <h3>Tarkista tilaus ennen vahvistamista</h3>
                        <dl class="order-summary">
                            <div><dt>Reitti</dt><dd><?= htmlspecialchars(JC_DEPARTURE_CITY . ' → ' . $reviewFlight['destination_city']) ?></dd></div>
                            <div><dt>Lähtöpäivä</dt><dd><?= htmlspecialchars($pendingOrder['departure_date']) ?></dd></div>
                            <div><dt>Matkatyyppi</dt><dd><?= $pendingOrder['trip_type'] === 'round_trip' ? 'Meno-paluu' : 'Yhdensuuntainen' ?></dd></div>
                            <?php if ($pendingOrder['trip_type'] === 'round_trip'): ?>
                                <div><dt>Paluupäivä</dt><dd><?= htmlspecialchars($pendingOrder['return_date']) ?></dd></div>
                                <div><dt>Paluureitti</dt><dd><?= htmlspecialchars($reviewFlight['destination_city'] . ' → ' . JC_DEPARTURE_CITY) ?></dd></div>
                            <?php endif; ?>
                            <div><dt>Lennon ajankohta</dt><dd><?= htmlspecialchars($reviewFlight['departure_period']) ?></dd></div>
                            <div><dt>Lentokone</dt><dd><?= htmlspecialchars($reviewFlight['aircraft']) ?></dd></div>
                            <div><dt>Lippuja</dt><dd><?= (int)$pendingOrder['ticket_count'] ?></dd></div>
                            <div><dt>Yhteensä</dt><dd><?= number_format($reviewFlight['price'] * $pendingOrder['ticket_count'] * ($pendingOrder['trip_type'] === 'round_trip' ? 2 : 1), 2, ',', ' ') ?> €</dd></div>
                            <div><dt>Tilaaja</dt><dd><?= htmlspecialchars($pendingOrder['customer_name']) ?></dd></div>
                            <div><dt>Osoite</dt><dd><?= htmlspecialchars($pendingOrder['customer_address']) ?></dd></div>
                            <div><dt>Sähköposti</dt><dd><?= htmlspecialchars($pendingOrder['customer_email']) ?></dd></div>
                            <div><dt>Puhelin</dt><dd><?= htmlspecialchars($pendingOrder['customer_phone']) ?></dd></div>
                        </dl>
                        <form method="post" class="hero-actions">
                            <?php foreach ($pendingOrder as $field => $value): ?>
                                <input type="hidden" name="<?= htmlspecialchars($field) ?>" value="<?= htmlspecialchars((string)$value) ?>">
                            <?php endforeach; ?>
                            <button type="submit" name="booking_action" value="confirm" class="button primary">Vahvista ja varaa</button>
                            <button type="submit" name="booking_action" value="cancel" class="button secondary cancel-button">Peruuta tilaus</button>
                        </form>
                    <?php else: ?>
                    <form method="post" class="booking-form">
                        <div class="inline-row">
                            <div class="field">
                                <label for="booking_flight_id">Valitse lento</label>
                                <select id="booking_flight_id" name="flight_id" required>
                                    <option value="">Valitse lento</option>
                                    <?php foreach ($flights as $flight): ?>
                                        <option value="<?= (int)$flight['id'] ?>">
                                            <?= htmlspecialchars($flight['departure_city'] . ' → ' . $flight['destination_city'] . ' / ' . $flight['departure_period'] . ' / ' . $flight['flight_code']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="inline-row">
                            <div class="field">
                                <label for="booking_departure_date">Lähtöpäivä</label>
                                <input id="booking_departure_date" name="departure_date" type="date" min="<?= $today ?>" value="<?= htmlspecialchars($searchParams['departure_date']) ?>" required>
                            </div>
                            <div class="field">
                                <label for="booking_trip_type">Matkatyyppi</label>
                                <select id="booking_trip_type" name="trip_type" required>
                                    <option value="one_way" <?= $searchParams['trip_type'] === 'one_way' ? 'selected' : '' ?>>Yhdensuuntainen</option>
                                    <option value="round_trip" <?= $searchParams['trip_type'] === 'round_trip' ? 'selected' : '' ?>>Meno-paluu</option>
                                </select>
                            </div>
                            <div class="field" id="booking_return_date_field" <?= $searchParams['trip_type'] === 'round_trip' ? '' : 'hidden' ?>>
                                <label for="booking_return_date">Paluupäivä</label>
                                <input id="booking_return_date" name="return_date" type="date" min="<?= htmlspecialchars($minimumReturnDate) ?>" value="<?= htmlspecialchars($searchParams['return_date']) ?>">
                            </div>
                            <div class="field">
                                <label for="booking_ticket_count">Lippujen lukumäärä</label>
                                <input id="booking_ticket_count" name="ticket_count" type="number" min="1" max="9" value="<?= (int)$searchParams['ticket_count'] ?>" required>
                            </div>
                        </div>

                        <div class="inline-row">
                            <div class="field">
                                <label for="customer_name">Matkustajien nimet / tilaaja</label>
                                <input id="customer_name" name="customer_name" type="text" placeholder="Matti Meikäläinen, Maija Meikäläinen" required>
                            </div>
                            <div class="field">
                                <label for="customer_phone">Puhelinnumero</label>
                                <input id="customer_phone" name="customer_phone" type="tel" placeholder="040 123 4567" required>
                            </div>
                        </div>

                        <div class="field">
                            <label for="customer_address">Osoite</label>
                            <input id="customer_address" name="customer_address" type="text" placeholder="Esimerkkitie 12, 00100 Helsinki" required>
                        </div>

                        <div class="field">
                            <label for="customer_email">Sähköposti</label>
                            <input id="customer_email" name="customer_email" type="email" placeholder="matti@example.com" required>
                        </div>

                        <button type="submit" name="booking_action" value="review" class="button primary">Tarkista tilaus</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <div class="brand" style="margin-bottom: 12px;">
                    <span class="brand-mark">JC</span>
                    <span>JC Airlines</span>
                </div>
                <p>Suomen elegantein lentomatkakokemus modernilla palvelulla.</p>
            </div>
            <div>
                <h4>Yhteystiedot</h4>
                <ul>
                    <li>+358 40 300 4500</li>
                    <li>asiakaspalvelu@jairlines.fi</li>
                    <li>Vantaa, Suomi</li>
                </ul>
            </div>
            <div>
                <h4>Palvelut</h4>
                <ul>
                    <li>Turvallinen varaus</li>
                    <li>Matkustajan tiedot</li>
                    <li>Reittipalvelu</li>
                </ul>
            </div>
        </div>
    </footer>
    <script>
        function setupTripDates(typeId, dateFieldId, returnId, departureId) {
            const type = document.getElementById(typeId);
            const wrapper = document.getElementById(dateFieldId);
            const returnDate = document.getElementById(returnId);
            const departureDate = document.getElementById(departureId);

            if (!type || !wrapper || !returnDate || !departureDate) {
                return;
            }

            const update = () => {
                const isRoundTrip = type.value === 'round_trip';
                wrapper.hidden = !isRoundTrip;
                returnDate.required = isRoundTrip;
                const parts = departureDate.value.split('-').map(Number);
                const nextDay = new Date(parts[0], parts[1] - 1, parts[2] + 1);
                returnDate.min = [
                    nextDay.getFullYear(),
                    String(nextDay.getMonth() + 1).padStart(2, '0'),
                    String(nextDay.getDate()).padStart(2, '0')
                ].join('-');
            };

            type.addEventListener('change', update);
            departureDate.addEventListener('change', update);
            update();
        }

        setupTripDates('trip_type', 'search_return_date_field', 'return_date', 'departure_date');
        setupTripDates('booking_trip_type', 'booking_return_date_field', 'booking_return_date', 'booking_departure_date');
    </script>
</body>
</html>
