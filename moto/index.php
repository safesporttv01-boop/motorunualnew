<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$current_page = $_GET['page'] ?? 'home';


$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotorUnal - Türkiye'nin Motor İlan Sitesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <style>

        /* Temel Reset ve Font */
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

a {
    text-decoration: none;
    color: inherit;
}

.sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100%;
    background-color: #fff;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    transition: left 0.3s ease;
    z-index: 1000;
}

.sidebar.open {
    left: 0;
}

/* Overlay */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 999;
}

.sidebar.open ~ .sidebar-overlay {
    display: block;
}

/* Toggle Button */
.sidebar-toggle {
    position: fixed;
    top: 15px;
    left: 15px;
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 12px;
    border-radius: 4px;
    cursor: pointer;
    z-index: 1100;
}

.sidebar-toggle i {
    font-size: 18px;
}

/* Sidebar Header */
.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background-color: #007bff;
    color: white;
}

.sidebar-header h5 {
    margin: 0;
    font-size: 18px;
}

.btn-close-sidebar {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

/* Sidebar Content */
.sidebar-content {
    padding: 10px 15px;
}

.category-item {
    margin-bottom: 10px;
}

.category-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f2f2f2;
    padding: 10px 12px;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s;
}

.category-link:hover {
    background-color: #e0e0e0;
}

.models-list {
    display: none;
    flex-direction: column;
    padding-left: 15px;
    margin-top: 5px;
}

.models-list a {
    display: block;
    padding: 6px 0;
    color: #333;
    font-size: 14px;
    transition: color 0.2s;
}

.models-list a:hover {
    color: #007bff;
}

/* Responsive */
@media screen and (max-width: 768px) {
    .sidebar {
        width: 100%;
    }
}

    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="fas fa-motorcycle me-2"></i>MotorUnal
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=ilanlar">İlanlar</a>
                    </li>
                    <?php  if (girisKontrol()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=profil">Profilim</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=ilan_ekle">İlan Ver</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=shop">
                                <i class="fas fa-shopping-cart me-1"></i>Mağaza
                            </a>
                        </li>
                         <li class="nav-item">
                           <a class="nav-link" href="index.php?page=search">
                                <i class="fas fa-search me-1"></i>Profil Ara
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=mesajlar">
                                <i class="fas fa-envelope me-1"></i>Mesajlar
                                <?php
                                if (file_exists('includes/messaging_functions.php')) {
                                    require_once 'includes/messaging_functions.php';
                                    $okunmamis_sayisi = getToplumOkunmamisMesajSayisi($_SESSION['kullanici_id']);
                                    if ($okunmamis_sayisi > 0):
                                ?>
                                    <span class="badge bg-danger"><?php echo $okunmamis_sayisi; ?></span>
                                <?php 
                                    endif;
                                }
                                ?>
                            </a>
                        </li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <li class="nav-item">
                                <a class="nav-link text-danger" href="admin/admin.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Çıkış</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login">Giriş</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3 ms-2" href="index.php?page=register">Kayıt Ol</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5><i class="fas fa-list me-2"></i>       İlan</h5>
            <button class="btn-close-sidebar" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="sidebar-content">
            <?php
            $markalar = getMarkalar();
            foreach ($markalar as $marka):
            ?>
                <div class="category-item">
                    <a href="#" class="category-link" onclick="toggleModels(<?php echo $marka['id']; ?>)">
                        <i class="fas fa-motorcycle me-2"></i><?php echo $marka['marka_adi']; ?>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="models-list" id="models-<?php echo $marka['id']; ?>">
                        <?php
                        $modeller = getModeller($marka['id']);
                        foreach ($modeller as $model):
                        ?>
                            <a href="index.php?page=ilanlar&marka=<?php echo $marka['id']; ?>&model=<?php echo $model['id']; ?>" class="model-link">
                                <?php echo $model['model_adi']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <?php
        switch ($current_page) {
            case 'home':
                include 'pages/home.php';
                break;
            case 'login':
                include 'pages/login.php';
                break;
            case 'register':
                include 'pages/register.php';
                break;
            case 'profil':
                include 'pages/profil.php';
                break;
            case 'ilan_ekle':
                include 'pages/ilan_ekle.php';
                break;
            case 'ilanlar':
                include 'pages/ilanlar.php';
                break;
            case 'ilan_detay':
                include 'pages/ilan_detay.php';
                break;
            case 'mesajlar':
                include 'pages/mesajlar.php';
                break;
                case 'search':
                include 'pages/search.php';
                break;
            case 'shop':
                if (file_exists('pages/shop.php')) {
                    include 'pages/shop.php';
                } else {
                    include 'pages/home.php';
                }
                break;
            default:
                include 'pages/home.php';
        }
        ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>MotorUnal</h5>
                    <p>Türkiye'nin en güvenilir motor ilanları platformu</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 MotorUnal. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <!-- Chat Widget -->
    <?php include 'chat_widget.php'; ?>

    <script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

function toggleModels(id) {
    const modelList = document.getElementById(`models-${id}`);
    if (modelList.style.display === "block") {
        modelList.style.display = "none";
    } else {
        modelList.style.display = "block";
    }
}
</script>

</body>
</html>