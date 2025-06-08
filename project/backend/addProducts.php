<?php
session_start();
require_once '../backend/db_connect.php';

// Verifică dacă utilizatorul este admin
if (!isset($_SESSION['user_id']) || $_SESSION['admin'] != 1) {
    header("Location: /login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Preluare date din formular
    $nume = $_POST['nume'];
    $pret_baza = $_POST['pret_baza'];
    $descriere = $_POST['descriere'];
    $ingrediente = $_POST['ingrediente'];
    
    // Procesare imagine
    $imagine = 'default.jpg'; // Imagine implicită
    if ($_FILES['imagine']['error'] == 0) {
        $extensie = pathinfo($_FILES['imagine']['name'], PATHINFO_EXTENSION);
        $imagine = uniqid() . '.' . $extensie;
        move_uploaded_file($_FILES['imagine']['tmp_name'], '../../assets/images/' . $imagine);
    }

    // Adaugă produsul în baza de date
    $sql = "INSERT INTO produse (nume, pret_baza, descriere, ingrediente, imagine) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsss", $nume, $pret_baza, $descriere, $ingrediente, $imagine);
    $stmt->execute();
    $produs_id = $stmt->insert_id;
    $stmt->close();

    // Adaugă dimensiunile
    if (isset($_POST['dimensiune']) && isset($_POST['pret_dimensiune'])) {
        for ($i = 0; $i < count($_POST['dimensiune']); $i++) {
            $dimensiune = $_POST['dimensiune'][$i];
            $pret = $_POST['pret_dimensiune'][$i];
            
            if (!empty($dimensiune)) {
                $sql = "INSERT INTO dimensiuni (produs_id, nume, pret) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isd", $produs_id, $dimensiune, $pret);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: list.php?success=1");
    exit();
}

include '../../includes/header.php';
?>

<h1>Adăugare Produs Nou</h1>

<form method="post" enctype="multipart/form-data">
    <label>Nume Produs:</label>
    <input type="text" name="nume" required>

    <label>Preț Bază (RON):</label>
    <input type="number" step="0.01" name="pret_baza" required>

    <label>Descriere:</label>
    <textarea name="descriere" required></textarea>

    <label>Ingrediente:</label>
    <textarea name="ingrediente"></textarea>

    <label>Imagine:</label>
    <input type="file" name="imagine" accept="image/*">

    <h3>Dimensiuni</h3>
    <div id="dimensiuni-container">
        <div class="dimensiune-row">
            <input type="text" name="dimensiune[]" placeholder="Dimensiune (ex: Mic)">
            <input type="number" step="0.01" name="pret_dimensiune[]" placeholder="Preț">
            <button type="button" class="sterge-dimensiune">Șterge</button>
        </div>
    </div>
    <button type="button" id="adauga-dimensiune">+ Adaugă Dimensiune</button>

    <button type="submit">Salvează Produs</button>
</form>

<script>
document.getElementById('adauga-dimensiune').addEventListener('click', function() {
    const container = document.getElementById('dimensiuni-container');
    const newRow = document.createElement('div');
    newRow.className = 'dimensiune-row';
    newRow.innerHTML = `
        <input type="text" name="dimensiune[]" placeholder="Dimensiune (ex: Mic)">
        <input type="number" step="0.01" name="pret_dimensiune[]" placeholder="Preț">
        <button type="button" class="sterge-dimensiune">Șterge</button>
    `;
    container.appendChild(newRow);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('sterge-dimensiune')) {
        e.target.parentElement.remove();
    }
});
</script>

<?php include '../../includes/footer.php'; ?>