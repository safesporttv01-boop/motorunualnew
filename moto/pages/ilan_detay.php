<?php
$ilan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$ilan_id) {
    header("Location: index.php");
    exit;
}

$ilan = getIlan($ilan_id);
if (!$ilan) {
    header("Location: index.php");
    exit;
}

$fotograflar = getIlanFotograflari($ilan_id);
?>

<div class="container my-4">
    <div class="row">
        <!-- Fotoğraflar -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                  <?php if (!empty($fotograflar)): ?>
    <div id="ilanCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($fotograflar as $index => $foto): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo $foto['fotograf_yolu']; ?>"
                         class="d-block w-100" style="height: 400px; object-fit: cover;"
                         alt="<?php echo $ilan['ilan_ismi']; ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($fotograflar) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#ilanCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#ilanCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>

            <div class="carousel-indicators">
                <?php foreach ($fotograflar as $index => $foto): ?>
                    <button type="button" data-bs-target="#ilanCarousel"
                            data-bs-slide-to="<?php echo $index; ?>"
                            <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
        <i class="fas fa-motorcycle fa-5x text-muted"></i>
    </div>
<?php endif; ?>

            <!-- Açıklama -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Açıklama</h5>
                </div>
                <div class="card-body">
                    <?php if ($ilan['aciklama']): ?>
                        <p class="mb-0"><?php echo nl2br($ilan['aciklama']); ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-0">Bu ilan için açıklama eklenmemiş.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ürün Bilgileri -->
        <div class="col-lg-4">
            <!-- Temel Bilgiler -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title"><?php echo $ilan['ilan_ismi']; ?></h4>

                    <div class="mb-3">
                        <span class="badge bg-primary me-1"><?php echo $ilan['marka_adi']; ?></span>
                        <span class="badge bg-secondary"><?php echo $ilan['model_adi']; ?></span>
                    </div>

                    <div class="price-tag mb-3" style="font-size: 2rem;">
                        <?php echo number_format($ilan['fiyat'], 0, ',', '.'); ?> ₺
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">İlan No: <?php echo $ilan['ilan_no']; ?></small><br>
                        <small class="text-muted">İlan Tarihi: <?php echo date('d.m.Y', strtotime($ilan['ilan_tarihi'])); ?></small>
                    </div>

                    <?php if ($ilan['konum']): ?>
                        <div class="mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            <span><?php echo $ilan['konum']; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($ilan['takas']): ?>
                        <div class="mb-3">
                            <span class="badge bg-success">
                                <i class="fas fa-exchange-alt me-1"></i>Takas Kabul Edilir
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Teknik Özellikler -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Teknik Özellikler</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <?php if ($ilan['tipi']): ?>
                            <tr>
                                <td class="text-muted">Tip:</td>
                                <td><strong><?php echo $ilan['tipi']; ?></strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['km']): ?>
                            <tr>
                                <td class="text-muted">KM:</td>
                                <td><strong><?php echo number_format($ilan['km'], 0, ',', '.'); ?> km</strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['motor_hacmi']): ?>
                            <tr>
                                <td class="text-muted">Motor Hacmi:</td>
                                <td><strong><?php echo $ilan['motor_hacmi']; ?> cc</strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['motor_gucu']): ?>
                            <tr>
                                <td class="text-muted">Motor Gücü:</td>
                                <td><strong><?php echo $ilan['motor_gucu']; ?> HP</strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['zamanlama_tipi']): ?>
                            <tr>
                                <td class="text-muted">Zamanlama:</td>
                                <td><strong><?php echo $ilan['zamanlama_tipi']; ?></strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['silindir_sayisi']): ?>
                            <tr>
                                <td class="text-muted">Silindir:</td>
                                <td><strong><?php echo $ilan['silindir_sayisi']; ?></strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['sogutma']): ?>
                            <tr>
                                <td class="text-muted">Soğutma:</td>
                                <td><strong><?php echo $ilan['sogutma']; ?></strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['renk']): ?>
                            <tr>
                                <td class="text-muted">Renk:</td>
                                <td><strong><?php echo $ilan['renk']; ?></strong></td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($ilan['plaka_uyruk']): ?>
                            <tr>
                                <td class="text-muted">Plaka:</td>
                                <td><strong><?php echo $ilan['plaka_uyruk']; ?></strong></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- İletişim -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-phone me-2"></i>İletişim</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong><?php echo $ilan['ad'] . ' ' . $ilan['soyad']; ?></strong>
                    </div>

                    <?php if ($ilan['telefon']): ?>
                        <div class="mb-3">
                            <a href="tel:<?php echo $ilan['telefon']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-phone me-2"></i><?php echo $ilan['telefon']; ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($ilan['iletisim_bilgi']): ?>
                        <div class="alert alert-info">
                            <small><?php echo nl2br($ilan['iletisim_bilgi']); ?></small>
                        </div>
                    <?php endif; ?>

                    <!-- Mesaj Gönderme Butonu -->
                    <?php if (girisKontrol() && $_SESSION['kullanici_id'] != $ilan['kullanici_id']): ?>
                        <div class="mb-3">
                            <button class="btn btn-info w-100" onclick="openMessageModal()">
                                <i class="fas fa-envelope me-2"></i>İlan Sahibine Mesaj Gönder
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="text-center">
                        <a href="https://wa.me/90<?php echo $ilan['telefon']; ?>?text=Merhaba,%20<?php echo urlencode($ilan['ilan_ismi']); ?>%20ilanınızla%20ilgileniyorum." target="_blank" class="btn btn-success w-100 mb-2">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp ile İletişim
                        </a>
                        <button class="btn btn-outline-primary w-100" onclick="shareAd()">
                            <i class="fas fa-share me-2"></i>İlanı Paylaş
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mesaj Gönderme Modal -->
<?php if (girisKontrol() && $_SESSION['kullanici_id'] != $ilan['kullanici_id']): ?>
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2"></i>Mesaj Gönder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <?php if (!empty($fotograflar)): ?>
                        <img src="<?php echo $fotograflar[0]['fotograf_yolu']; ?>" 
                             class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;" 
                             alt="<?php echo $ilan['ilan_ismi']; ?>">
                    <?php else: ?>
                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-motorcycle text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h6 class="mb-1"><?php echo $ilan['ilan_ismi']; ?></h6>
                        <small class="text-muted"><?php echo number_format($ilan['fiyat'], 0, ',', '.'); ?> ₺</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="messageText" class="form-label">Mesajınız</label>
                    <textarea class="form-control" id="messageText" rows="4" 
                              placeholder="Merhaba, ilanınızla ilgileniyorum..."></textarea>
                </div>
                
                <div id="messageStatus" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="sendMessageToOwner()">
                    <i class="fas fa-paper-plane me-2"></i>Mesaj Gönder
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
let messageWebSocket = null;

