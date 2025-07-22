<?php
$host = 'localhost';
$dbname = 'roverchf_ecran';
$user = 'roverchf_rovseb';
$password = 'Sroverch77390'; // À adapter selon ton environnement

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
