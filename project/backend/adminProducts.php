<?php
// admin_products.php - Pagina pentru gestionarea produselor (doar pentru admin)
require_once 'db_connect.php';
session_start();

// Verifică dacă utilizatorul este admin (temporar comentat pentru testare)
/*
if (!isset($_SESSION['user_id']) || $_SESSION['admin'] != 1) {
    header("Location: login.php");
    exit();
}
*/

$conn = getDBConnection();
$message = "";

// Procesarea formularului pentru adăugare/editare produs
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $nume = sanitizeInput($_POST['nume']);
                $pret_baza = floatval($_POST['pret_baza']);
                $descriere = sanitizeInput($_POST['descriere']);
                $ingrediente = sanitizeInput($_POST['ingrediente']);
                $delivery_info = sanitizeInput($_POST['delivery_info']);
                
                $imagine = "";
                if (isset($_FILES['imagine']) && $_FILES['imagine']['error'] == 0) {
                    $imagine = uploadImage($_FILES['imagine']);
                    if (!$imagine) {
                        $message = "Eroare la încărcarea imaginii!";
                        break;
                    }
                }
                
                $sql = "INSERT INTO produse (nume, pret_baza, descriere, ingrediente, delivery_info, imagine) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdssss", $nume, $pret_baza, $descriere, $ingrediente, $delivery_info, $imagine);
                
                if ($stmt->execute()) {
                    $produs_id = $conn->insert_id;
                    
                    // Adaugă dimensiunile
                    if (isset($_POST['dimensiuni']) && is_array($_POST['dimensiuni'])) {
                        foreach ($_POST['dimensiuni'] as $dim) {
                            if (!empty($dim['nume']) && !empty($dim['pret'])) {
                                $sql_dim = "INSERT INTO dimensiuni (produs_id, nume, pret) VALUES (?, ?, ?)";
                                $stmt_dim = $conn->prepare($sql_dim);
                                $stmt_dim->bind_param("isd", $produs_id, $dim['nume'], floatval($dim['pret']));
                                $stmt_dim->execute();
                            }
                        }
                    }
                    
                    $message = "Produsul a fost adăugat cu succes!";
                } else {
                    $message = "Eroare la adăugarea produsului: " . $conn->error;
                }
                break;
                
            case 'delete_product':
                $id = intval($_POST['product_id']);
                
                // Șterge imaginea asociată
                $sql_img = "SELECT imagine FROM produse WHERE id = ?";
                $stmt_img = $conn->prepare($sql_img);
                $stmt_img->bind_param("i", $id);
                $stmt_img->execute();
                $result_img = $stmt_img->get_result();
                if ($row_img = $result_img->fetch_assoc()) {
                    if (!empty($row_img['imagine']) && file_exists($row_img['imagine'])) {
                        unlink($row_img['imagine']);
                    }
                }
                
                $sql = "DELETE FROM produse WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Produsul a fost șters cu succes!";
                } else {
                    $message = "Eroare la ștergerea produsului: " . $conn->error;
                }
                break;
        }
    }
}

// Obține lista produselor
$sql = "SELECT p.*, GROUP_CONCAT(CONCAT(d.nume, ':', d.pret) SEPARATOR '|') as dimensiuni 
        FROM produse p 
        LEFT JOIN dimensiuni d ON p.id = d.produs_id 
        GROUP BY p.id 
        ORDER BY p.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionare Produse - Cafenea Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #8B4513;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #8B4513;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        input:focus, textarea:focus {
            border-color: #8B4513;
            outline: none;
        }
        
        .dimensiuni-container {
            border: 2px dashed #8B4513;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .dimensiune-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .dimensiune-item input {
            flex: 1;
        }
        
        .btn {
            background: linear-gradient(45deg, #8B4513, #D2691E);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .product-card {
            border: 1px solid #ddd;
            border-radius: 15px;
            padding: 20px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .product-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 1.1rem;
            color: #D2691E;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .dimensiuni-list {
            margin-bottom: 15px;
        }
        
        .dimensiune-tag {
            display: inline-block;
            background: #8B4513;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin: 2px;
        }
        
        .nav-links {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .nav-links a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background: #8B4513;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: #D2691E;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>☕ Gestionare Produse Cafenea</h1>
        
        <div class="nav-links">
            <a href="products.php">Vezi Produsele</a>
            <a href="admin_products.php">Gestionare Produse</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Formular pentru adăugare produs -->
        <div class="form-section">
            <h2>Adaugă Produs Nou</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label for="nume">Nume Produs:</label>
                    <input type="text" id="nume" name="nume" required>
                </div>
                
                <div class="form-group">
                    <label for="pret_baza">Preț de Bază (Lei):</label>
                    <input type="number" id="pret_baza" name="pret_baza" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="descriere">Descriere:</label>
                    <textarea id="descriere" name="descriere" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="ingrediente">Ingrediente:</label>
                    <textarea id="ingrediente" name="ingrediente" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="delivery_info">Informații Livrare:</label>
                    <textarea id="delivery_info" name="delivery_info" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagine">Imagine Produs:</label>
                    <input type="file" id="imagine" name="imagine" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Dimensiuni disponibile:</label>
                    <div class="dimensiuni-container" id="dimensiuni-container">
                        <div class="dimensiune-item">
                            <input type="text" name="dimensiuni[0][nume]" placeholder="Nume (ex: S, M, L)" />
                            <input type="number" name="dimensiuni[0][pret]" placeholder="Preț" step="0.01" />
                            <button type="button" class="btn btn-small" onclick="adaugaDimensiune()">+</button>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn">Adaugă Produs</button>
            </form>
        </div>
        
        <!-- Lista produselor existente -->
        <h2>Produse Existente</h2>
        <div class="products-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <?php if (!empty($row['imagine'])): ?>
                        <img src="<?php echo htmlspecialchars($row['imagine']); ?>" alt="<?php echo htmlspecialchars($row['nume']); ?>" class="product-image">
                    <?php endif; ?>
                    
                    <div class="product-title"><?php echo htmlspecialchars($row['nume']); ?></div>
                    <div class="product-price">Preț de bază: <?php echo number_format($row['pret_baza'], 2); ?> Lei</div>
                    
                    <?php if (!empty($row['descriere'])): ?>
                        <div class="product-description"><?php echo htmlspecialchars($row['descriere']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($row['dimensiuni'])): ?>
                        <div class="dimensiuni-list">
                            <strong>Dimensiuni:</strong><br>
                            <?php 
                            $dimensiuni = explode('|', $row['dimensiuni']);
                            foreach ($dimensiuni as $dim) {
                                if (!empty($dim)) {
                                    list($nume, $pret) = explode(':', $dim);
                                    echo "<span class='dimensiune-tag'>$nume: " . number_format($pret, 2) . " Lei</span>";
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Sigur vrei să ștergi acest produs?')">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-small">Șterge</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <script>
        let dimensiuneIndex = 1;
        
        function adaugaDimensiune() {
            const container = document.getElementById('dimensiuni-container');
            const newDiv = document.createElement('div');
            newDiv.className = 'dimensiune-item';
            newDiv.innerHTML = `
                <input type="text" name="dimensiuni[${dimensiuneIndex}][nume]" placeholder="Nume (ex: S, M, L)" />
                <input type="number" name="dimensiuni[${dimensiuneIndex}][pret]" placeholder="Preț" step="0.01" />
                <button type="button" class="btn btn-danger btn-small" onclick="this.parentElement.remove()">-</button>
            `;
            container.appendChild(newDiv);
            dimensiuneIndex++;
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>