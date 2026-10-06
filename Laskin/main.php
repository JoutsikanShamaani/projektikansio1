<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Laskin</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

        <h1>Laskin</h1>

        <!-- Lomake laskimelle -->
         <form method="post" action="">
            <label for="luku1">Luku 1:</label>
            <input type="number step="any" id="luku1" name="luku1" required
                value="<?php echo isset($_POST["luku1"]) ? htmlspecialchars($_POST["luku1"]) : ''; ?>">

            <label for="luku2">Luku 2:</label>
            <input type="number step="any" id="luku2" name="luku2" required
                value="<?php echo isset($_POST["luku2"]) ? htmlspecialchars($_POST["luku2"]) : ''; ?>">

            <button type="submit">Laske</button>
        </form>

        <?php

        // käsitellään lomake
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // otetaan vastaan käyttäjän syöte
        $luku1 = $_POST["luku1"];
        $luku2 = $_POST["luku2"];
       
       
        // tarkistetaan että molemmat syötteet ovat numeroita
        if (!is_numeric($luku1) || !is_numeric($luku2)) {
            echo '<div class="error">Virhe: Syötteiden tulee olla numeroita.</div>';
        }   else {

        // muunnetaan oikeiksi luvuiksi
        $a = floatval($luku1);
        $b = floatval($luku2);

        // suoritetaan laskutoimitukset
        $summa = $a + $b;
        $erotus = $a - $b;
        $tulo = $a * $b;
        $osamaara = $b != 0 ? $a / $b : '<div class="error">Virhe: Nollalla jakaminen ei ole sallittua.</div>';
        }

         // Tulostetaan tulokset käyttäjälle
          echo '<div class="results">';
          echo '<h2>Tulokset</h2>';
          echo '<p>' . htmlspecialchars($luku1) . ' + ' . htmlspecialchars($luku2) . ' = <strong>' . $summa . '</strong></p>';
          echo '<p>' . htmlspecialchars($luku1) . ' - ' . htmlspecialchars($luku2) . ' = <strong>' . $erotus . '</strong></p>';
          echo '<p>' . htmlspecialchars($luku1) . ' * ' . htmlspecialchars($luku2) . ' = <strong>' . $tulo . '</strong></p>';
          echo '<p>' . htmlspecialchars($luku1) . ' / ' . htmlspecialchars($luku2) . ' = <strong>' . $osamaara . '</strong></p>';
          echo '</div>';
      }
  
  ?>

</body>
</html>
       
       