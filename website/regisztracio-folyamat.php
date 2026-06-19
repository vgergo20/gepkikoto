<?php
require 'kapcsolat.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $felhasznalonev = trim($_POST['felhasznalonev'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $jelszo = $_POST['jelszo'] ?? '';
    $jelszo_megerosites = $_POST['jelszo_megerosites'] ?? '';

    if (empty($felhasznalonev) || empty($email) || empty($jelszo) || empty($jelszo_megerosites)) {
        $_SESSION['hiba'] = "Minden mező kitöltése kötelező.";
    } elseif ($jelszo !== $jelszo_megerosites) {
        $_SESSION['hiba'] = "A két jelszó nem egyezik meg.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['hiba'] = "Érvénytelen e-mail cím formátum.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM felhasznalok WHERE felhasznalonev = :fnv OR email = :email");
        $stmt->execute(['fnv' => $felhasznalonev, 'email' => $email]);

        if ($stmt->fetch()) {
            $_SESSION['hiba'] = "Ez a felhasználónév vagy e-mail cím már foglalt.";
        } else {
            $hashed_jelszo = password_hash($jelszo, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO felhasznalok (felhasznalonev, email, jelszo) VALUES (:fnv, :email, :jelszo)");
            
            if ($stmt->execute(['fnv' => $felhasznalonev, 'email' => $email, 'jelszo' => $hashed_jelszo])) {
                $_SESSION['siker'] = "Sikeres regisztráció! Most már bejelentkezhetsz.";
                header("Location: index.php"); 
                exit;
            } else {
                $_SESSION['hiba'] = "Hiba történt a regisztráció során az adatbázisban.";
            }
        }
    }
    
    $_SESSION['input'] = $_POST;
    header("Location: regisztracio.php");
    exit;

} else {
    header("Location: regisztracio.php");
    exit;
}