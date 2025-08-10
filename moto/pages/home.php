
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <h1 class="display-4 fw-bold mb-4 fade-in-up">
                    Türkiye'nin En Büyük Motor Platformu
                </h1>
                <p class="lead mb-4 fade-in-up">
                    Binlerce motor ilanı arasından size en uygun olanı bulun. Güvenli, hızlı ve kolay.
                </p>
                <div class="d-flex gap-3 flex-wrap fade-in-up">
                    <a href="index.php?page=ilanlar" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>İlanları İncele
                    </a>
                    <a href="index.php?page=ilan_ekle" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>İlan Ver
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="assets/images/logo.png" 
                     alt="Motor" class="img-fluid rounded shadow-lg pulse">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 mt-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 fw-bold mb-3">Neden MotorUnal?</h2>
                <p class="lead text-muted">Size en iyi hizmeti sunmak için buradayız</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Güvenli Alışveriş</h4>
                    <p class="text-muted">Tüm ilanlar moderatör onayından geçer. Güvenliğiniz bizim önceliğimiz.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Hızlı İlan Verme</h4>
                    <p class="text-muted">Sadece birkaç dakikada ilanınızı yayınlayın ve binlerce kişiye ulaşın.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Gelişmiş Arama</h4>
                    <p class="text-muted">Marka, model, fiyat ve daha fazla kritere göre arama yapın.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Mobil Uyumlu</h4>
                    <p class="text-muted">Her cihazdan kolayca erişim. Responsive tasarım ile her yerde.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->

<!-- Recent Listings -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-3">Son Eklenen İlanlar</h2>
                <p class="lead text-muted">En yeni motor ilanlarını keşfedin</p>
            </div>
        </div>

        <div class="row g-4">
            <?php
            // Son eklenen ilanları getir
            $stmt = $pdo->prepare("
                SELECT i.*, m.marka_adi, md.model_adi, k.ad, k.soyad,
                       (SELECT fotograf_yolu FROM ilan_fotograflari WHERE ilan_id = i.id ORDER BY sira_no LIMIT 1) as fotograf
                FROM ilanlar i 
                JOIN markalar m ON i.marka_id = m.id 
                JOIN modeller md ON i.model_id = md.id 
                JOIN kullanicilar k ON i.kullanici_id = k.id 
                WHERE i.aktif = TRUE AND i.onaylanmis = TRUE
                ORDER BY i.ilan_tarihi DESC 
                LIMIT 6
            ");
            $stmt->execute();
            $son_ilanlar = $stmt->fetchAll();

            foreach ($son_ilanlar as $ilan):
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card ilan-card h-100">
                        <div class="position-relative">
                            <?php if ($ilan['fotograf']): ?>
                                <img src="<?php echo $ilan['fotograf']; ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?php echo $ilan['ilan_ismi']; ?>">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                    <i class="fas fa-motorcycle fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge badge-custom">Yeni</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($ilan['ilan_ismi']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-motorcycle me-1"></i>
                                <?php echo $ilan['marka_adi'] . ' ' . $ilan['model_adi']; ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($ilan['konum']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price-tag"><?php echo number_format($ilan['fiyat'], 0, ',', '.'); ?> ₺</span>
                                <a href="index.php?page=ilan_detay&id=<?php echo $ilan['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    Detay Gör
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="index.php?page=ilanlar" class="btn btn-primary btn-lg">
                <i class="fas fa-eye me-2"></i>Tüm İlanları Görüntüle
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center text-white">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-4">Hemen Başlayın!</h2>
                <p class="lead mb-5">Ücretsiz hesap oluşturun ve 3 ücretsiz ilan hakkınızı kullanın</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <?php if (!isset($_SESSION['kullanici_id'])): ?>
                        <a href="index.php?page=register" class="btn btn-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Ücretsiz Kayıt Ol
                        </a>
                        <a href="index.php?page=login" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=ilan_ekle" class="btn btn-light btn-lg">
                            <i class="fas fa-plus me-2"></i>Hemen İlan Ver
                        </a>
                        <a href="index.php?page=profil" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user me-2"></i>Profilime Git
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <h2 class="display-5 fw-bold mb-4">Biz Kimiz?</h2>
                <p class="lead mb-4">
                    MotorUnal, Türkiye'nin en yeni ve en güvenilir motor alım-satım platformudur. 
                    2025 yılından beri binlerce motor severin buluşma noktasıyız.
                </p>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Güvenilir</h6>
                                <small class="text-muted">Platform</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-heart fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Motor</h6>
                                <small class="text-muted">Tutkusu</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="About Us" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>