// WebSocket bağlantısı
function connectMessageWebSocket() {
    <?php if (girisKontrol()): ?>
    try {
        messageWebSocket = new WebSocket('ws://localhost:8765');
        
        messageWebSocket.onopen = function() {
            console.log('Mesaj WebSocket bağlantısı kuruldu');
            
            // Kullanıcıyı kaydet
            messageWebSocket.send(JSON.stringify({
                type: 'register',
                kullanici_id: <?php echo $_SESSION['kullanici_id']; ?>,
                kullanici_ad: '<?php echo $_SESSION['kullanici_ad']; ?>',
                kullanici_email: '<?php echo $_SESSION['kullanici_email']; ?>'
            }));
        };
        
        messageWebSocket.onmessage = function(event) {
            const data = JSON.parse(event.data);
            handleMessageResponse(data);
        };
        
        messageWebSocket.onclose = function() {
            console.log('Mesaj WebSocket bağlantısı kesildi');
            setTimeout(connectMessageWebSocket, 3000);
        };
        
        messageWebSocket.onerror = function(error) {
            console.error('Mesaj WebSocket hatası:', error);
        };
        
    } catch (error) {
        console.error('Mesaj WebSocket bağlantısı kurulamadı:', error);
        setTimeout(connectMessageWebSocket, 3000);
    }
    <?php endif; ?>
}

