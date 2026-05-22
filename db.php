<?php
// Upravené pro školu. V potřebě MAMP nebo XAMPP je potřeba dát root / root a název databáze
$servername = "localhost";
$username = "capjan1";
$password = "";
$dbname = "capjan1";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
