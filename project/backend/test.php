<?php
// add_test_products.php - Script pentru adăugarea unor produse de test
require_once 'db_connect.php';

$conn = getDBConnection();

// Produse de test
$produse_test = [
    [
        'nume' => 'Espresso Classic',
        'pret_baza' => 8.50,
        'descriere' => 'Espresso intens și aromat, preparat din boabe de cafea premium. Perfect pentru dimineața sau oricând ai nevoie de energie.',
        'ingrediente' => 'Boabe de cafea 100% arabica, apă filtrată',
        'delivery_info' => 'Disponibil pentru livrare în 15-30 minute în zona centrală',
        'dimensiuni' => [
            ['nume' => 'S', 'pret' => 8.50],
            ['nume' => 'M', 'pret' => 10.50],
            ['nume' => 'L', 'pret' => 12.50]
        ]
    ],
    [
        'nume' => 'Cappuccino Delicios',
        'pret_baza' => 12.00,
        'descriere' => 'Cappuccino cremos cu spumă perfectă de lapte, posibil cu diverse arome și decorațiuni.',
        'ingrediente' => 'Espresso, lapte proaspăt, spumă de lapte, scorțișoară (opțional)',
        'delivery_info' => 'Servit la temperatura optimă, livrare în max 25 minute',
        'dimensiuni' => [
            ['nume' => 'Regular', 'pret' => 12.00],
            ['nume' => 'Large', 'pret' => 15.00]
        ]
    ],
    [
        'nume' => 'Latte Macchiato',
        'pret_baza' => 14.00,
        'descriere' => 'Latte macchiato cu straturi perfecte de lapte, espresso și spumă. O experiență vizuală și gustativă unică.',
        'ingrediente' => 'Espresso dublu, lapte integral, spumă de lapte, sirop de vanilie (opțional)',
        'delivery_info' => 'Livrat în pahare speciale pentru păstrarea straturilor',
        'dimensiuni' => [
            ['nume' => 'Standard', 'pret' => 14.00],
            ['nume' => 'XL', 'pret' => 17.50]
        ]
    ],
    [
        'nume' => 'Americano',
        'pret_baza' => 7.00,
        'descriere' => 'Cafea americană clasică, perfectă pentru cei care preferă gustul intens dar nu atât de concentrat ca espresso.',
        'ingrediente' => 'Espresso dublu, apă caldă filtrată',
        'delivery_info' => 'Ideal pentru birou, livrare rapidă',
        'dimensiuni' => [
            ['nume' => 'Regular', 'pret' => 7.00],
            ['nume' => 'Large', 'pret' => 9.00]
        ]
    ],
    [
        'nume' => 'Mocha Chocolate',
        'pret_baza' => 16.00,
        'descriere' => 'Combinația perfectă între cafea și ciocolată, cu frișcă generoas și pudră de cacao.',
        'ingrediente' => 'Espresso, lapte, ciocolată caldă, frișcă, pudră de cacao',
        'delivery_info' => 'Decorat frumos, ideal pentru momente speciale',
        'dimensiuni' => [
            ['nume' => 'Standard', 'pret' => 16.00],
            ['nume' => 'Premium', 'pret' => 19.50]
        ]
    ],
    [
        'nume' => 'Caramel Frappé',
        'pret_baza' => 18.00,
        'descriere' => 'Băutură rece perfectă pentru zilele călduroase, cu gheață, cafea, lapte și sirop de caramel.',
        'ingrediente' => 'Cafea rece, lapte, gheață, sirop de caramel, frișcă, caramel lichid',
        'delivery_info' => 'Servit rece, perfect pentru vară',
        'dimensiuni' => [
            ['nume' => 'Medium', 'pret' => 18.00],
            ['nume' => 'Large', 'pret' => 21.00]
        ]
    ]
];

echo "<h2>Adăugare produse de test...</h2>";

foreach ($produse_test as $produs) {
    // Inserează produsul
    $sql = "INSERT INTO produse (nume, pret_baza, descriere, ingrediente, delivery_info, imagine) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $imagine = ""; // Fără imagine pentru produsele de test
    $stmt->bind_param("sdssss", 
        $produs['nume'], 
        $produs['pret_baza'], 
        $produs['descriere'], 
        $produs['ingrediente'], 
        $produs['delivery_info'], 
        $imagine
    );
    
    if ($stmt->execute()) {
        $produs_id = $conn->insert_id;
        echo "✅ Produs adăugat: " . $produs['nume'] . " (ID: $produs_id)<br>";
        
        // Adaugă dimensiunile
        foreach ($produs['dimensiuni'] as $dim) {
            $sql_dim = "INSERT INTO dimensiuni (produs_id, nume, pret) VALUES (?, ?, ?)";
            $stmt_dim = $conn->prepare($sql_dim);
            $stmt_dim->bind_param("isd", $produs_id, $dim['nume'], $dim['pret']);
            
            if ($stmt_dim->execute()) {
                echo "&nbsp;&nbsp;→ Dimensiune adăugată: " . $dim['nume'] . " - " . $dim['pret'] . " Lei<br>";
            } else {
                echo "&nbsp;&nbsp;❌ Eroare la adăugarea dimensiunii: " . $conn->error . "<br>";
            }
        }
        
    } else {
        echo "❌ Eroare la adăugarea produsului " . $produs['nume'] . ": " . $conn->error . "<br>";
    }
    
    echo "<br>";
}

echo "<h3>Produsele de test au fost adăugate cu succes!</h3>";
echo "<p><a href='products.php'>Vezi produsele</a> | <a href='admin_products.php'>Administrare produse</a></p>";

$conn->close();
?>