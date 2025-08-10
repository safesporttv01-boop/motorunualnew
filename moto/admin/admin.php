<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!girisKontrol() || !adminKontrol()) {
    header("Location: ../index.php");
    exit;
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// İstatistikler
$stats = [
    'toplam_kullanici' => $pdo->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn(),
    'toplam_ilan' => $pdo->query("SELECT COUNT(*) FROM ilanlar")->fetchColumn(),
    'onay_bekleyen' => $pdo->query("SELECT COUNT(*) FROM ilanlar WHERE onaylanmis = FALSE")->fetchColumn(),
    'aktif_ilan' => $pdo->query("SELECT COUNT(*) FROM ilanlar WHERE aktif = TRUE AND onaylanmis = TRUE")->fetchColumn(),
    'bugun_kayit' => $pdo->query("SELECT COUNT(*) FROM kullanicilar WHERE DATE(kayit_tarihi) = CURDATE()")->fetchColumn(),
    'bugun_ilan' => $pdo->query("SELECT COUNT(*) FROM ilanlar WHERE DATE(ilan_tarihi) = CURDATE()")->fetchColumn()
];

// İlan onaylama işlemi
if (isset($_POST['onayla_ilan'])) {
    $ilan_id = $_POST['ilan_id'];
    $stmt = $pdo->prepare("UPDATE ilanlar SET onaylanmis = TRUE WHERE id = ?");
    if ($stmt->execute([$ilan_id])) {
        $success = "İlan başarıyla onaylandı!";
    } else {
        $error = "İlan onaylanırken hata oluştu!";
    }
}

// İlan reddetme işlemi
if (isset($_POST['reddet_ilan'])) {
    $ilan_id = $_POST['ilan_id'];
    $stmt = $pdo->prepare("UPDATE ilanlar SET aktif = FALSE WHERE id = ?");
    if ($stmt->execute([$ilan_id])) {
        $success = "İlan başarıyla reddedildi!";
    } else {
        $error = "İlan reddedilirken hata oluştu!";
    }
}

// Kullanıcı silme işlemi
if (isset($_POST['sil_kullanici'])) {
    $kullanici_id = $_POST['kullanici_id'];
    $stmt = $pdo->prepare("UPDATE kullanicilar SET aktif = FALSE WHERE id = ?");
    if ($stmt->execute([$kullanici_id])) {
        $success = "Kullanıcı başarıyla deaktif edildi!";
    } else {
        $error = "Kullanıcı deaktif edilirken hata oluştu!";
    }
}

// Marka ekleme işlemi
if (isset($_POST['ekle_marka'])) {
    $marka_adi = trim($_POST['marka_adi']);
    if (!empty($marka_adi)) {
        $stmt = $pdo->prepare("INSERT INTO markalar (marka_adi) VALUES (?)");
        if ($stmt->execute([$marka_adi])) {
            $success = "Marka başarıyla eklendi!";
        } else {
            $error = "Marka eklenirken hata oluştu!";
        }
    } else {
        $error = "Marka adı boş bırakılamaz!";
    }
}

// Marka silme işlemi
if (isset($_POST['sil_marka'])) {
    $marka_id = $_POST['marka_id'];
    $stmt = $pdo->prepare("UPDATE markalar SET aktif = FALSE WHERE id = ?");
    if ($stmt->execute([$marka_id])) {
        $success = "Marka başarıyla deaktif edildi!";
    } else {
        $error = "Marka deaktif edilirken hata oluştu!";
    }
}

// Model ekleme işlemi
if (isset($_POST['ekle_model'])) {
    $model_adi = trim($_POST['model_adi']);
    $marka_id = $_POST['model_marka_id'];
    if (!empty($model_adi) && !empty($marka_id)) {
        $stmt = $pdo->prepare("INSERT INTO modeller (marka_id, model_adi) VALUES (?, ?)");
        if ($stmt->execute([$marka_id, $model_adi])) {
            $success = "Model başarıyla eklendi!";
        } else {
            $error = "Model eklenirken hata oluştu!";
        }
    } else {
        $error = "Model adı ve marka seçimi zorunludur!";
    }
}

// Model silme işlemi
if (isset($_POST['sil_model'])) {
    $model_id = $_POST['model_id'];
    $stmt = $pdo->prepare("UPDATE modeller SET aktif = FALSE WHERE id = ?");
    if ($stmt->execute([$model_id])) {
        $success = "Model başarıyla deaktif edildi!";
    } else {
        $error = "Model deaktif edilirken hata oluştu!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotorUnal - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #52c41a;
            --warning-color: #faad14;
            --danger-color: #ff4d4f;
            --info-color: #1890ff;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --sidebar-width: 280px;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .sidebar-header p {
            margin: 5px 0 0 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 15px;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none !important;
            cursor: pointer;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white !important;
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 700;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .stats-card.success {
            border-left-color: var(--success-color);
        }

        .stats-card.warning {
            border-left-color: var(--warning-color);
        }

        .stats-card.danger {
            border-left-color: var(--danger-color);
        }

        .stats-card.info {
            border-left-color: var(--info-color);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stats-text {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }

        .stats-icon {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 15px;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .content-card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .content-card-body {
            padding: 25px;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: var(--light-color);
            border: none;
            font-weight: 600;
            color: var(--dark-color);
            padding: 15px;
        }

        .table td {
            border-color: #f0f0f0;
            vertical-align: middle;
            padding: 15px;
        }

        .badge {
            font-size: 0.85rem;
            padding: 8px 12px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 16px;
        }

        .alert {
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .quick-actions {
            display: grid;
            gap: 15px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar-toggle {
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: var(--primary-color);
                color: white;
                border: none;
                padding: 10px;
                border-radius: 5px;
            }
        }

        /* Chat Specific Styles */
        .chat-room-item {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-room-item:hover {
            background-color: var(--light-color) !important;
        }

        .hover-bg-light:hover {
            background-color: var(--light-color) !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        #messages-container {
            background: #f8f9fa;
        }

        .message-bubble {
            max-width: 80%;
            word-wrap: break-word;
        }

        .admin-message {
            text-align: right;
        }

        .admin-message .message-bubble {
            background: var(--primary-color);
            color: white;
            margin-left: auto;
        }

        .user-message {
            text-align: left;
        }

        .user-message .message-bubble {
            background: white;
            border: 1px solid #dee2e6;
        }

        .message-time {
            font-size: 0.8rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-cogs me-2"></i>Admin Panel</h4>
            <p>Hoşgeldin, <?php echo $_SESSION['kullanici_ad']; ?></p>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <div class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" 
                   onclick="showContent('dashboard')">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </div>
            </div>
            <div class="nav-item">
                <div class="nav-link <?php echo $current_page == 'ilanlar' ? 'active' : ''; ?>" 
                   onclick="showContent('ilanlar')">
                    <i class="fas fa-list"></i>İlan Yönetimi
                </div>
            </div>
            <div class="nav-item">
                <div class="nav-link <?php echo $current_page == 'kullanicilar' ? 'active' : ''; ?>" 
                   onclick="showContent('kullanicilar')">
                    <i class="fas fa-users"></i>Kullanıcı Yönetimi
                </div>
            </div>
            <div class="nav-item">
                <div class="nav-link <?php echo $current_page == 'markalar' ? 'active' : ''; ?>" 
                   onclick="showContent('markalar')">
                    <i class="fas fa-tags"></i>Marka/Model Yönetimi
                </div>
            </div>
            <div class="nav-item">
                <div class="nav-link <?php echo $current_page == 'chat' ? 'active' : ''; ?>" 
                   onclick="showContent('chat')">
                    <i class="fas fa-comments"></i>Canlı Destek
                    <span id="unread-messages-badge" class="badge bg-danger ms-2" style="display: none;">0</span>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 15px;">
            <div class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home"></i>Ana Siteye Dön
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>Çıkış Yap
                </a>
            </div>
        </nav>
    </div>

    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Content -->
        <div id="dashboard-content" class="content-section <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
            <div class="page-header">
                <h1><i class="fas fa-tachometer-alt me-3"></i>Dashboard</h1>
                <p class="mb-0 text-muted">Sistem genel durumu ve istatistikleri</p>
            </div>

            <!-- İstatistik Kartları -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-icon text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-number text-primary"><?php echo number_format($stats['toplam_kullanici']); ?></div>
                        <div class="stats-text">Toplam Kullanıcı</div>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> +<?php echo $stats['bugun_kayit']; ?> bugün
                        </small>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card success">
                        <div class="stats-icon text-success">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stats-number text-success"><?php echo number_format($stats['toplam_ilan']); ?></div>
                        <div class="stats-text">Toplam İlan</div>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> +<?php echo $stats['bugun_ilan']; ?> bugün
                        </small>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card warning">
                        <div class="stats-icon text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-number text-warning"><?php echo number_format($stats['onay_bekleyen']); ?></div>
                        <div class="stats-text">Onay Bekleyen</div>
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i> Beklemede
                        </small>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card info">
                        <div class="stats-icon text-info">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-number text-info"><?php echo number_format($stats['aktif_ilan']); ?></div>
                        <div class="stats-text">Aktif İlan</div>
                        <small class="text-info">
                            <i class="fas fa-eye"></i> Yayında
                        </small>
                    </div>
                </div>
            </div>

            <!-- Son Aktiviteler -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="content-card">
                        <div class="content-card-header">
                            <i class="fas fa-clock me-2"></i>Son Eklenen İlanlar
                        </div>
                        <div class="content-card-body">
                            <?php
                            $son_ilanlar = $pdo->query("
                                SELECT i.*, k.ad, k.soyad, m.marka_adi, mo.model_adi 
                                FROM ilanlar i
                                LEFT JOIN kullanicilar k ON i.kullanici_id = k.id
                                LEFT JOIN markalar m ON i.marka_id = m.id
                                LEFT JOIN modeller mo ON i.model_id = mo.id
                                ORDER BY i.ilan_tarihi DESC 
                                LIMIT 5
                            ")->fetchAll();
                            ?>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>İlan</th>
                                            <th>Kullanıcı</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($son_ilanlar as $ilan): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo substr($ilan['ilan_ismi'], 0, 30) . (strlen($ilan['ilan_ismi']) > 30 ? '...' : ''); ?></strong><br>
                                                    <small class="text-muted"><?php echo $ilan['marka_adi'] . ' ' . $ilan['model_adi']; ?></small>
                                                </td>
                                                <td><?php echo $ilan['ad'] . ' ' . $ilan['soyad']; ?></td>
                                                <td>
                                                    <?php if ($ilan['onaylanmis']): ?>
                                                        <span class="badge bg-success">Onaylı</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Bekliyor</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!$ilan['onaylanmis']): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="ilan_id" value="<?php echo $ilan['id']; ?>">
                                                            <button type="submit" name="onayla_ilan" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <a href="../index.php?page=ilan_detay&id=<?php echo $ilan['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="content-card">
                        <div class="content-card-header">
                            <i class="fas fa-user-plus me-2"></i>Son Kayıt Olan Kullanıcılar
                        </div>
                        <div class="content-card-body">
                            <?php
                            $son_kullanicilar = $pdo->query("
                                SELECT * FROM kullanicilar 
                                WHERE aktif = TRUE 
                                ORDER BY kayit_tarihi DESC 
                                LIMIT 5
                            ")->fetchAll();
                            ?>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kullanıcı</th>
                                            <th>Email</th>
                                            <th>Kayıt Tarihi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($son_kullanicilar as $kullanici): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo $kullanici['ad'] . ' ' . $kullanici['soyad']; ?></strong>
                                                    <?php if ($kullanici['is_admin']): ?>
                                                        <span class="badge bg-danger ms-1">Admin</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $kullanici['email']; ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($kullanici['kayit_tarihi'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- İlanlar Content -->
        <div id="ilanlar-content" class="content-section <?php echo $current_page == 'ilanlar' ? 'active' : ''; ?>">
            <div class="page-header">
                <h1><i class="fas fa-list me-3"></i>İlan Yönetimi</h1>
                <p class="mb-0 text-muted">Tüm ilanları görüntüle ve yönet</p>
            </div>

            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-list me-2"></i>İlan Listesi
                </div>
                <div class="content-card-body">
                    <?php
                    $ilanlar = $pdo->query("
                        SELECT i.*, k.ad, k.soyad, m.marka_adi, mo.model_adi 
                        FROM ilanlar i
                        LEFT JOIN kullanicilar k ON i.kullanici_id = k.id
                        LEFT JOIN markalar m ON i.marka_id = m.id
                        LEFT JOIN modeller mo ON i.model_id = mo.id
                        ORDER BY i.ilan_tarihi DESC
                    ")->fetchAll();
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>İlan</th>
                                    <th>Kullanıcı</th>
                                    <th>Fiyat</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ilanlar as $ilan): ?>
                                    <tr>
                                        <td><?php echo $ilan['id']; ?></td>
                                        <td>
                                            <strong><?php echo substr($ilan['ilan_ismi'], 0, 30) . (strlen($ilan['ilan_ismi']) > 30 ? '...' : ''); ?></strong><br>
                                            <small class="text-muted"><?php echo $ilan['marka_adi'] . ' ' . $ilan['model_adi']; ?></small>
                                        </td>
                                        <td><?php echo $ilan['ad'] . ' ' . $ilan['soyad']; ?></td>
                                        <td><?php echo number_format($ilan['fiyat'], 0, ',', '.'); ?> ₺</td>
                                        <td><?php echo date('d.m.Y', strtotime($ilan['ilan_tarihi'])); ?></td>
                                        <td>
                                            <?php if ($ilan['onaylanmis'] && $ilan['aktif']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php elseif (!$ilan['onaylanmis']): ?>
                                                <span class="badge bg-warning">Onay Bekliyor</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if (!$ilan['onaylanmis']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="ilan_id" value="<?php echo $ilan['id']; ?>">
                                                        <button type="submit" name="onayla_ilan" class="btn btn-sm btn-success" title="Onayla">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="ilan_id" value="<?php echo $ilan['id']; ?>">
                                                    <button type="submit" name="reddet_ilan" class="btn btn-sm btn-danger" title="Reddet" 
                                                            onclick="return confirm('Bu ilanı reddetmek istediğinize emin misiniz?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                                <a href="../index.php?page=ilan_detay&id=<?php echo $ilan['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank" title="Görüntüle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kullanıcılar Content -->
        <div id="kullanicilar-content" class="content-section <?php echo $current_page == 'kullanicilar' ? 'active' : ''; ?>">
            <div class="page-header">
                <h1><i class="fas fa-users me-3"></i>Kullanıcı Yönetimi</h1>
                <p class="mb-0 text-muted">Tüm kullanıcıları görüntüle ve yönet</p>
            </div>

            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-list me-2"></i>Kullanıcı Listesi
                </div>
                <div class="content-card-body">
                    <?php
                    $kullanicilar = $pdo->query("
                        SELECT * FROM kullanicilar 
                        ORDER BY kayit_tarihi DESC
                    ")->fetchAll();
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kullanıcı</th>
                                    <th>Email</th>
                                    <th>Telefon</th>
                                    <th>İlan Hakkı</th>
                                    <th>Rol</th>
                                    <th>Durum</th>
                                    <th>Kayıt Tarihi</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kullanicilar as $kullanici): ?>
                                    <tr>
                                        <td><?php echo $kullanici['id']; ?></td>
                                        <td>
                                            <strong><?php echo $kullanici['ad'] . ' ' . $kullanici['soyad']; ?></strong>
                                        </td>
                                        <td><?php echo $kullanici['email']; ?></td>
                                        <td><?php echo $kullanici['telefon'] ?: '-'; ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $kullanici['ilan_hakki']; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($kullanici['is_admin']): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Kullanıcı</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($kullanici['aktif']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($kullanici['kayit_tarihi'])); ?></td>
                                        <td>
                                            <?php if ($kullanici['aktif'] && $kullanici['id'] != $_SESSION['kullanici_id']): ?>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bu kullanıcıyı deaktif etmek istediğinize emin misiniz?')">
                                                    <input type="hidden" name="kullanici_id" value="<?php echo $kullanici['id']; ?>">
                                                    <button type="submit" name="sil_kullanici" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Management Content -->
        <div id="chat-content" class="content-section <?php echo $current_page == 'chat' ? 'active' : ''; ?>">
            <div class="page-header">
                <h1><i class="fas fa-comments me-3"></i>Canlı Destek</h1>
                <p class="mb-0 text-muted">Kullanıcılarla anlık mesajlaşma</p>
            </div>

            <div class="row h-100">
                <!-- Chat Rooms List -->
                <div class="col-md-4">
                    <div class="content-card">
                        <div class="content-card-header">
                            <i class="fas fa-users me-2"></i>Aktif Sohbetler
                        </div>
                        <div class="content-card-body p-0">
                            <div id="chat-rooms-list">
                                <div class="text-center p-4 text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Sohbetler yükleniyor...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Window -->
                <div class="col-md-8">
                    <div class="content-card" style="height: 600px;">
                        <div class="content-card-header" id="chat-header">
                            <i class="fas fa-comment me-2"></i>Sohbet seçin
                        </div>
                        <div class="content-card-body d-flex flex-column p-0" style="height: 100%;">
                            <!-- Messages Area -->
                            <div id="messages-container" class="flex-grow-1 p-3 overflow-auto bg-light" style="max-height: 450px;">
                                <div class="text-center text-muted" id="no-chat-selected">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>Başlamak için bir sohbet seçin</p>
                                </div>
                            </div>

                            <!-- Message Input -->
                            <div class="border-top p-3" id="message-input-area" style="display: none;">
                                <div class="input-group">
                                    <input type="text" id="message-input" class="form-control" placeholder="Mesajınızı yazın..." disabled>
                                    <button class="btn btn-primary" type="button" id="send-message-btn" disabled>
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marka/Model Yönetimi Content -->
        <div id="markalar-content" class="content-section <?php echo $current_page == 'markalar' ? 'active' : ''; ?>">
            <div class="page-header">
                <h1><i class="fas fa-tags me-3"></i>Marka/Model Yönetimi</h1>
                <p class="mb-0 text-muted">Motor markalarını ve modellerini yönet</p>
            </div>

            <div class="row">
                <!-- Marka Ekleme -->
                <div class="col-lg-6 mb-4">
                    <div class="content-card">
                        <div class="content-card-header">
                            <i class="fas fa-plus me-2"></i>Yeni Marka Ekle
                        </div>
                        <div class="content-card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="marka_adi" class="form-label">Marka Adı</label>
                                    <input type="text" class="form-control" id="marka_adi" name="marka_adi" 
                                           placeholder="Örn: Honda, Yamaha" required>
                                </div>
                                <button type="submit" name="ekle_marka" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Marka Ekle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Model Ekleme -->
                <div class="col-lg-6 mb-4">
                    <div class="content-card">
                        <div class="content-card-header">
                            <i class="fas fa-plus me-2"></i>Yeni Model Ekle
                        </div>
                        <div class="content-card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="model_marka_id" class="form-label">Marka</label>
                                    <select class="form-select" id="model_marka_id" name="model_marka_id" required>
                                        <option value="">Marka Seçin</option>
                                        <?php
                                        $markalar = $pdo->query("SELECT * FROM markalar WHERE aktif = TRUE ORDER BY marka_adi")->fetchAll();
                                        foreach ($markalar as $marka):
                                        ?>
                                            <option value="<?php echo $marka['id']; ?>"><?php echo $marka['marka_adi']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="model_adi" class="form-label">Model Adı</label>
                                    <input type="text" class="form-control" id="model_adi" name="model_adi" 
                                           placeholder="Örn: CBR600RR, MT-07" required>
                                </div>
                                <button type="submit" name="ekle_model" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Model Ekle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marka Listesi -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <i class="fas fa-list me-2"></i>Marka Listesi
                </div>
                <div class="content-card-body">
                    <?php
                    $markalar_list = $pdo->query("
                        SELECT m.*, COUNT(mo.id) as model_sayisi 
                        FROM markalar m 
                        LEFT JOIN modeller mo ON m.id = mo.marka_id AND mo.aktif = TRUE
                        WHERE m.aktif = TRUE 
                        GROUP BY m.id 
                        ORDER BY m.marka_adi
                    ")->fetchAll();
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Marka Adı</th>
                                    <th>Model Sayısı</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($markalar_list as $marka): ?>
                                    <tr>
                                        <td><?php echo $marka['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($marka['marka_adi']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $marka['model_sayisi']; ?> model</span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bu markayı deaktif etmek istediğinize emin misiniz?')">
                                                <input type="hidden" name="marka_id" value="<?php echo $marka['id']; ?>">
                                                <button type="submit" name="sil_marka" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Model Listesi -->
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-list me-2"></i>Model Listesi
                </div>
                <div class="content-card-body">
                    <?php
                    $modeller_list = $pdo->query("
                        SELECT mo.*, m.marka_adi 
                        FROM modeller mo 
                        LEFT JOIN markalar m ON mo.marka_id = m.id 
                        WHERE mo.aktif = TRUE 
                        ORDER BY m.marka_adi, mo.model_adi
                    ")->fetchAll();
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Marka</th>
                                    <th>Model</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modeller_list as $model): ?>
                                    <tr>
                                        <td><?php echo $model['id']; ?></td>
                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($model['marka_adi']); ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($model['model_adi']); ?></strong></td>
                                        <td>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bu modeli deaktif etmek istediğinize emin misiniz?')">
                                                <input type="hidden" name="model_id" value="<?php echo $model['id']; ?>">
                                                <button type="submit" name="sil_model" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chat WebSocket bağlantısı
        let chatWebSocket = null;
        let currentRoomId = null;
        let adminInfo = {
            kullanici_id: <?php echo $_SESSION['kullanici_id']; ?>,
            kullanici_ad: '<?php echo $_SESSION['kullanici_ad']; ?>'
        };

        function initChatSystem() {
            try {
                chatWebSocket = new WebSocket('ws://localhost:8765');

                chatWebSocket.onopen = function() {
                    // Admin olarak bağlan
                    chatWebSocket.send(JSON.stringify({
                        type: 'admin',
                        kullanici_id: adminInfo.kullanici_id,
                        kullanici_ad: adminInfo.kullanici_ad,
                        kullanici_email: ''
                    }));
                };

                chatWebSocket.onmessage = function(event) {
                    const data = JSON.parse(event.data);
                    handleChatMessage(data);
                };

                chatWebSocket.onclose = function() {
                    setTimeout(initChatSystem, 3000); // Yeniden bağlan
                };

                chatWebSocket.onerror = function(error) {
                    console.error('WebSocket Error:', error);
                };
            } catch (error) {
                console.error('Chat connection error:', error);
                setTimeout(initChatSystem, 5000);
            }
        }

        function handleChatMessage(data) {
            switch(data.type) {
                case 'rooms_list':
                    updateRoomsList(data.rooms);
                    break;
                case 'rooms_list_update':
                    updateRoomsList(data.rooms);
                    updateUnreadBadge(data.rooms);
                    break;
                case 'new_message':
                    // Yeni kullanıcı mesajı
                    if (currentRoomId == data.room_id) {
                        addMessageToChat('user', data.kullanici_ad, data.message, data.timestamp);
                    }
                    // Bildirim sayısını güncelle
                    updateUnreadCount(1);
                    break;
                case 'room_messages':
                    displayRoomMessages(data.messages);
                    break;
                case 'admin_message_update':
                    if (currentRoomId == data.room_id) {
                        addMessageToChat('admin', data.sender_name, data.message, data.timestamp);
                    }
                    break;
            }
        }

        function updateRoomsList(rooms = null) {
            if (rooms) {
                const roomsList = document.getElementById('chat-rooms-list');
                if (rooms.length === 0) {
                    roomsList.innerHTML = '<div class="text-center p-4 text-muted">Henüz aktif sohbet yok</div>';
                    return;
                }

                let roomsHtml = '';
                rooms.forEach(room => {
                    const lastMessageTime = room.last_message_time ? 
                        new Date(room.last_message_time).toLocaleString('tr-TR', {
                            hour: '2-digit',
                            minute: '2-digit',
                            day: '2-digit',
                            month: '2-digit',
                            year: '2-digit'
                        }) : 'Yeni sohbet';

                    roomsHtml += `
                        <div class="chat-room-item p-3 border-bottom cursor-pointer ${currentRoomId == room.room_id ? 'bg-primary text-white' : 'hover-bg-light'}" 
                             onclick="selectChatRoom(${room.room_id}, '${room.kullanici_ad}', '${room.kullanici_email}')">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">${room.kullanici_ad}</h6>
                                    <small class="text-muted">${room.kullanici_email}</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">${lastMessageTime}</small>
                                    ${room.message_count > 0 ? `<div><span class="badge bg-info">${room.message_count}</span></div>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });

                roomsList.innerHTML = roomsHtml;
            }
        }

        function selectChatRoom(roomId, userName, userEmail) {
            currentRoomId = roomId;

            // UI güncelle
            document.getElementById('chat-header').innerHTML = `
                <i class="fas fa-user me-2"></i>${userName} <small class="text-muted">(${userEmail})</small>
            `;

            document.getElementById('no-chat-selected').style.display = 'none';
            document.getElementById('message-input-area').style.display = 'block';
            document.getElementById('message-input').disabled = false;
            document.getElementById('send-message-btn').disabled = false;

            // Aktif odayı işaretle
            document.querySelectorAll('.chat-room-item').forEach(item => {
                item.classList.remove('bg-primary', 'text-white');
                item.classList.add('hover-bg-light');
            });

            event.target.closest('.chat-room-item').classList.add('bg-primary', 'text-white');
            event.target.closest('.chat-room-item').classList.remove('hover-bg-light');

            // Bu oda için bildirim sayısını sıfırla
            unreadMessageCount = Math.max(0, unreadMessageCount - 1);
            updateUnreadCount(0);

            // Mesajları getir
            if (chatWebSocket && chatWebSocket.readyState === WebSocket.OPEN) {
                chatWebSocket.send(JSON.stringify({
                    type: 'get_room_messages',
                    room_id: roomId
                }));
            }
        }

        function displayRoomMessages(messages) {
            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.innerHTML = '';

            messages.forEach(message => {
                addMessageToChat(message.sender_type, message.sender_name, message.message, message.timestamp);
            });

            scrollToBottom();
        }

        function addMessageToChat(senderType, senderName, message, timestamp) {
            const messagesContainer = document.getElementById('messages-container');
            const messageDiv = document.createElement('div');

            const time = new Date(timestamp).toLocaleTimeString('tr-TR', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const isAdmin = senderType === 'admin';
            const alignClass = isAdmin ? 'text-end' : 'text-start';
            const bgClass = isAdmin ? 'bg-primary text-white' : 'bg-white';

            messageDiv.className = `mb-3 ${alignClass}`;
            messageDiv.innerHTML = `
                <div class="d-inline-block p-3 rounded ${bgClass} shadow-sm" style="max-width: 70%;">
                    <div class="fw-bold mb-1">${senderName}</div>
                    <div>${message}</div>
                    <small class="opacity-75">${time}</small>
                </div>
            `;

            messagesContainer.appendChild(messageDiv);
            scrollToBottom();
        }

        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();

            if (message && currentRoomId && chatWebSocket && chatWebSocket.readyState === WebSocket.OPEN) {
                chatWebSocket.send(JSON.stringify({
                    type: 'chat_message',
                    room_id: currentRoomId,
                    message: message
                }));

                // Mesajı UI'a ekle
                addMessageToChat('admin', 'Ben', message, new Date().toISOString());
                messageInput.value = '';
            }
        }

        function scrollToBottom() {
            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        let unreadMessageCount = 0;

        function updateUnreadCount(increment = 0) {
            if (increment > 0) {
                unreadMessageCount += increment;
            }
            
            const badge = document.getElementById('unread-messages-badge');
            if (unreadMessageCount > 0) {
                badge.textContent = unreadMessageCount;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }

        function updateUnreadBadge(rooms) {
            let totalUnread = 0;
            rooms.forEach(room => {
                if (room.message_count > 0) {
                    totalUnread += parseInt(room.message_count);
                }
            });
            
            const badge = document.getElementById('unread-messages-badge');
            if (totalUnread > 0) {
                badge.textContent = totalUnread;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }

        function refreshRoomsList() {
             if (chatWebSocket && chatWebSocket.readyState === WebSocket.OPEN) {
                chatWebSocket.send(JSON.stringify({
                    type: 'get_rooms'
                }));
            }
        }

        // Event listeners
        document.getElementById('send-message-btn').addEventListener('click', sendMessage);
        document.getElementById('message-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function showContent(page) {
            // Tüm content section'ları gizle
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Tüm nav-link'lerin active class'ını kaldır
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Seçilen content'i göster
            document.getElementById(page + '-content').classList.add('active');

            // Seçilen nav-link'e active class ekle
            event.target.classList.add('active');

            // URL'yi güncelle
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.history.pushState({}, '', url);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Sayfa yüklendiğinde aktif sayfayı göster
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 'dashboard';

            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            document.getElementById(page + '-content').classList.add('active');

            // Chat sistemini başlat
            initChatSystem();
        });
    </script>
</body>
</html>