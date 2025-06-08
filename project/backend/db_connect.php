<?php
// config.php - Configurare conexiune bază de date
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cofee_store_coffener";

function getDBConnection() {
    global $servername, $username, $password, $dbname;
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Conexiunea a eșuat: " . $conn->connect_error);
    }
    
    // Setează charset-ul pentru caractere românești
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Funcție pentru securizarea input-urilor
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Funcție pentru upload imagini
function uploadImage($file, $target_dir = "uploads/") {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid() . "." . $imageFileType;
    
    // Verifică dacă fișierul este o imagine
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Verifică dimensiunea fișierului (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Permite doar anumite formate
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return false;
    }
}
?>