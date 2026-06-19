<?php
require 'kapcsolat.php';

$id = "C:\\xampp\\htdocs\\files\\shared\\";

$gep_azonosito = trim($_GET['gep_azonosito'] ?? '');

if (empty($gep_azonosito)) {
	http_response_code(400);
	die("Hiba: Hianyzik a gep_azonosito.");
}

$file_name = $gep_azonosito . '.txt';
$file_path = $id . $file_name;

$anydesk_id = trim(file_get_contents($file_path));

$anydesk_id = substr($anydesk_id, 2);

$anydesk_id = trim($anydesk_id);

if (empty($anydesk_id)) {
    http_response_code(500);
    die("Hiba: Az ID fajl ures volt.");
}

try {
	$stmt = $pdo->prepare("
	UPDATE berlesek
	SET telepitve = 1, anydesk_id = ?
	WHERE gep_azonosito = ?
	");

	$stmt->execute([$anydesk_id, $gep_azonosito]);

} catch (\PDOException $e) {
	http_response_code(500);
	die("Hiba tortent az adatbazis frissitese soran: " . $e->getMessage());
}
?>