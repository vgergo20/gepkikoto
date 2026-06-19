<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

$hiba_uzenet = $_SESSION['hiba'] ?? null;
$siker_uzenet = $_SESSION['siker'] ?? null;
$input = $_SESSION['input'] ?? [];

unset($_SESSION['hiba']);
unset($_SESSION['siker']);
unset($_SESSION['input']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
</head>
<body>

    <h1>Regisztráció</h1>

    <?php if ($hiba_uzenet): ?>
        <p style="color: red;"><?php echo htmlspecialchars($hiba_uzenet); ?></p>
    <?php endif; ?>

    <?php if ($siker_uzenet): ?>
        <p style="color: green;"><?php echo htmlspecialchars($siker_uzenet); ?></p>
    <?php endif; ?>

    <form action="regisztracio-folyamat.php" method="POST">
        
        <label for="felhasznalonev">Felhasználónév:</label><br>
        <input type="text" id="felhasznalonev" name="felhasznalonev" 
               value="<?php echo htmlspecialchars($input['felhasznalonev'] ?? ''); ?>" required><br><br>

        <label for="email">E-mail cím:</label><br>
        <input type="email" id="email" name="email" 
               value="<?php echo htmlspecialchars($input['email'] ?? ''); ?>" required><br><br>

        <label for="jelszo">Jelszó:</label><br>
        <input type="password" id="jelszo" name="jelszo" required><br><br>

        <label for="jelszo_megerosites">Jelszó megerősítése:</label><br>
        <input type="password" id="jelszo_megerosites" name="jelszo_megerosites" required><br><br>

        <button type="submit">Regisztráció</button>
    </form>
    
    <p>Már van fiókod? <a href="index.php">Jelentkezz be!</a></p>

</body>
</html>