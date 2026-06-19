<?php
header('Content-Type: text/html; charset=utf-8');
require 'kapcsolat.php';
session_start();

$hiba_uzenet = $_SESSION['hiba'] ?? null;
$siker_uzenet = $_SESSION['siker'] ?? null;

unset($_SESSION['hiba']);
unset($_SESSION['siker']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $azonosito = trim($_POST['azonosito'] ?? '');
    $jelszo = $_POST['jelszo'] ?? '';

    $stmt = $pdo->prepare("SELECT id, felhasznalonev, jelszo FROM felhasznalok WHERE felhasznalonev = :azn OR email = :azn");
    $stmt->execute(['azn' => $azonosito]);
    $felhasznalo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($felhasznalo && password_verify($jelszo, $felhasznalo['jelszo'])) {
        
        $_SESSION['bejelentkezve'] = true;
        $_SESSION['felhasznalo_id'] = $felhasznalo['id'];
        $_SESSION['felhasznalonev'] = $felhasznalo['felhasznalonev'];
        
        header("Location: dashboard.php"); 
        exit;
        
    } else {
        $hiba_uzenet = "Hibás felhasználónév/e-mail vagy jelszó.";
        $_SESSION['hiba'] = $hiba_uzenet;
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
</head>
<body>

    <h1>Bejelentkezés</h1>

    <?php if ($hiba_uzenet): ?>
        <p style="color: red;"><?php echo htmlspecialchars($hiba_uzenet); ?></p>
    <?php endif; ?>

    <?php if ($siker_uzenet): ?>
        <p style="color: green;"><?php echo htmlspecialchars($siker_uzenet); ?></p>
    <?php endif; ?>

    <form action="index.php" method="POST">
        
        <label for="azonosito">Felhasználónév vagy E-mail:</label><br>
        <input type="text" id="azonosito" name="azonosito" required><br><br>

        <label for="jelszo">Jelszó:</label><br>
        <input type="password" id="jelszo" name="jelszo" required><br><br>

        <button type="submit">Bejelentkezés</button>
    </form>
    
    <p>Nincs még fiókod? 
        <a href="regisztracio.php">
            <button>Regisztráció</button>
        </a>
    </p>

</body>
</html>