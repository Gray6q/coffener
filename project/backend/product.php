<?php
// products.php - Pagina de afișare produse pentru clienți
require_once 'db_connect.php';
session_start();

$conn = getDBConnection();

// Obține produsele cu dimensiunile lor
$sql = "SELECT p.*, d.id as dim_id, d.nume as dim_nume, d.pret as dim_pret 
        FROM produse p 
        LEFT JOIN dimensiuni d ON p.id = d.produs_id 
        ORDER BY p.id DESC, d.pret ASC";
$result = $conn->query($sql);

// Grupează produsele cu dimensiunile lor
$produse = [];
while ($row = $result->fetch_assoc()) {
    $produs_id = $row['id'];
    if (!isset($produse[$produs_id])) {
        $produse[$produs_id] = [
            'id' => $row['id'],
            'nume' => $row['nume'],
            'pret_baza' => $row['pret_baza'],
            'descriere' => $row['descriere'],
            'ingrediente' => $row['ingrediente'],
            'delivery_info' => $row['delivery_info'],
            'imagine' => $row['imagine'],
            'dimensiuni' => []
        ];
    }
    
    if (!empty($row['dim_id'])) {
        $produse[$produs_id]['dimensiuni'][] = [
            'id' => $row['dim_id'],
            'nume' => $row['dim_nume'],
            'pret' => $row['dim_pret']
        ];
    }
}

