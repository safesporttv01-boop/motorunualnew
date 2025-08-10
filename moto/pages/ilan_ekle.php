<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!girisKontrol()) {
    header("Location: ../index.php?page=login");
    exit;
}

$kullanici = getKullanici($_SESSION['kullanici_id']);

if (!isset($kullanici['ilan_hakki']) || $kullanici['ilan_hakki'] < 1) {
header("Location: /moto/index.php?page=shop");
    exit;
}


$markalar = getMarkalar();
$error = '';
$success = '';

if ($_POST) {
    $data = [
        'ilan_ismi' => temizle($_POST['ilan_ismi']),
        'marka_id' => (int)$_POST['marka_id'],
        'model_id' => (int)$_POST['model_id'],
        'konum' => temizle($_POST['konum']),
        'fiyat' => (float)str_replace('.', '', $_POST['fiyat']),
        'tipi' => temizle($_POST['tipi']),
        'km' => (int)$_POST['km'],
        'motor_hacmi' => (int)$_POST['motor_hacmi'],
        'motor_gucu' => (int)$_POST['motor_gucu'],
        'zamanlama_tipi' => temizle($_POST['zamanlama_tipi']),
        'silindir_sayisi' => temizle($_POST['silindir_sayisi']),
        'sogutma' => temizle($_POST['sogutma']),
        'renk' => temizle($_POST['renk']),
        'plaka_uyruk' => temizle($_POST['plaka_uyruk']),
        'takas' => isset($_POST['takas']),
        'iletisim_bilgi' => temizle($_POST['iletisim_bilgi']),
        'aciklama' => temizle($_POST['aciklama']),
        'telefon' => temizle($_POST['telefon'])
    ];

    if (empty($data['ilan_ismi']) || empty($data['marka_id']) || empty($data['model_id']) || empty($data['fiyat'])) {
        $error = 'Lütfen zorunlu alanları doldurun.';
    } else {
        $ilan_id = ilanKaydet($data);
        if ($ilan_id) {
            // Fotoğraf yükleme işlemi burada yapılacak
            $success = 'İlanınız başarıyla eklendi! Onaylandıktan sonra yayınlanacaktır.';
        } else {
            $error = 'İlan eklenirken hata oluştu.';
        }
    }
}


