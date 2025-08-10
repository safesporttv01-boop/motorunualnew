
<?php
$marka_id = isset($_GET['marka']) ? (int)$_GET['marka'] : null;
$model_id = isset($_GET['model']) ? (int)$_GET['model'] : null;

$ilanlar = getIlanlar(50, $marka_id, $model_id);
$markalar = getMarkalar();

$selected_marka = null;
$selected_model = null;

if ($marka_id) {
    foreach ($markalar as $marka) {
        if ($marka['id'] == $marka_id) {
            $selected_marka = $marka;
            break;
        }
    }
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if ($user_id) {
    $ilanlar = getKullaniciIlanlari($user_id); // Fetch ads for the user
    $ilanlar_count = count($ilanlar);
} else {
    $ilanlar = getIlanlar(); // Fetch all ads
    $ilanlar_count = count($ilanlar);
}
?>

<div class="container my-4">
    <!-- Filtreler -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtreler</h5>
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="ilanlar">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="filter_marka" class="form-label">Marka</label>
                        <select class="form-select" id="filter_marka" name="marka" onchange="getModelsForFilter()">
                            <option value="">Tüm Markalar</option>
                            <?php foreach ($markalar as $marka): ?>
                                <option value="<?php echo $marka['id']; ?>"
                                        <?php echo ($marka_id == $marka['id']) ? 'selected' : ''; ?>>
                                    <?php echo $marka['marka_adi']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="filter_model" class="form-label">Model</label>
                        <select class="form-select" id="filter_model" name="model">
                            <option value="">Tüm Modeller</option>
                            <?php if ($marka_id): ?>
                                <?php
                                $modeller = getModeller($marka_id);
                                foreach ($modeller as $model):
                                ?>
                                    <option value="<?php echo $model['id']; ?>"
                                            <?php echo ($model_id == $model['id']) ? 'selected' : ''; ?>>
                                        <?php echo $model['model_adi']; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-2"></i>Filtrele
                        </button>
                        <a href="index.php?page=ilanlar" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Temizle
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Sonuçlar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>
            <?php if ($selected_marka): ?>
                <?php echo $selected_marka['marka_adi']; ?>
                <?php if ($selected_model): ?>
                    <?php echo $selected_model['model_adi']; ?>
                <?php endif; ?>
                İlanları
            <?php else: ?>
                Tüm İlanlar
            <?php endif; ?>
        </h4>
        <span class="text-muted"><?php echo count($ilanlar); ?> ilan bulundu</span>
    </div>
    
    <?php if (empty($ilanlar)): ?>
        <div class="text-center py-5">
            <i class="fas fa-search display-1 text-muted mb-3"></i>
            <h5>İlan bulunamadı</h5>
            <p class="text-muted">Aradığınız kriterlere uygun ilan bulunmamaktadır.</p>
            <a href="index.php?page=ilanlar" class="btn btn-primary">Tüm İlanları Görüntüle</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($ilanlar as $ilan): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <?php if ($ilan['ilk_fotograf']): ?>
                            <img src="<?php echo $ilan['ilk_fotograf']; ?>" 
                                 class="card-img-top" style="height: 250px; object-fit: cover;" 
                                 alt="<?php echo $ilan['ilan_ismi']; ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 250px;">
                                <i class="fas fa-motorcycle fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="index.php?page=ilan_detay&id=<?php echo $ilan['id']; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo $ilan['ilan_ismi']; ?>
                                </a>
                            </h6>
                            
                            <div class="mb-2">
                                <span class="badge bg-primary"><?php echo $ilan['marka_adi']; ?></span>
                                <span class="badge bg-secondary"><?php echo $ilan['model_adi']; ?></span>
                            </div>
                            
                            <p class="price-tag mb-2">
                                <?php echo number_format($ilan['fiyat'], 0, ',', '.'); ?> ₺
                            </p>
                            
                            <div class="mb-2">
                                <?php if ($ilan['konum']): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo $ilan['konum']; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('d.m.Y', strtotime($ilan['ilan_tarihi'])); ?>
                                </small>
                                
                                <a href="index.php?page=ilan_detay&id=<?php echo $ilan['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Detay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination burada eklenebilir -->
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary" onclick="loadMore()">
                <i class="fas fa-plus me-2"></i>Daha Fazla Göster
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
function getModelsForFilter() {
    const markaId = document.getElementById('filter_marka').value;
    const modelSelect = document.getElementById('filter_model');
    
    if (!markaId) {
        modelSelect.innerHTML = '<option value="">Tüm Modeller</option>';
        return;
    }
    
    fetch('ajax/get_models.php?marka_id=' + markaId)
        .then(response => response.json())
        .then(data => {
            modelSelect.innerHTML = '<option value="">Tüm Modeller</option>';
            data.forEach(model => {
                modelSelect.innerHTML += `<option value="${model.id}">${model.model_adi}</option>`;
            });
        });
}

function loadMore() {
    // AJAX ile daha fazla ilan yükleme işlemi
    console.log('Daha fazla ilan yükleniyor...');
}
</script>
