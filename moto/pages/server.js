const WebSocket = require('ws');
const wss = new WebSocket.Server({ port: 8765 });

wss.on('connection', ws => {
    console.log('Yeni kullanıcı bağlandı');

    ws.on('message', message => {
        console.log(`Mesaj: ${message}`);

        // Gelen mesajı tüm bağlı kullanıcılara gönder
        wss.clients.forEach(client => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(message);
            }
        });
    });
});
