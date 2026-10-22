<?php
require __DIR__ . '/db.php';

$destinationCities = jc_city_list();
$searchParams = [
    'departure_city' => $_GET['departure_city'] ?? 'Helsinki',
    'destination_city' => $_GET['destination_city'] ?? 'Tampere',
    'departure_date' => $_GET['departure_date'] ?? date('Y-m-d'),
    'departure_period' => $_GET['departure_period'] ?? 'aamu',
    'ticket_count' => $_GET['ticket_count'] ?? 1,
];

$flights = jc_get_flights($searchParams);

$bookingStatus = null;
$bookingError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $ticketCount = max(1, (int)($_POST['ticket_count'] ?? 1));
    $required = ['customer_name', 'customer_address', 'customer_email', 'customer_phone', 'destination_city'];
    $missing = [];
    foreach ($required as $field) {
        if (empty(trim((string)($_POST[$field] ?? '')))) {
            $missing[] = $field;
        }
    }

    if ($missing) {
        $bookingError = 'Täytä kaikki vaaditut kentät ennen tilauksen vahvistamista.';
    } else {
        $payload = [
            'departure_city' => $_POST['departure_city'] ?? 'Helsinki',
            'destination_city' => $_POST['destination_city'],
            'departure_date' => $_POST['departure_date'],
            'departure_period' => $_POST['departure_period'],
            'ticket_count' => $ticketCount,
            'customer_name' => trim($_POST['customer_name']),
            'customer_address' => trim($_POST['customer_address']),
            'customer_email' => trim($_POST['customer_email']),
            'customer_phone' => trim($_POST['customer_phone']),
        ];

        $bookingStatus = jc_create_order($payload);
    }
}

$today = date('Y-m-d');
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
                    <form action="index.php" method="get" class="booking-form">
                        <div class="form-grid">
                            <div class="field">
                                <label for="departure_city">Lähtöpaikka</label>
                                <select id="departure_city" name="departure_city">
                                    <?php foreach ($destinationCities as $city): ?>
                                        <option value="<?= htmlspecialchars($city) ?>" <?= $searchParams['departure_city'] === $city ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                    <p>Valitse matkasi haluamastasi kaupungista, rakennamme helpoimman lähdön ja vakuuttavan palvelukokemuksen.</p>
                </div>
                <div class="feature-strip">
                    <div class="stat"><strong>32+</strong>reitit</div>
                    <div class="stat"><strong>4.8/5</strong>asiakastyytyväisyys</div>
                    <div class="stat"><strong>90 min</strong>nopein vaihtoyhteys</div>
                    <div class="stat"><strong>24/7</strong>asiakaspalvelu</div>
                </div>
            </div>
        </section>

        <section class="section" style="background: rgba(103, 209, 255, 0.04);">
            <div class="container">
                <div class="section-header">
                    <h2>Löydetyt lennot</h2>
                    <p>Valaise matkasi parhaat vaihtoehdot päivän ja kohteen mukaan.</p>
                </div>

                <?php if (!empty($bookingStatus['message'])): ?>
                    <div class="message <?= $bookingStatus['status'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($bookingStatus['message']) ?></div>
                <?php endif; ?>

                <?php if (!empty($bookingError)): ?>
                    <div class="message error"><?= htmlspecialchars($bookingError) ?></div>
                <?php endif; ?>

                <?php if (!$flights): ?>
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
                                        <span><?= htmlspecialchars($flight['flight_code']) ?></span>
                                        <span><?= htmlspecialchars($flight['departure_date']) ?></span>
                                        <span><?= ucfirst(htmlspecialchars($flight['departure_period'])) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="badge">Vapaita paikkoja: <?= (int)$flight['available_seats'] ?></div>
                                </div>
                                <div class="price"><?= number_format((float)$flight['price'], 2, ',', ' ') ?> €</div>
                                <a href="#varaus" class="button primary" onclick="document.getElementById('booking_departure_city').value='<?= htmlspecialchars($flight['departure_city']) ?>'; document.getElementById('booking_destination_city').value='<?= htmlspecialchars($flight['destination_city']) ?>'; document.getElementById('booking_departure_date').value='<?= htmlspecialchars($flight['departure_date']) ?>'; document.getElementById('booking_departure_period').value='<?= htmlspecialchars($flight['departure_period']) ?>';">Valitse</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="varaus">
            <div class="container">
                <div class="section-header">
                    <h2>Varaa lippu</h2>
                    <p>Syötä matkustajan tiedot ja vahvista varaus. Tilausaika ja sähköpostitiedot tallennetaan turvallisesti.</p>
                </div>

                <div class="booking-box">
                    <form method="post" class="booking-form">
                        <div class="inline-row">
                            <div class="field">
                                <label for="booking_departure_city">Lähtöpaikka</label>
                                <select id="booking_departure_city" name="departure_city" required>
                                    <?php foreach ($destinationCities as $city): ?>
                                        <option value="<?= htmlspecialchars($city) ?>" <?= ($searchParams['departure_city'] === $city) ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label for="booking_destination_city">Kohde</label>
                                <select id="booking_destination_city" name="destination_city" required>
                                    <?php foreach ($destinationCities as $city): ?>
                                        <option value="<?= htmlspecialchars($city) ?>" <?= ($searchParams['destination_city'] === $city) ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
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
                                <label for="booking_departure_period">Aikajakso</label>
                                <select id="booking_departure_period" name="departure_period" required>
                                    <option value="aamu" <?= $searchParams['departure_period'] === 'aamu' ? 'selected' : '' ?>>Aamu</option>
                                    <option value="päivä" <?= $searchParams['departure_period'] === 'päivä' ? 'selected' : '' ?>>Päivä</option>
                                    <option value="ilta" <?= $searchParams['departure_period'] === 'ilta' ? 'selected' : '' ?>>Ilta</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="booking_ticket_count">Lippujen lukumäärä</label>
                                <input id="booking_ticket_count" name="ticket_count" type="number" min="1" max="9" value="<?= (int)$searchParams['ticket_count'] ?>" required>
                            </div>
                        </div>

                        <div class="inline-row">
                            <div class="field">
                                <label for="customer_name">Matkustajan nimi</label>
                                <input id="customer_name" name="customer_name" type="text" placeholder="Matti Meikäläinen" required>
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

                        <button type="submit" name="place_order" class="button primary">Vahvista tilaus</button>
                    </form>
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
</body>
</html>
