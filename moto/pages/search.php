<?php
require_once __DIR__ . '/../includes/functions.php';

if (!girisKontrol()) {
    header("Location: index.php?page=login");
    exit;
}

$users = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_query = temizle($_POST['search_query']);
    if (empty($search_query)) {
        $error = 'Arama alanı boş bırakılamaz.';
    } else {
        // Search for users in the database
        $users = searchUsers($search_query); // Implement this function in your functions.php
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Arama</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Link to your CSS -->
</head>
<body>

<div class="container my-5">
    <h2>Kullanıcı Ara</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="input-group">
            <input type="text" name="search_query" class="form-control" placeholder="Kullanıcı adı veya e-posta">
            <button type="submit" class="btn btn-primary" name="search">Ara</button>
        </div>
    </form>

    <?php if (!empty($users)): ?>
        <ul class="list-group">
            <?php foreach ($users as $user): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo $user['ad'] . ' ' . $user['soyad']; ?> (<?php echo $user['email']; ?>)
                    <a href="index.php?page=ilanlar&user_id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">İlanlarına Bak</a>
                    <a href="send_message.php?to=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm">Mesaj Gönder</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>