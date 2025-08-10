
<?php
if (girisKontrol()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $ad = temizle($_POST['ad']);
    $soyad = temizle($_POST['soyad']);
    $email = temizle($_POST['email']);
    $telefon = temizle($_POST['telefon']);
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];
    
    if (empty($ad) || empty($soyad) || empty($email) || empty($sifre)) {
        $error = 'Lütfen zorunlu alanları doldurun.';
    } elseif ($sifre !== $sifre_tekrar) {
        $error = 'Şifreler eşleşmiyor.';
    } elseif (strlen($sifre) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır.';
    } else {
        if (kullaniciKaydet($ad, $soyad, $email, $sifre, $telefon)) {
            $success = 'Kayıt başarılı! Giriş yapabilirsiniz.';
        } else {
            $error = 'Bu e-posta adresi zaten kullanımda.';
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Kayıt Ol</h3>
                    <p class="text-center text-muted mb-4">
                        Ücretsiz hesap oluşturun ve 3 bedava ilan hakkınızı kazanın!
                    </p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <a href="index.php?page=login" class="alert-link">Giriş yapmak için tıklayın</a>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ad" class="form-label">Ad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ad" name="ad" required
                                       value="<?php echo isset($_POST['ad']) ? $_POST['ad'] : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="soyad" class="form-label">Soyad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="soyad" name="soyad" required
                                       value="<?php echo isset($_POST['soyad']) ? $_POST['soyad'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefon" class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="telefon" name="telefon"
                                   value="<?php echo isset($_POST['telefon']) ? $_POST['telefon'] : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sifre" class="form-label">Şifre <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="sifre" name="sifre" required>
                                <div class="form-text">En az 6 karakter</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sifre_tekrar" class="form-label">Şifre Tekrar <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="sifre_tekrar" name="sifre_tekrar" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-user-plus me-2"></i>Kayıt Ol
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0">Zaten hesabınız var mı? 
                            <a href="index.php?page=login" class="text-decoration-none">Giriş yapın</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
