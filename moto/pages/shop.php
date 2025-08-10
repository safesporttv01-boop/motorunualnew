<?php
if (!girisKontrol()) {
    header("Location: index.php?page=login");
    exit();
}

$kullanici = getKullanici($_SESSION['kullanici_id']);
$success = '';
$error = '';

// Satın alma işlemi
if ($_POST && isset($_POST['paket_id'])) {
    $paket_id = (int)$_POST['paket_id'];

    // Paket bilgilerini al
    $paketler = [
        1 => ['ilan_sayisi' => 5, 'fiyat' => 50, 'isim' => 'Başlangıç Paketi'],
        2 => ['ilan_sayisi' => 10, 'fiyat' => 90, 'isim' => 'Standart Paket'],
        3 => ['ilan_sayisi' => 25, 'fiyat' => 200, 'isim' => 'Premium Paket'],
        4 => ['ilan_sayisi' => 50, 'fiyat' => 350, 'isim' => 'Pro Paket']
    ];

    if (isset($paketler[$paket_id])) {
        $paket = $paketler[$paket_id];

        // İlan hakkı ekleme
        if (ilanHakkiEkle($_SESSION['kullanici_id'], $paket['ilan_sayisi'])) {
            $success = $paket['isim'] . ' başarıyla satın alındı! ' . $paket['ilan_sayisi'] . ' ilan hakkı hesabınıza eklendi.';
            $kullanici = getKullanici($_SESSION['kullanici_id']); // Güncel bilgileri al
        } else {
            $error = 'Satın alma işlemi sırasında bir hata oluştu.';
        }
    } else {
        $error = 'Geçersiz paket seçimi.';
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-primary">
                    <i class="fas fa-shopping-cart me-3"></i>İlan Hakkı Mağazası
                </h2>
                <p class="lead text-muted">İlan verme haklarınızı artırın ve daha fazla motoru satışa çıkarın</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Mevcut İlan Hakkınız: <strong><?php echo $kullanici['ilan_hakki']; ?></strong>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <div class="mt-3">
                        <a href="index.php?page=ilan_ekle" class="btn btn-success me-2">
                            <i class="fas fa-plus me-2"></i>Hemen İlan Ver
                        </a>
                        <a href="index.php?page=profil" class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i>Profilime Dön
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Paketler -->
            <div class="row g-4">
                <!-- Başlangıç Paketi -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h5 class="mb-0">Başlangıç</h5>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <h2 class="text-primary">50₺</h2>
                            </div>
                            <h4 class="text-primary mb-3">5 İlan Hakkı</h4>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>5 Adet İlan</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>30 Gün Geçerli</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Fotoğraf Desteği</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Temel Destek</li>
                            </ul>
                            <form method="POST">
                                <input type="hidden" name="paket_id" value="1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Satın Al
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Standart Paket -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-success text-white text-center position-relative">
                            <h5 class="mb-0">Standart</h5>
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">Popüler</span>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <h2 class="text-success">90₺</h2>
                                <small class="text-muted">10₺ tasarruf</small>
                            </div>
                            <h4 class="text-success mb-3">10 İlan Hakkı</h4>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>10 Adet İlan</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>60 Gün Geçerli</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Fotoğraf Desteği</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Öncelikli Destek</li>
                            </ul>
                            <form method="POST">
                                <input type="hidden" name="paket_id" value="2">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Satın Al
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Premium Paket -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-warning text-dark text-center position-relative">
                            <h5 class="mb-0">Premium</h5>
                            <span class="badge bg-info position-absolute top-0 end-0 m-2">En Çok Tercih</span>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <h2 class="text-warning">200₺</h2>
                                <small class="text-muted">50₺ tasarruf</small>
                            </div>
                            <h4 class="text-warning mb-3">25 İlan Hakkı</h4>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>25 Adet İlan</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>90 Gün Geçerli</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Fotoğraf Desteği</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Premium Destek</li>
                            </ul>
                            <form method="POST">
                                <input type="hidden" name="paket_id" value="3">
                                <button type="submit" class="btn btn-warning w-100 text-dark">
                                    <i class="fas fa-shopping-cart me-2"></i>Satın Al
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pro Paket -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-dark text-white text-center position-relative">
                            <h5 class="mb-0">Pro</h5>
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">En Avantajlı</span>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <h2 class="text-dark">350₺</h2>
                                <small class="text-muted">150₺ tasarruf</small>
                            </div>
                            <h4 class="text-dark mb-3">50 İlan Hakkı</h4>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>50 Adet İlan</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>120 Gün Geçerli</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Fotoğraf Desteği</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>VIP Destek</li>
                            </ul>
                            <form method="POST">
                                <input type="hidden" name="paket_id" value="4">
                                <button type="submit" class="btn btn-dark w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Satın Al
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bilgilendirme -->
            <div class="card mt-5 border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-info-circle me-2"></i>Önemli Bilgiler
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>İlan hakları anında hesabınıza eklenir</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Hiçbir gizli ücret yoktur</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>7/24 müşteri desteği</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Güvenli ödeme sistemi</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Kullanılmayan haklar birikiyor</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>İade garantisi</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.price-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 3px solid #dee2e6;
}

.price-amount {
    font-size: 1.5rem;
    font-weight: bold;
    color: #495057;
}

.price-save {
    color: #28a745;
    font-weight: 600;
    font-size: 0.75rem;
}

.card-header .badge {
    position: absolute;
    top: -8px;
    right: 15px;
    font-size: 0.7rem;
}
</style>