?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Yeni İlan Ekle</h4>
                    <small>Kalan İlan Hakkınız: <?php echo $kullanici['ilan_hakki']; ?></small>
                    <?php if ($kullanici['ilan_hakki'] <= 3): ?>
                        <div class="mt-2">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                İlan haklarınız azalıyor! 
                                <a href="index.php?page=shop" class="text-decoration-none">
                                    <strong>Mağazadan yeni haklar satın alın</strong>
                                </a>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <div class="mt-2">
                                <a href="index.php?page=profil" class="btn btn-sm btn-outline-success">Profilime Git</a>
                                <a href="index.php?page=ilan_ekle" class="btn btn-sm btn-primary">Yeni İlan Ekle</a>
                            </div>
                        </div>
                    <?php else: ?>

                    <form method="POST" enctype="multipart/form-data">
                        <!-- Temel Bilgiler -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary"><i class="fas fa-info-circle me-2"></i>Temel Bilgiler</h5>
                                <hr>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="ilan_ismi" class="form-label">İlan Başlığı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ilan_ismi" name="ilan_ismi" required
                                       placeholder="Örn: Tertemiz 2020 Honda CBR600RR"
                                       value="<?php echo isset($_POST['ilan_ismi']) ? $_POST['ilan_ismi'] : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="marka_id" class="form-label">Marka <span class="text-danger">*</span></label>
                                <select class="form-select" id="marka_id" name="marka_id" required onchange="getModels()">
                                    <option value="">Marka Seçiniz</option>
                                    <?php foreach ($markalar as $marka): ?>
                                        <option value="<?php echo $marka['id']; ?>"
                                                <?php echo (isset($_POST['marka_id']) && $_POST['marka_id'] == $marka['id']) ? 'selected' : ''; ?>>
                                            <?php echo $marka['marka_adi']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="model_id" class="form-label">Model <span class="text-danger">*</span></label>
                                <select class="form-select" id="model_id" name="model_id" required>
                                    <option value="">Önce Marka Seçiniz</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="konum" class="form-label">Konum</label>
                                <input type="text" class="form-control" id="konum" name="konum"
                                       placeholder="Örn: İzmir, Bornova"
                                       value="<?php echo isset($_POST['konum']) ? $_POST['konum'] : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fiyat" class="form-label">Fiyat (₺) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fiyat" name="fiyat" required
                                       onkeyup="formatPrice(this)"
                                       placeholder="Örn: 45.000"
                                       value="<?php echo isset($_POST['fiyat']) ? $_POST['fiyat'] : ''; ?>">
                            </div>
                        </div>

                        <!-- Teknik Özellikler -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary"><i class="fas fa-cogs me-2"></i>Teknik Özellikler</h5>
                                <hr>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tipi" class="form-label">Motor Tipi</label>
                                <select class="form-select" id="tipi" name="tipi">
                                    <option value="">Seçiniz</option>
                                    <option value="Scooter" <?php echo (isset($_POST['tipi']) && $_POST['tipi'] == 'Scooter') ? 'selected' : ''; ?>>Scooter</option>
                                    <option value="Naked" <?php echo (isset($_POST['tipi']) && $_POST['tipi'] == 'Naked') ? 'selected' : ''; ?>>Naked</option>
                                    <option value="Sport" <?php echo (isset($_POST['tipi']) && $_POST['tipi'] == 'Sport') ? 'selected' : ''; ?>>Sport</option>
                                    <option value="Touring" <?php echo (isset($_POST['tipi']) && $_POST['tipi'] == 'Touring') ? 'selected' : ''; ?>>Touring</option>
                                    <option value="Adventure" <?php echo (isset($_POST['tipi']) && $_POST['tipi'] == 'Adventure') ? 'selected' : ''; ?>>Adventure</option>
                                    <option value="Cruiser" <?php echo (isset($_POST['tipi']) && $_POST['tipi'] == 'Cruiser') ? 'selected' : ''; ?>>Cruiser</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="km" class="form-label">Kilometre</label>
                                <input type="number" class="form-control" id="km" name="km"
                                       value="<?php echo isset($_POST['km']) ? $_POST['km'] : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="motor_hacmi" class="form-label">Motor Hacmi (cc)</label>
                                <input type="number" class="form-control" id="motor_hacmi" name="motor_hacmi"
                                       value="<?php echo isset($_POST['motor_hacmi']) ? $_POST['motor_hacmi'] : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="motor_gucu" class="form-label">Motor Gücü (HP)</label>
                                <input type="number" class="form-control" id="motor_gucu" name="motor_gucu"
                                       value="<?php echo isset($_POST['motor_gucu']) ? $_POST['motor_gucu'] : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="zamanlama_tipi" class="form-label">Zamanlama Tipi</label>
                                <select class="form-select" id="zamanlama_tipi" name="zamanlama_tipi">
                                    <option value="">Seçiniz</option>
                                    <option value="2 Zamanlı" <?php echo (isset($_POST['zamanlama_tipi']) && $_POST['zamanlama_tipi'] == '2 Zamanlı') ? 'selected' : ''; ?>>2 Zamanlı</option>
                                    <option value="4 Zamanlı" <?php echo (isset($_POST['zamanlama_tipi']) && $_POST['zamanlama_tipi'] == '4 Zamanlı') ? 'selected' : ''; ?>>4 Zamanlı</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="silindir_sayisi" class="form-label">Silindir Sayısı</label>
                                <select class="form-select" id="silindir_sayisi" name="silindir_sayisi">
                                    <option value="">Seçiniz</option>
                                    <option value="Tek Silindir" <?php echo (isset($_POST['silindir_sayisi']) && $_POST['silindir_sayisi'] == 'Tek Silindir') ? 'selected' : ''; ?>>Tek Silindir</option>
                                    <option value="İki Silindir" <?php echo (isset($_POST['silindir_sayisi']) && $_POST['silindir_sayisi'] == 'İki Silindir') ? 'selected' : ''; ?>>İki Silindir</option>
                                    <option value="Üç Silindir" <?php echo (isset($_POST['silindir_sayisi']) && $_POST['silindir_sayisi'] == 'Üç Silindir') ? 'selected' : ''; ?>>Üç Silindir</option>
                                    <option value="Dört Silindir" <?php echo (isset($_POST['silindir_sayisi']) && $_POST['silindir_sayisi'] == 'Dört Silindir') ? 'selected' : ''; ?>>Dört Silindir</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sogutma" class="form-label">Soğutma Sistemi</label>
                                <select class="form-select" id="sogutma" name="sogutma">
                                    <option value="">Seçiniz</option>
                                    <option value="Su" <?php echo (isset($_POST['sogutma']) && $_POST['sogutma'] == 'Su') ? 'selected' : ''; ?>>Su</option>
                                    <option value="Hava" <?php echo (isset($_POST['sogutma']) && $_POST['sogutma'] == 'Hava') ? 'selected' : ''; ?>>Hava</option>
                                    <option value="Yağ" <?php echo (isset($_POST['sogutma']) && $_POST['sogutma'] == 'Yağ') ? 'selected' : ''; ?>>Yağ</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="renk" class="form-label">Renk</label>
                                <input type="text" class="form-control" id="renk" name="renk"
                                       value="<?php echo isset($_POST['renk']) ? $_POST['renk'] : ''; ?>">
                            </div>
                        </div>

                        <!-- Diğer Bilgiler -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary"><i class="fas fa-clipboard-list me-2"></i>Diğer Bilgiler</h5>
                                <hr>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="plaka_uyruk" class="form-label">Plaka Uyruğu</label>
                                <input type="text" class="form-control" id="plaka_uyruk" name="plaka_uyruk"
                                       placeholder="Örn: Türkiye TR Plakalı"
                                       value="<?php echo isset($_POST['plaka_uyruk']) ? $_POST['plaka_uyruk'] : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefon" class="form-label">İletişim Telefonu</label>
                                <input type="tel" class="form-control" id="telefon" name="telefon"
                                       onkeyup="formatPhone(this)"
                                       value="<?php echo isset($_POST['telefon']) ? $_POST['telefon'] : $kullanici['telefon']; ?>">
                            </div>

                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="takas" name="takas" value="1"
                                           <?php echo (isset($_POST['takas'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="takas">
                                        Takas kabul edilir
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="iletisim_bilgi" class="form-label">İletişim Bilgileri</label>
                                <textarea class="form-control" id="iletisim_bilgi" name="iletisim_bilgi" rows="3"
                                          placeholder="İletişim için özel notlarınız..."><?php echo isset($_POST['iletisim_bilgi']) ? $_POST['iletisim_bilgi'] : ''; ?></textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="aciklama" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="4"
                                          placeholder="Motorunuz hakkında detaylı açıklama yazın..."><?php echo isset($_POST['aciklama']) ? $_POST['aciklama'] : ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Fotoğraflar -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary"><i class="fas fa-camera me-2"></i>Fotoğraflar</h5>
                                <hr>
                            </div>

                            <div class="col-12">
                                <div class="file-upload-wrapper">
                                    <input type="file" id="fotograflar" name="fotograflar[]" multiple accept="image/*" onchange="previewImages(this)">
                                    <label for="fotograflar" class="file-upload-text">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
                                        Fotoğraf yüklemek için tıklayın (En fazla 15 adet)
                                    </label>
                                </div>
                                <div id="image-preview" class="mt-3"></div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i>İlanı Yayınla
                            </button>
                        </div>
                    </form>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getModels() {
    const markaId = document.getElementById('marka_id').value;
    const modelSelect = document.getElementById('model_id');

    if (!markaId) {
        modelSelect.innerHTML = '<option value="">Önce Marka Seçiniz</option>';
        return;
    }

    fetch('ajax/get_models.php?marka_id=' + markaId)
        .then(response => response.json())
        .then(data => {
            modelSelect.innerHTML = '<option value="">Model Seçiniz</option>';
            data.forEach(model => {
                modelSelect.innerHTML += `<option value="${model.id}">${model.model_adi}</option>`;
            });
        });
}

function formatPrice(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = value;
}

function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 10) {
        value = value.replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '($1) $2 $3 $4');
    }
    input.value = value;
}

let selectedFiles = [];

function previewImages(input) {
    const files = Array.from(input.files);
    const maxFiles = 15;

    if (selectedFiles.length + files.length > maxFiles) {
        alert(`En fazla ${maxFiles} fotoğraf yükleyebilirsiniz!`);
        return;
    }

    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            selectedFiles.push(file);

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('image-preview');
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeImage(${selectedFiles.length - 1})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        }
    });

    updateFileInput();
}

function removeImage(index) {
    selectedFiles.splice(index, 1);

    // Preview'ları yeniden oluştur
    const previewContainer = document.getElementById('image-preview');
    previewContainer.innerHTML = '';

    selectedFiles.forEach((file, newIndex) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-image" onclick="removeImage(${newIndex})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            previewContainer.appendChild(previewItem);
        };
        reader.readAsDataURL(file);
    });

    updateFileInput();
}

function updateFileInput() {
    const input = document.getElementById('fotograflar');
    const dt = new DataTransfer();

    selectedFiles.forEach(file => {
        dt.items.add(file);
    });

    input.files = dt.files;
}
</script>
