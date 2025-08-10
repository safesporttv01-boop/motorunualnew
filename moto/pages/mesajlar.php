<?php
if (!girisKontrol()) {
    header("Location: index.php?page=login");
    exit;
}

require_once 'includes/messaging_functions.php';

$kullanici_id = $_SESSION['kullanici_id'];
$konusmalar = getKonusmalar($kullanici_id);

// Konuşma silme işlemi
if (isset($_POST['sil_konusma'])) {
    $konusma_id = (int)$_POST['konusma_id'];
    if (konusmaSil($konusma_id, $kullanici_id)) {
        $success = "Konuşma başarıyla silindi.";
        $konusmalar = getKonusmalar($kullanici_id); // Listeyi yenile
    } else {
        $error = "Konuşma silinirken hata oluştu.";
    }
}

$selected_konusma_id = isset($_GET['konusma']) ? (int)$_GET['konusma'] : null;
$selected_konusma = null;
$mesajlar = [];

if ($selected_konusma_id) {
    $selected_konusma = getKonusmaDetay($selected_konusma_id, $kullanici_id);
    if ($selected_konusma) {
        $mesajlar = getKonusmaMesajlari($selected_konusma_id, $kullanici_id);
    }
}
?>

<div class="container-fluid my-4">
    <div class="row">
        <!-- Konuşmalar Listesi -->
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>Mesajlarım
                    </h5>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($konusmalar)): ?>
                        <div class="text-center p-4">
                            <i class="fas fa-inbox display-1 text-muted mb-3"></i>
                            <h6>Henüz mesajınız yok</h6>
                            <p class="text-muted small">İlan sahipleriyle mesajlaşmaya başlayın</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($konusmalar as $konusma): ?>
                            <div class="conversation-item <?php echo ($selected_konusma_id == $konusma['id']) ? 'active' : ''; ?>" 
                                 onclick="selectConversation(<?php echo $konusma['id']; ?>)">
                                <div class="d-flex align-items-center p-3 border-bottom">
                                    <div class="avatar-sm me-3">
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
                                                <?php echo date('d.m.Y H:i', strtotime($konusma['son_mesaj_tarihi'])); ?>
                                            </small>
                                        </div>
                                        <p class="text-muted small mb-1"><?php echo $konusma['ilan_ismi']; ?></p>
                                        <?php if ($konusma['son_mesaj']): ?>
                                            <p class="text-muted small mb-0">
                                                <?php echo mb_substr($konusma['son_mesaj'], 0, 50) . (mb_strlen($konusma['son_mesaj']) > 50 ? '...' : ''); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mesaj Detayı -->
        <div class="col-md-8 col-lg-9">
            <?php if ($selected_konusma): ?>
                <div class="card border-0 shadow-sm h-100">
                    <!-- Konuşma Başlığı -->
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0"><?php echo $selected_konusma['karsi_taraf_ad'] . ' ' . $selected_konusma['karsi_taraf_soyad']; ?></h6>
                                    <small class="text-muted"><?php echo $selected_konusma['ilan_ismi']; ?></small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=ilan_detay&id=<?php echo $selected_konusma['ilan_id']; ?>">
                                            <i class="fas fa-eye me-2"></i>İlanı Görüntüle
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="konusma_id" value="<?php echo $selected_konusma['id']; ?>">
                                            <button type="submit" name="sil_konusma" class="dropdown-item text-danger" 
                                                    onclick="return confirm('Bu konuşmayı silmek istediğinizden emin misiniz?')">
                                                <i class="fas fa-trash me-2"></i>Konuşmayı Sil
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Mesajlar -->
                    <div class="card-body p-0" style="height: 400px; overflow-y: auto;" id="messages-container">
                        <div class="p-3">
                            <?php if (empty($mesajlar)): ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>Henüz mesaj yok. İlk mesajı gönderin!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($mesajlar as $mesaj): ?>
                                    <div class="message-item mb-3 <?php echo ($mesaj['gonderen_id'] == $kullanici_id) ? 'sent' : 'received'; ?>">
                                        <div class="message-bubble">
                                            <?php if ($mesaj['gonderen_id'] != $kullanici_id): ?>
                                                <div class="message-sender"><?php echo $mesaj['ad'] . ' ' . $mesaj['soyad']; ?></div>
                                            <?php endif; ?>
                                            <div class="message-text"><?php echo nl2br(htmlspecialchars($mesaj['mesaj'])); ?></div>
                                            <div class="message-time">
                                                <?php echo date('d.m.Y H:i', strtotime($mesaj['gonderim_tarihi'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Mesaj Gönderme -->
                    <div class="card-footer bg-white">
                        <div class="input-group">
                            <input type="text" id="message-input" class="form-control" 
                                   placeholder="Mesajınızı yazın..." maxlength="1000">
                            <button class="btn btn-primary" type="button" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-comments display-1 text-muted mb-4"></i>
                            <h5>Mesajlaşmaya Başlayın</h5>
                            <p class="text-muted">Sol taraftan bir konuşma seçin veya yeni bir mesaj başlatın</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.conversation-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

.conversation-item.active {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.message-item.sent {
    text-align: right;
}

.message-item.received {
    text-align: left;
}

.message-bubble {
    display: inline-block;
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    word-wrap: break-word;
}

.message-item.sent .message-bubble {
    background-color: #007bff;
    color: white;
}

.message-item.received .message-bubble {
    background-color: #f1f3f4;
    color: #333;
}

.message-sender {
    font-size: 0.8rem;
    font-weight: bold;
    margin-bottom: 4px;
    color: #666;
}

.message-text {
    margin-bottom: 4px;
}

.message-time {
    font-size: 0.7rem;
    opacity: 0.7;
}

.avatar-sm {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

#messages-container {
    scroll-behavior: smooth;
}
</style>

<script>
let websocket = null;
let currentConversationId = <?php echo $selected_konusma_id ? $selected_konusma_id : 'null'; ?>;
let currentUserId = <?php echo $kullanici_id; ?>;

// WebSocket bağlantısı
function connectWebSocket() {
    try {
        websocket = new WebSocket('ws://localhost:8765');
        
        websocket.onopen = function() {
            console.log('WebSocket bağlantısı kuruldu');
            
            // Kullanıcıyı kaydet
            websocket.send(JSON.stringify({
                type: 'register',
                kullanici_id: currentUserId,
                kullanici_ad: '<?php echo $_SESSION['kullanici_ad']; ?>',
                kullanici_email: '<?php echo $_SESSION['kullanici_email']; ?>'
            }));
        };
        
        websocket.onmessage = function(event) {
            const data = JSON.parse(event.data);
            handleWebSocketMessage(data);
        };
        
        websocket.onclose = function() {
            console.log('WebSocket bağlantısı kesildi');
            // 3 saniye sonra yeniden bağlanmaya çalış
            setTimeout(connectWebSocket, 3000);
        };
        
        websocket.onerror = function(error) {
            console.error('WebSocket hatası:', error);
        };
        
    } catch (error) {
        console.error('WebSocket bağlantısı kurulamadı:', error);
        setTimeout(connectWebSocket, 3000);
    }
}

function handleWebSocketMessage(data) {
    if (data.type === 'new_message') {
        if (data.konusma_id == currentConversationId) {
            addMessageToUI(data);
        }
        // Sayfa yenilenmeden konuşma listesini güncelle
        updateConversationList();
    }
}

function addMessageToUI(messageData) {
    const container = document.getElementById('messages-container').querySelector('.p-3');
    const messageDiv = document.createElement('div');
    
    const isOwn = messageData.gonderen_id == currentUserId;
    const messageClass = isOwn ? 'sent' : 'received';
    
    messageDiv.className = `message-item mb-3 ${messageClass}`;
    messageDiv.innerHTML = `
        <div class="message-bubble">
            ${!isOwn ? '<div class="message-sender">Karşı Taraf</div>' : ''}
            <div class="message-text">${messageData.mesaj}</div>
            <div class="message-time">${new Date().toLocaleString('tr-TR')}</div>
        </div>
    `;
    
    container.appendChild(messageDiv);
    scrollToBottom();
}

function sendMessage() {
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message || !currentConversationId || !websocket) {
        return;
    }
    
    websocket.send(JSON.stringify({
        type: 'send_message',
        konusma_id: currentConversationId,
        gonderen_id: currentUserId,
        mesaj: message
    }));
    
    input.value = '';
}

function selectConversation(konusmaId) {
    window.location.href = `index.php?page=mesajlar&konusma=${konusmaId}`;
}

function scrollToBottom() {
    const container = document.getElementById('messages-container');
    container.scrollTop = container.scrollHeight;
}

function updateConversationList() {
    // AJAX ile konuşma listesini güncelle
    // Bu fonksiyon isteğe bağlı olarak implement edilebilir
}

// Enter tuşu ile mesaj gönderme
document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
    
    // WebSocket bağlantısını başlat
    connectWebSocket();
    
    // Sayfa yüklendiğinde en alta kaydır
    scrollToBottom();
});
</script>