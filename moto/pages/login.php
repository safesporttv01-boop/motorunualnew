
<?php
// Eğer kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (girisKontrol()) {
    header("Location: index.php?page=home");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = temizle($_POST['email']);
    $sifre = $_POST['sifre'];
    
    if (empty($email) || empty($sifre)) {
        $error = 'Lütfen tüm alanları doldurun.';
    } else {
        if (kullaniciGiris($email, $sifre)) {
            $success = 'Giriş başarılı! Yönlendiriliyorsunuz...';
            // JavaScript ile yönlendirme
            echo '<script>
                setTimeout(function() {
                    window.location.href = "index.php?page=home";
                }, 1500);
            </script>';
        } else {
            $error = 'E-posta veya şifre hatalı.';
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Giriş Yap</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="sifre" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="sifre" name="sifre" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0">Hesabınız yok mu? 
                            <a href="index.php?page=register" class="text-decoration-none">Kayıt olun</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
