
<?php if (girisKontrol()): ?>
<div id="chat-widget" class="chat-widget">
    <div id="chat-toggle" class="chat-toggle">
        <i class="fas fa-comments"></i>
        <span id="chat-badge" class="chat-badge" style="display: none;">0</span>
    </div>
    
    <div id="chat-window" class="chat-window">
        <div class="chat-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-headset me-2"></i>
                    <strong>Canlı Destek</strong>
                </div>
                <button id="close-chat" class="btn btn-sm btn-link text-white p-0">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div id="chat-messages" class="chat-messages">
            <div class="welcome-message">
                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Merhaba! Size nasıl yardımcı olabiliriz?
                </div>
            </div>
        </div>
        
        <div class="chat-input-area">
            <div class="input-group">
                <input type="text" id="user-message-input" class="form-control" placeholder="Mesajınızı yazın..." disabled>
                <button id="send-user-message" class="btn btn-primary" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
        
        <div id="chat-connection-status" class="text-center p-2 bg-warning text-dark" style="display: none;">
            <small><i class="fas fa-spinner fa-spin me-1"></i>Bağlanıyor...</small>
        </div>
    </div>
</div>

<style>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.chat-toggle {
    width: 60px;
    height: 60px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,123,255,0.3);
    transition: all 0.3s ease;
    position: relative;
}

.chat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(0,123,255,0.4);
}

.chat-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chat-window.active {
    display: flex;
}

.chat-header {
    background: #007bff;
    color: white;
    padding: 15px;
    font-weight: 600;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    background: #f8f9fa;
}

.chat-input-area {
    padding: 15px;
    border-top: 1px solid #dee2e6;
    background: white;
}

.message-item {
    margin-bottom: 15px;
}

.message-bubble {
    max-width: 80%;
    padding: 10px 15px;
    border-radius: 15px;
    word-wrap: break-word;
}

.message-user {
    text-align: right;
}

.message-user .message-bubble {
    background: #007bff;
    color: white;
    margin-left: auto;
}

.message-admin {
    text-align: left;
}

.message-admin .message-bubble {
    background: white;
    color: #333;
    border: 1px solid #dee2e6;
}

.message-time {
    font-size: 0.7rem;
    opacity: 0.7;
    margin-top: 5px;
}

@media (max-width: 768px) {
    .chat-window {
        width: 300px;
    }
}
</style>

<script>
class ChatWidget {
    constructor() {
        this.websocket = null;
        this.isConnected = false;
        this.unreadCount = 0;
        this.userInfo = {
            kullanici_id: <?php echo $_SESSION['kullanici_id']; ?>,
            kullanici_ad: '<?php echo $_SESSION['kullanici_ad']; ?>',
            kullanici_email: '<?php echo $_SESSION['kullanici_email']; ?>'
        };
        
        this.initEventListeners();
        this.connect();
    }
    
    initEventListeners() {
        document.getElementById('chat-toggle').addEventListener('click', () => {
            this.toggleChatWindow();
        });
        
        document.getElementById('close-chat').addEventListener('click', () => {
            this.toggleChatWindow();
        });
        
        document.getElementById('send-user-message').addEventListener('click', () => {
            this.sendMessage();
        });
        
        document.getElementById('user-message-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
    }
    
    connect() {
        try {
            this.showConnectionStatus('Bağlanıyor...');
            this.websocket = new WebSocket('ws://localhost:8765');
            
            this.websocket.onopen = () => {
                this.websocket.send(JSON.stringify({
                    type: 'user',
                    kullanici_id: this.userInfo.kullanici_id,
                    kullanici_ad: this.userInfo.kullanici_ad,
                    kullanici_email: this.userInfo.kullanici_email
                }));
                
                this.isConnected = true;
                this.hideConnectionStatus();
                this.enableInput();
            };
            
            this.websocket.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            };
            
            this.websocket.onclose = () => {
                this.isConnected = false;
                this.disableInput();
                this.showConnectionStatus('Bağlantı kesildi. Yeniden bağlanılıyor...');
                setTimeout(() => this.connect(), 3000);
            };
            
            this.websocket.onerror = (error) => {
                console.error('Chat connection error:', error);
                this.showConnectionStatus('Bağlantı hatası!');
            };
            
        } catch (error) {
            console.error('WebSocket creation error:', error);
            this.showConnectionStatus('Bağlantı kurulamadı!');
        }
    }
    
    handleMessage(data) {
        if (data.type === 'message') {
            this.addMessage(data.sender_type, data.sender_name, data.message, data.timestamp);
            
            if (!this.isChatWindowOpen()) {
                this.incrementUnreadCount();
            }
        }
    }
    
    sendMessage() {
        const input = document.getElementById('user-message-input');
        const message = input.value.trim();
        
        if (message && this.websocket && this.websocket.readyState === WebSocket.OPEN) {
            this.websocket.send(JSON.stringify({
                type: 'chat_message',
                message: message
            }));
            
            this.addMessage('user', 'Ben', message, new Date().toISOString());
            input.value = '';
        }
    }
    
    addMessage(senderType, senderName, message, timestamp) {
        const messagesContainer = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        
        const time = new Date(timestamp).toLocaleTimeString('tr-TR', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const isUser = senderType === 'user';
        const alignClass = isUser ? 'message-user' : 'message-admin';
        
        messageDiv.className = `message-item ${alignClass}`;
        messageDiv.innerHTML = `
            <div class="message-bubble">
                ${!isUser ? `<div class="fw-bold mb-1">${senderName}</div>` : ''}
                <div>${message}</div>
            </div>
            <div class="message-time">${time}</div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }
    
    toggleChatWindow() {
        const chatWindow = document.getElementById('chat-window');
        chatWindow.classList.toggle('active');
        
        if (this.isChatWindowOpen()) {
            this.resetUnreadCount();
        }
    }
    
    isChatWindowOpen() {
        return document.getElementById('chat-window').classList.contains('active');
    }
    
    incrementUnreadCount() {
        this.unreadCount++;
        this.updateUnreadBadge();
    }
    
    resetUnreadCount() {
        this.unreadCount = 0;
        this.updateUnreadBadge();
    }
    
    updateUnreadBadge() {
        const badge = document.getElementById('chat-badge');
        if (this.unreadCount > 0) {
            badge.textContent = this.unreadCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
    
    enableInput() {
        document.getElementById('user-message-input').disabled = false;
        document.getElementById('send-user-message').disabled = false;
    }
    
    disableInput() {
        document.getElementById('user-message-input').disabled = true;
        document.getElementById('send-user-message').disabled = true;
    }
    
    showConnectionStatus(message) {
        const status = document.getElementById('chat-connection-status');
        status.innerHTML = `<small><i class="fas fa-spinner fa-spin me-1"></i>${message}</small>`;
        status.style.display = 'block';
    }
    
    hideConnectionStatus() {
        document.getElementById('chat-connection-status').style.display = 'none';
    }
    
    scrollToBottom() {
        const messagesContainer = document.getElementById('chat-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// Chat widget'ını başlat
document.addEventListener('DOMContentLoaded', function() {
    new ChatWidget();
});
</script>
<?php endif; ?>