// Procesare adăugare în coș (temporar - fără autentificare)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $message = "Produsul a fost adăugat în coș! (Funcție temporară - necesită sistem de autentificare)";
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produse Cafenea - Coffener</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #2C1810, #8B4513);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            background: rgba(139, 69, 19, 0.95);
            padding: 20px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover, .nav-links a.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 20px 40px;
        }
        
        .hero {
            text-align: center;
            margin-bottom: 50px;
            color: white;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        
        .filters {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            font-weight: bold;
            color: #8B4513;
        }
        
        .filter-group input, .filter-group select {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 20px;
            outline: none;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            backdrop-filter: blur(10px);
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .no-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: #666;
            font-size: 3rem;
        }
        
        .product-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 15px;
        }
        
        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .product-ingredients {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .product-ingredients strong {
            color: #8B4513;
        }
        
        .dimensiuni-section {
            margin-bottom: 20px;
        }
        
        .dimensiuni-section h4 {
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .dimensiuni-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .dimensiune-option {
            background: #f8f9fa;
            border: 2px solid #ddd;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            min-width: 80px;
        }
        
        .dimensiune-option:hover, .dimensiune-option.selected {
            background: #8B4513;
            color: white;
            border-color: #8B4513;
        }
        
        .dimensiune-option .nume {
            font-weight: bold;
            display: block;
        }
        
        .dimensiune-option .pret {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .quantity-btn {
            background: #8B4513;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        
        .add-to-cart-btn {
            width: 100%;
            background: linear-gradient(45deg, #8B4513, #D2691E);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .add-to-cart-btn:hover:not(:disabled) {
            transform: translateY(-2px);
        }
        
        .add-to-cart-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .delivery-info {
            background: #e8f5e8;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #2d5a2d;
            margin-top: 15px;
        }
        
        .price-display {
            text-align: center;
            margin-bottom: 15px;
            padding: 15px;
            background: linear-gradient(45deg, #8B4513, #D2691E);
            color: white;
            border-radius: 15px;
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="#" class="logo">☕ Coffener</a>
            <nav class="nav-links">
                <a href="index.php">Acasă</a>
                <a href="products.php" class="active">Produse</a>
                <a href="admin_products.php">Admin</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="hero">
            <h1>☕ Produsele Noastre</h1>
            <p>Descoperiți aromele autentice ale cafelei de calitate</p>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label>Caută:</label>
                <input type="text" id="search" placeholder="Caută produse..." onkeyup="filterProducts()">
            </div>
            <div class="filter-group">
                <label>Sortează după:</label>
                <select id="sort" onchange="sortProducts()">
                    <option value="name">Nume</option>
                    <option value="price_asc">Preț crescător</option>
                    <option value="price_desc">Preț descrescător</option>
                </select>
            </div>
        </div>

        <div class="products-grid" id="products-grid">
            <?php if (empty($produse)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: white;">
                    <h2>Nu există produse disponibile</h2>
                    <p>Reveniți mai târziu sau contactați administratorul pentru a adăuga produse.</p>
                    <a href="admin_products.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #8B4513; color: white; text-decoration: none; border-radius: 20px;">Adaugă Produse (Admin)</a>
                </div>
            <?php else: ?>
                <?php foreach ($produse as $produs): ?>
                    <div class="product-card" data-name="<?php echo strtolower($produs['nume']); ?>" data-price="<?php echo $produs['pret_baza']; ?>">
                        <?php if (!empty($produs['imagine']) && file_exists($produs['imagine'])): ?>
                            <img src="<?php echo htmlspecialchars($produs['imagine']); ?>" alt="<?php echo htmlspecialchars($produs['nume']); ?>" class="product-image">
                        <?php else: ?>
                            <div class="no-image">☕</div>
                        <?php endif; ?>
                        
                        <h3 class="product-title"><?php echo htmlspecialchars($produs['nume']); ?></h3>
                        
                        <?php if (!empty($produs['descriere'])): ?>
                            <p class="product-description"><?php echo htmlspecialchars($produs['descriere']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($produs['ingrediente'])): ?>
                            <div class="product-ingredients">
                                <strong>Ingrediente:</strong> <?php echo htmlspecialchars($produs['ingrediente']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="product-form">
                            <input type="hidden" name="add_to_cart" value="1">
                            <input type="hidden" name="produs_id" value="<?php echo $produs['id']; ?>">
                            
                            <?php if (!empty($produs['dimensiuni'])): ?>
                                <div class="dimensiuni-section">
                                    <h4>Alege dimensiunea:</h4>
                                    <div class="dimensiuni-options">
                                        <?php foreach ($produs['dimensiuni'] as $index => $dimensiune): ?>
                                            <label class="dimensiune-option">
                                                <input type="radio" name="dimensiune_id" value="<?php echo $dimensiune['id']; ?>" 
                                                       data-price="<?php echo $dimensiune['pret']; ?>" 
                                                       <?php echo $index === 0 ? 'checked' : ''; ?> 
                                                       style="display: none;" 
                                                       onchange="updatePrice(this)">
                                                <span class="nume"><?php echo htmlspecialchars($dimensiune['nume']); ?></span>
                                                <span class="pret"><?php echo number_format($dimensiune['pret'], 2); ?> Lei</span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="price-display">
                                    <span class="current-price"><?php echo number_format($produs['dimensiuni'][0]['pret'], 2); ?> Lei</span>
                                </div>
                            <?php else: ?>
                                <div class="price-display">
                                    <span class="current-price"><?php echo number_format($produs['pret_baza'], 2); ?> Lei</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>
                                <input type="number" name="cantitate" value="1" min="1" max="10" class="quantity-input" readonly>
                                <button type="button" class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                            </div>
                            
                            <button type="submit" class="add-to-cart-btn">
                                🛒 Adaugă în coș
                            </button>
                        </form>
                        
                        <?php if (!empty($produs['delivery_info'])): ?>
                            <div class="delivery-info">
                                <strong>ℹ️ Info livrare:</strong> <?php echo htmlspecialchars($produs['delivery_info']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Funcție pentru actualizarea prețului când se schimbă dimensiunea
        function updatePrice(radio) {
            const price = parseFloat(radio.dataset.price);
            const priceDisplay = radio.closest('.product-card').querySelector('.current-price');
            priceDisplay.textContent = price.toFixed(2) + ' Lei';
            
            // Actualizează stilul opțiunilor
            const options = radio.closest('.dimensiuni-options').querySelectorAll('.dimensiune-option');
            options.forEach(option => option.classList.remove('selected'));
            radio.closest('.dimensiune-option').classList.add('selected');
        }
        
        // Funcție pentru schimbarea cantității
        function changeQuantity(button, change) {
            const input = button.parentElement.querySelector('.quantity-input');
            let newValue = parseInt(input.value) + change;
            
            if (newValue >= 1 && newValue <= 10) {
                input.value = newValue;
            }
        }
        
        // Funcție pentru filtrarea produselor
        function filterProducts() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const productName = card.dataset.name;
                if (productName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Funcție pentru sortarea produselor
        function sortProducts() {
            const sortType = document.getElementById('sort').value;
            const grid = document.getElementById('products-grid');
            const cards = Array.from(grid.querySelectorAll('.product-card'));
            
            cards.sort((a, b) => {
                switch(sortType) {
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    case 'price_asc':
                        return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    case 'price_desc':
                        return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    default:
                        return 0;
                }
            });
            
            // Reordonează cardurile în DOM
            cards.forEach(card => grid.appendChild(card));
        }
        
        // Inițializare: selectează prima dimensiune disponibilă pentru fiecare produs
        document.addEventListener('DOMContentLoaded', function() {
            const firstOptions = document.querySelectorAll('.dimensiune-option:first-child input[type="radio"]');
            firstOptions.forEach(radio => {
                radio.closest('.dimensiune-option').classList.add('selected');
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>