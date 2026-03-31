<?php
$host = "localhost";
$user = "root"; // sesuaikan dengan user mysql anda
$pass = "";     // sesuaikan dengan password mysql anda
$db   = "wukong_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
?>