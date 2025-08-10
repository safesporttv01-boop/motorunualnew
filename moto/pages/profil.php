
<?php
if (!girisKontrol()) {
    header("Location: index.php?page=login");
    exit;
}

$kullanici = getKullanici($_SESSION['kullanici_id']);
$kullanici_ilanlari = getKullaniciIlanlari($_SESSION['kullanici_id']);

$success = '';
$error = '';

if ($_POST && isset($_POST['profil_guncelle'])) {
    $ad = temizle($_POST['ad']);
    $soyad = temizle($_POST['soyad']);
    $telefon = temizle($_POST['telefon']);
    
    if (empty($ad) || empty($soyad)) {
        $error = 'Ad ve soyad boş bırakılamaz.';
    } else {
        if (profilGuncelle($_SESSION['kullanici_id'], $ad, $soyad, $telefon)) {
            $success = 'Profil başarıyla güncellendi.';
            $kullanici = getKullanici($_SESSION['kullanici_id']);
        } else {
            $error = 'Profil güncellenirken hata oluştu.';
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar-circle mb-3">
                        <i class="fas fa-user fa-3x text-primary"></i>
                    </div>
                    <h5><?php echo $kullanici['ad'] . ' ' . $kullanici['soyad']; ?></h5>
                    <p class="text-muted"><?php echo $kullanici['email']; ?></p>
                    <div class="mt-3">
                        <span class="badge bg-primary">
                            <i class="fas fa-plus me-1"></i>
                            <?php echo $kullanici['ilan_hakki']; ?> İlan Hakkı
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">Hızlı İşlemler</h6>
                    <div class="d-grid gap-2">
                        <a href="index.php?page=ilan_ekle" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Yeni İlan
                        </a>
                        <a href="index.php?page=ilanlar" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-search me-2"></i>İlanları Gör
                        </a>
                        <a href="index.php?page=shop" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-shopping-cart me-2"></i>Mağaza
                        </a>
                        <a href="index.php?page=mesajlar" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-envelope me-2"></i>Mesajlar
                            <?php
                            require_once 'includes/messaging_functions.php';
                            $okunmamis_sayisi = getToplumOkunmamisMesajSayisi($_SESSION['kullanici_id']);
                            if ($okunmamis_sayisi > 0):
                            ?>
                                <span class="badge bg-danger"><?php echo $okunmamis_sayisi; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Profil Güncelleme -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profil Bilgilerim</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ad" class="form-label">Ad</label>
                                <input type="text" class="form-control" id="ad" name="ad" 
                                       value="<?php echo $kullanici['ad']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="soyad" class="form-label">Soyad</label>
                                <input type="text" class="form-control" id="soyad" name="soyad" 
                                       value="<?php echo $kullanici['soyad']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" value="<?php echo $kullanici['email']; ?>" readonly>
                                <div class="form-text">E-posta adresi değiştirilemez</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefon" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="telefon" name="telefon" 
                                       value="<?php echo $kullanici['telefon']; ?>">
                            </div>
                        </div>
                        
                        <button type="submit" name="profil_guncelle" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Profili Güncelle
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Mesajlar Özeti -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Son Mesajlar</h5>
                    <a href="index.php?page=mesajlar" class="btn btn-outline-primary btn-sm">
                        Tümünü Gör
                        <?php if ($okunmamis_sayisi > 0): ?>
                            <span class="badge bg-danger ms-1"><?php echo $okunmamis_sayisi; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    $son_konusmalar = array_slice(getKonusmalar($_SESSION['kullanici_id']), 0, 3);
                    if (empty($son_konusmalar)):
                    ?>
                        <div class="text-center py-3">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Henüz mesajınız bulunmuyor</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($son_konusmalar as $konusma): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="me-3">
                                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">
                                            <?php echo $konusma['karsi_taraf_ad'] . ' ' . $konusma['karsi_taraf_soyad']; ?>
                                            <?php if ($konusma['okunmamis_sayisi'] > 0): ?>
                                                <span class="badge bg-danger ms-1"><?php echo $konusma['okunmamis_sayisi']; ?></span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('d.m H:i', strtotime($konusma['son_mesaj_tarihi'])); ?>
                                        </small>
                                    </div>
                                    <p class="text-muted small mb-1"><?php echo $konusma['ilan_ismi']; ?></p>
                                    <?php if ($konusma['son_mesaj']): ?>
                                        <p class="text-muted small mb-0">
                                            <?php echo mb_substr($konusma['son_mesaj'], 0, 40) . (mb_strlen($konusma['son_mesaj']) > 40 ? '...' : ''); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="index.php?page=mesajlar&konusma=<?php echo $konusma['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-reply"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- İlanlarım -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>İlanlarım</h5>
                    <a href="index.php?page=ilan_ekle" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Yeni İlan Ekle
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($kullanici_ilanlari)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-motorcycle display-1 text-muted mb-3"></i>
                            <h5>Henüz ilanınız bulunmuyor</h5>
                            <p class="text-muted">İlk ilanınızı oluşturmak için aşağıdaki butona tıklayın</p>
                            <a href="index.php?page=ilan_ekle" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>İlan Ver
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($kullanici_ilanlari as $ilan): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <?php if ($ilan['ilk_fotograf']): ?>
                                            <img src="<?php echo $ilan['ilk_fotograf']; ?>" 
                                                 class="card-img-top" style="height: 200px; object-fit: cover;" 
                                                 alt="<?php echo $ilan['ilan_ismi']; ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <i class="fas fa-motorcycle fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo $ilan['ilan_ismi']; ?></h6>
                                            <p class="text-muted small mb-2">
                                                <?php echo $ilan['marka_adi'] . ' ' . $ilan['model_adi']; ?>
                                            </p>
                                            <p class="price-tag mb-2">
                                                <?php echo number_format($ilan['fiyat'], 0, ',', '.'); ?> ₺
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <?php echo date('d.m.Y', strtotime($ilan['ilan_tarihi'])); ?>
                                                </small>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?page=ilan_detay&id=<?php echo $ilan['id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm">Görüntüle</a>
                                                    <a href="index.php?page=ilan_duzenle&id=<?php echo $ilan['id']; ?>" 
                                                       class="btn btn-outline-secondary btn-sm">Düzenle</a>
                                                </div>
                                            </div>
                                            <?php if (!$ilan['onaylanmis']): ?>
                                                <div class="mt-2">
                                                    <span class="badge bg-warning">Onay Bekliyor</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
</style>
