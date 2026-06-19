<?php
header('Content-Type: text/html; charset=utf-8');
require 'kapcsolat.php';
session_start();

if (!isset($_SESSION['bejelentkezve']) || $_SESSION['bejelentkezve'] !== true) {
    header("Location: index.php");
    exit;
}

$felhasznalo_id = $_SESSION['felhasznalo_id'];
$felhasznalonev = $_SESSION['felhasznalonev'];

$stmt = $pdo->prepare("
    SELECT gep_azonosito, allapot, gep_nev, telepitve, anydesk_id
    FROM berlesek
    WHERE felhasznalo_id = :userid
    ORDER BY gep_azonosito
");
$stmt->execute(['userid' => $felhasznalo_id]);
$berelt_gepek = $stmt->fetchAll(PDO::FETCH_ASSOC);

$siker_uzenet = $_SESSION['siker'] ?? null;
$hiba_uzenet = $_SESSION['hiba'] ?? null;
unset($_SESSION['siker']);
unset($_SESSION['hiba']);
$is_installing = false;

foreach ($berelt_gepek as $gep) {
    if (((int)$gep['telepitve'] ?? 1) === 0) { 
        $is_installing = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Irányítópult</title>
    <?php 
    if ($is_installing): ?>
        <meta http-equiv="refresh" content="5"> 
    <?php endif; ?>
</head>
<body>
    <h1>Üdvözöllek, <?php echo htmlspecialchars($felhasznalonev); ?>!</h1>
    
    <?php if ($hiba_uzenet): ?>
        <p style="color: red;"><?php echo htmlspecialchars($hiba_uzenet); ?></p>
    <?php endif; ?>
    
    <?php if ($siker_uzenet): ?>
        <p style="color: green;"><?php echo htmlspecialchars($siker_uzenet); ?></p>
    <?php endif; ?>

    <h2>Bérelt gépeim</h2>
    <?php if (count($berelt_gepek) > 0): ?>
        <ul>
            <?php foreach ($berelt_gepek as $gep): ?>
                <?php
                    $is_running = ($gep['allapot'] == 1);
		    $is_installed = ($gep['telepitve'] == 1);
		    $show_buttons = $is_installed;
                    if ($is_installed){
                    	if ($is_running) {
                        	$allapot_szoveg = 'A gép bekapcsolva';
                        	$allapot_szin = 'green';
                        	$inditas_disabled = 'disabled';
                        	$leallitas_disabled = '';
                    	} else {
                        	$allapot_szoveg = 'A gép kikapcsolva';
                        	$allapot_szin = 'red';
                        	$inditas_disabled = '';
                        	$leallitas_disabled = 'disabled';
                    }
		    } else {
			$allapot_szoveg = 'Gép telepítése folyamatban (Kérlek várj)';
                        $allapot_szin = 'blue';
		    }
                ?>
                <li>
                    <strong>Gép neve: <?php echo htmlspecialchars($gep['gep_nev']); ?></strong> 
                    
                    (<strong style="color: <?php echo $allapot_szin; ?>;"><?php echo $allapot_szoveg; ?></strong>)
		    <?php if ($is_installed && !empty($gep['anydesk_id'])): ?>
                        <br>AnyDesk ID: <strong><?php echo htmlspecialchars($gep['anydesk_id']); ?></strong>
                    <?php elseif ($is_installed): ?>
                        <br>AnyDesk ID: <strong>Nincs beállítva</strong>
                    <?php endif; ?>
                    
                    <p style="margin-top: 5px;">
                    <?php if ($show_buttons): ?>
                        <a href="berles-folyamat.php?action=inditas&gep_azonosito=<?php echo urlencode($gep['gep_azonosito']); ?>">
                            <button style="background-color: green; color: white;" <?php echo $inditas_disabled; ?>>Indítás</button>
                        </a>
                        
                        <a href="berles-folyamat.php?action=leallit&gep_azonosito=<?php echo urlencode($gep['gep_azonosito']); ?>" style="text-decoration: none;">
                            <button style="background-color: orange; color: white; margin-right: 10px;" <?php echo $leallitas_disabled; ?>>Leállítás</button>
                        </a>
                        
                        <a href="berles-folyamat.php?action=torol&gep_azonosito=<?php echo urlencode($gep['gep_azonosito']); ?>">
                            <button style="background-color: red; color: white; margin-right: 10px;">Gép törlése</button>
                        </a>
		    <?php endif; ?>
                    </p>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Jelenleg nincs bérelt géped.</p>
    <?php endif; ?>

    <hr>
    
    <h2>Új gép bérlése</h2>

    <p>Kérlek, adj egy nevet az új gépednek:</p>
            
    <form action="berles-folyamat.php" method="POST">
        <input type="hidden" name="action" value="berel">
        
        <label for="gep_nev">Gép neve:</label><br>
        <input type="text" id="gep_nev" name="gep_azonosito" required><br><br>
        
        <button type="submit" style="background-color: blue; color: white;">Bérlés és telepítés</button>
    </form>
    
    <p><a href="kijelentkezes.php">Kijelentkezés</a></p>
</body>
</html>