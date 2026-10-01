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
        
