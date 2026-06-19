<?php
require 'kapcsolat.php';
session_start();

function hatterben($cmd) {
    pclose(popen("start /B " . $cmd, "r")); 
}

$letrehoz = "C:/xampp/htdocs/files/letrehoz.ps1";
$indit = "C:/xampp/htdocs/files/start.ps1";
$leallit = "C:/xampp/htdocs/files/stop.ps1";
$torol = "C:/xampp/htdocs/files/delete.ps1";

if (!isset($_SESSION['bejelentkezve']) || $_SESSION['bejelentkezve'] !== true) {
    header("Location: index.php");
    exit;
}

$felhasznalo_id = $_SESSION['felhasznalo_id'];
$action = $_REQUEST['action'] ?? '';

$gep_azonosito_input = trim($_REQUEST['gep_azonosito'] ?? null);


if ($action === 'berel') {
    $gep_nev_felhasznalo_altal = $gep_azonosito_input;
    
    if (empty($gep_nev_felhasznalo_altal)) {
        $_SESSION['hiba'] = "A gépnév megadása kötelező.";
        header("Location: dashboard.php");
        exit;
    }
    
    $VMName = $felhasznalo_id . '-' . $gep_nev_felhasznalo_altal;

    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM berlesek WHERE gep_azonosito = ?");
        $stmt_check->execute([$VMName]); 

        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['hiba'] = "Ez az azonosító már foglalt.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO berlesek (felhasznalo_id, gep_azonosito, gep_nev) VALUES (?, ?, ?)");
            $stmt->execute([$felhasznalo_id, $VMName, $gep_nev_felhasznalo_altal]);
            $stmt = $pdo->prepare("INSERT INTO berlesek (felhasznalo_id, gep_azonosito, gep_nev, allapot) VALUES (?, ?, ?, 0)");
            $stmt = $pdo->prepare("INSERT INTO berlesek (felhasznalo_id, gep_azonosito, gep_nev, allapot, telepitve) VALUES (?, ?, ?, ?, 0)");

            $script_safe = escapeshellarg($letrehoz);
            $vmname_safe = escapeshellarg($VMName);
            $command = "powershell.exe -ExecutionPolicy Bypass -File $script_safe -VM_NEV $vmname_safe";
            hatterben($command);
        }
    } catch (\PDOException $e) {
        $_SESSION['hiba'] = "Hiba történt a bérlés/telepítés közben.";
    }
    
    header("Location: dashboard.php");
    exit;

} elseif ($action === 'inditas' || $action === 'leallit' || $action === 'torol') {
    $VMName = $gep_azonosito_input; 
    
    if (empty($VMName)) {
        $_SESSION['hiba'] = "Hiányzó gépazonosító a művelethez.";
        header("Location: dashboard.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM berlesek WHERE felhasznalo_id = ? AND gep_azonosito = ?");
    $stmt->execute([$felhasznalo_id, $VMName]);
    if ($stmt->fetchColumn() == 0 && $action !== 'leallit') {
           header("Location: dashboard.php");
        exit;
    }

    $ps_script = '';
    $action_display = '';

    if ($action === 'inditas') {
        $ps_script = $indit;
        $action_display = "indítási";
	$stmt_update = $pdo->prepare("UPDATE berlesek SET allapot = ? WHERE felhasznalo_id = ? AND gep_azonosito = ?");
        $stmt_update->execute([1, $felhasznalo_id, $VMName]);
    } elseif ($action === 'leallit') {
        $ps_script = $leallit;
        $action_display = "leállítási";
	$stmt_update = $pdo->prepare("UPDATE berlesek SET allapot = ? WHERE felhasznalo_id = ? AND gep_azonosito = ?");
        $stmt_update->execute([0, $felhasznalo_id, $VMName]);
    } elseif ($action === 'torol') {
        $ps_script = $torol;
        $action_display = "törlési";
    }

    $script_safe = escapeshellarg($ps_script);
    $vmname_safe = escapeshellarg($VMName);
    $command = "powershell.exe -ExecutionPolicy Bypass -File $script_safe -VM_NEV $vmname_safe";
    
    $output = shell_exec($command);

    if ($action === 'torol') {
        $stmt = $pdo->prepare("DELETE FROM berlesek WHERE felhasznalo_id = ? AND gep_azonosito = ?");
        $stmt->execute([$felhasznalo_id, $VMName]);
    }
    header("Location: dashboard.php");
    exit;
}

header("Location: dashboard.php");
exit;