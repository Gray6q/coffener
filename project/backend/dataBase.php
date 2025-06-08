<?php
$servername = "localhost";  // sau IP-ul serverului tău MySQL
$username = "root";         // numele de utilizator MySQL
$password = "";             // parola MySQL (dacă ai)
$dbname = "cofee_store_coffener";

// 1. Creare conexiune fără DB pentru a crea baza de date
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

// 2. Creare baza de date dacă nu există
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Baza de date creată sau deja există.<br>";
} else {
    die("Eroare creare DB: " . $conn->error);
}

// 3. Conectare la baza de date nou creată
$conn->select_db($dbname);

// 4. Creare tabele

// Tabel produse
$sql_produse = "CREATE TABLE IF NOT EXISTS produse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    pret_baza DECIMAL(8,2) NOT NULL,
    descriere TEXT,
    ingrediente TEXT,
    delivery_info TEXT,
    imagine VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql_produse)) {
    die("Eroare creare tabela produse: " . $conn->error);
}

// Tabel dimensiuni
$sql_dimensiuni = "CREATE TABLE IF NOT EXISTS dimensiuni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produs_id INT NOT NULL,
    nume VARCHAR(10) NOT NULL,
    pret DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (produs_id) REFERENCES produse(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql_dimensiuni)) {
    die("Eroare creare tabela dimensiuni: " . $conn->error);
}

// Tabel utilizatori
$sql_utilizatori = "CREATE TABLE IF NOT EXISTS utilizatori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    parola VARCHAR(255) NOT NULL,
    admin TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql_utilizatori)) {
    die("Eroare creare tabela utilizatori: " . $conn->error);
}

// Tabel cos
$sql_cos = "CREATE TABLE IF NOT EXISTS cos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    produs_id INT NOT NULL,
    dimensiune_id INT NOT NULL,
    cantitate INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (produs_id) REFERENCES produse(id) ON DELETE CASCADE,
    FOREIGN KEY (dimensiune_id) REFERENCES dimensiuni(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql_cos)) {
    die("Eroare creare tabela cos: " . $conn->error);
}

// Tabel review
$sql_review = "CREATE TABLE IF NOT EXISTS review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produs_id INT NOT NULL,
    user_id INT NOT NULL,
    comentariu TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    FOREIGN KEY (produs_id) REFERENCES produse(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES utilizatori(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sql_review)) {
    die("Eroare creare tabela review: " . $conn->error);
}

echo "Toate tabelele au fost create cu succes.";

$conn->close();
?>
