<?php
require_once '../backend/db_connect.php';

// 1. Ștergere produs dacă există parametru delete_id
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM produse WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect după ștergere ca să evităm re-trimiterea formului la refresh
    header("Location: products.php?msg=Produs șters cu succes");
    exit;
}

// 2. Preluare produse pentru listare
$sql = "SELECT id, nume, pret_baza FROM produse ORDER BY nume ASC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8" />
    <title>Lista produse - Cofetărie Online</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #673ab7; color: white; }
        tr:hover { background: #f0e6ff; }
        a.button-edit, a.button-delete {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            color: white;
        }
        a.button-edit { background: #673ab7; }
        a.button-edit:hover { background: #512da8; }
        a.button-delete { background: #e53935; margin-left: 6px; }
        a.button-delete:hover { background: #b71c1c; }
        .msg { margin-bottom: 20px; color: green; }
    </style>
</head>
<body>

<h1>Lista produse</h1>

<?php if (isset($_GET['msg'])): ?>
    <p class="msg"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nume produs</th>
            <th>Preț bază (lei)</th>
            <th>Acțiuni</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while($produs = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $produs['id'] ?></td>
            <td>
                <a href="product_edit.php?id=<?= $produs['id'] ?>">
                    <?= htmlspecialchars($produs['nume']) ?>
                </a>
            </td>
            <td><?= number_format($produs['pret_baza'], 2, ',', ' ') ?></td>
            <td>
                <a href="product_edit.php?id=<?= $produs['id'] ?>" class="button-edit">Editează</a>
                <a href="products.php?delete_id=<?= $produs['id'] ?>" class="button-delete" onclick="return confirm('Sigur vrei să ștergi acest produs?');">Șterge</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">Nu există produse în baza de date.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>

<?php
$conn->close();
?>