function handleMessageResponse(data) {
    const statusDiv = document.getElementById('messageStatus');
    
    if (data.type === 'conversation_started') {
        if (data.success) {
            // Konuşma başlatıldı, şimdi mesajı gönder
            const messageText = document.getElementById('messageText').value.trim();
            if (messageText && messageWebSocket) {
                messageWebSocket.send(JSON.stringify({
                    type: 'send_message',
                    konusma_id: data.konusma_id,
                    gonderen_id: <?php echo girisKontrol() ? $_SESSION['kullanici_id'] : 'null'; ?>,
                    mesaj: messageText
                }));
            }
        } else {
            statusDiv.className = 'alert alert-danger';
            statusDiv.textContent = 'Konuşma başlatılamadı: ' + (data.error || 'Bilinmeyen hata');
            statusDiv.style.display = 'block';
        }
    } else if (data.type === 'new_message' && data.status === 'sent') {
        statusDiv.className = 'alert alert-success';
        statusDiv.innerHTML = '<i class="fas fa-check me-2"></i>Mesajınız başarıyla gönderildi!';
        statusDiv.style.display = 'block';
        
        // 2 saniye sonra modal'ı kapat ve mesajlar sayfasına yönlendir
        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('messageModal'));
            modal.hide();
            window.location.href = 'index.php?page=mesajlar';
        }, 2000);
    }
}

function openMessageModal() {
    const modal = new bootstrap.Modal(document.getElementById('messageModal'));
    modal.show();
    
    // Status mesajını temizle
    const statusDiv = document.getElementById('messageStatus');
    statusDiv.style.display = 'none';
    
    // Mesaj alanını temizle
    document.getElementById('messageText').value = '';
}

function sendMessageToOwner() {
    const messageText = document.getElementById('messageText').value.trim();
    
    if (!messageText) {
        alert('Lütfen mesajınızı yazın.');
        return;
    }
    
    if (!messageWebSocket || messageWebSocket.readyState !== WebSocket.OPEN) {
        alert('Bağlantı hatası. Lütfen sayfayı yenileyin.');
        return;
    }
    
    // Önce konuşmayı başlat
    messageWebSocket.send(JSON.stringify({
        type: 'start_conversation',
        gonderen_id: <?php echo girisKontrol() ? $_SESSION['kullanici_id'] : 'null'; ?>,
        alici_id: <?php echo $ilan['kullanici_id']; ?>,
        ilan_id: <?php echo $ilan['id']; ?>,
        baslik: '<?php echo addslashes($ilan['ilan_ismi']); ?> Hakkında'
    }));
    
    // Buton durumunu değiştir
    const sendButton = event.target;
    sendButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gönderiliyor...';
    
    // 10 saniye sonra butonu tekrar aktif et
    setTimeout(() => {
        sendButton.disabled = false;
        sendButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Mesaj Gönder';
    }, 10000);
}

function shareAd() {
    const ilanBaslik = '<?php echo addslashes($ilan["ilan_ismi"]); ?>';
    const ilanUrl = window.location.href;
    const ilanFiyat = '<?php echo number_format($ilan["fiyat"], 0, ",", "."); ?> ₺';
    const shareText = `${ilanBaslik} - ${ilanFiyat}\n${ilanUrl}`;

    if (navigator.share) {
        navigator.share({
            title: ilanBaslik,
            text: shareText,
            url: ilanUrl
        }).catch(console.error);
    } else {
        // Fallback - metni panoya kopyala
        navigator.clipboard.writeText(shareText).then(() => {
            alert('İlan linki panoya kopyalandı!');
        }).catch(() => {
            // Eski tarayıcılar için fallback
            const textArea = document.createElement('textarea');
            textArea.value = shareText;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('İlan linki panoya kopyalandı!');
        });
    }
}

// Sayfa yüklendiğinde WebSocket bağlantısını başlat
document.addEventListener('DOMContentLoaded', function() {
    connectMessageWebSocket();
});
</script>