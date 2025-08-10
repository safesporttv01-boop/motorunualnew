
<?php
require_once '../config/database.php';

if (isset($_GET['marka_id'])) {
    $marka_id = (int)$_GET['marka_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM modeller WHERE marka_id = ? AND aktif = TRUE ORDER BY model_adi");
    $stmt->execute([$marka_id]);
    $modeller = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($modeller);
}
?>
