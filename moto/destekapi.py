
import asyncio
import websockets
import json
from datetime import datetime
import sqlite3
import threading
import time

# Veritabanı bağlantısı
def init_db():
    conn = sqlite3.connect('chat.db', check_same_thread=False)
    cursor = conn.cursor()

    # Chat odaları tablosu
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS chat_rooms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kullanici_id INTEGER,
            kullanici_ad TEXT,
            kullanici_email TEXT,
            aktif INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')

    # Mesajlar tablosu
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS chat_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_id INTEGER,
            sender_type TEXT, -- 'user' veya 'admin'
            sender_name TEXT,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (room_id) REFERENCES chat_rooms (id)
        )
    ''')

    conn.commit()
    conn.close()

# Bağlı kullanıcılar ve adminler
connected_users = {}
connected_admins = {}
db_lock = threading.Lock()

def get_db_connection():
    return sqlite3.connect('chat.db', check_same_thread=False)

def create_chat_room(kullanici_id, kullanici_ad, kullanici_email):
    with db_lock:
        conn = get_db_connection()
        cursor = conn.cursor()

        # Mevcut oda var mı kontrol et
        cursor.execute('SELECT id FROM chat_rooms WHERE kullanici_id = ? AND aktif = 1', (kullanici_id,))
        existing_room = cursor.fetchone()

        if existing_room:
            room_id = existing_room[0]
        else:
            cursor.execute('''
                INSERT INTO chat_rooms (kullanici_id, kullanici_ad, kullanici_email)
                VALUES (?, ?, ?)
            ''', (kullanici_id, kullanici_ad, kullanici_email))
            room_id = cursor.lastrowid

        conn.commit()
        conn.close()
        return room_id

def save_message(room_id, sender_type, sender_name, message):
    with db_lock:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('''
            INSERT INTO chat_messages (room_id, sender_type, sender_name, message)
            VALUES (?, ?, ?, ?)
        ''', (room_id, sender_type, sender_name, message))
        conn.commit()
        conn.close()

def get_room_messages(room_id, limit=50):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute('''
        SELECT sender_type, sender_name, message, created_at
        FROM chat_messages
        WHERE room_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ''', (room_id, limit))
    messages = cursor.fetchall()
    conn.close()
    return list(reversed(messages))

def get_active_rooms():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute('''
        SELECT cr.id, cr.kullanici_id, cr.kullanici_ad, cr.kullanici_email,
               (SELECT COUNT(*) FROM chat_messages cm WHERE cm.room_id = cr.id) as message_count,
               (SELECT cm.created_at FROM chat_messages cm WHERE cm.room_id = cr.id ORDER BY cm.created_at DESC LIMIT 1) as last_message_time
        FROM chat_rooms cr
        WHERE cr.aktif = 1
        ORDER BY last_message_time DESC
    ''')
    rooms = cursor.fetchall()
    conn.close()
    return rooms

async def handle_user_connection(websocket):
    try:
        # İlk mesajda kullanıcı bilgilerini al
        initial_data = await websocket.recv()
        user_info = json.loads(initial_data)

        kullanici_id = user_info.get('kullanici_id')
        kullanici_ad = user_info.get('kullanici_ad')
        kullanici_email = user_info.get('kullanici_email')
        connection_type = user_info.get('type', 'user')

        if connection_type == 'admin':
            # Admin bağlantısı
            admin_id = f"admin_{kullanici_id}"
            connected_admins[admin_id] = {
                'websocket': websocket,
                'admin_id': kullanici_id,
                'admin_name': kullanici_ad
            }

            # Admin'e aktif odaları gönder
            rooms = get_active_rooms()
            await websocket.send(json.dumps({
                'type': 'rooms_list',
                'rooms': [{
                    'room_id': room[0],
                    'kullanici_id': room[1],
                    'kullanici_ad': room[2],
                    'kullanici_email': room[3],
                    'message_count': room[4],
                    'last_message_time': room[5]
                } for room in rooms]
            }))

        else:
            # Kullanıcı bağlantısı
            room_id = create_chat_room(kullanici_id, kullanici_ad, kullanici_email)

            user_key = f"user_{kullanici_id}"
            connected_users[user_key] = {
                'websocket': websocket,
                'room_id': room_id,
                'kullanici_id': kullanici_id,
                'kullanici_ad': kullanici_ad
            }

            # Önceki mesajları gönder
            messages = get_room_messages(room_id)
            for msg in messages:
                await websocket.send(json.dumps({
                    'type': 'message',
                    'sender_type': msg[0],
                    'sender_name': msg[1],
                    'message': msg[2],
                    'timestamp': msg[3]
                }))

        # Mesaj dinleme
        async for message in websocket:
            data = json.loads(message)
            message_type = data.get('type')

            if message_type == 'chat_message':
                if connection_type == 'admin':
                    # Admin mesajı
                    room_id = data.get('room_id')
                    message_text = data.get('message')

                    # Mesajı kaydet
                    save_message(room_id, 'admin', kullanici_ad, message_text)

                    # Kullanıcıya gönder
                    for user_key, user_data in connected_users.items():
                        if user_data['room_id'] == room_id:
                            await user_data['websocket'].send(json.dumps({
                                'type': 'message',
                                'sender_type': 'admin',
                                'sender_name': 'Destek Ekibi',
                                'message': message_text,
                                'timestamp': datetime.now().isoformat()
                            }))

                    # Diğer adminlere bildir
                    for admin_key, admin_data in connected_admins.items():
                        if admin_data['admin_id'] != kullanici_id:
                            await admin_data['websocket'].send(json.dumps({
                                'type': 'admin_message_update',
                                'room_id': room_id,
                                'sender_name': kullanici_ad,
                                'message': message_text,
                                'timestamp': datetime.now().isoformat()
                            }))

                    # Tüm adminlere güncel oda listesini gönder
                    rooms = get_active_rooms()
                    for admin_key, admin_data in connected_admins.items():
                        await admin_data['websocket'].send(json.dumps({
                            'type': 'rooms_list_update',
                            'rooms': [{
                                'room_id': room[0],
                                'kullanici_id': room[1],
                                'kullanici_ad': room[2],
                                'kullanici_email': room[3],
                                'message_count': room[4],
                                'last_message_time': room[5]
                            } for room in rooms]
                        }))

                else:
                    # Kullanıcı mesajı
                    user_data = connected_users[f"user_{kullanici_id}"]
                    room_id = user_data['room_id']
                    message_text = data.get('message')

                    # Mesajı kaydet
                    save_message(room_id, 'user', kullanici_ad, message_text)

                    # Tüm adminlere gönder
                    for admin_key, admin_data in connected_admins.items():
                        await admin_data['websocket'].send(json.dumps({
                            'type': 'new_message',
                            'room_id': room_id,
                            'kullanici_id': kullanici_id,
                            'kullanici_ad': kullanici_ad,
                            'message': message_text,
                            'timestamp': datetime.now().isoformat()
                        }))

                    # Adminlere güncel oda listesini gönder
                    rooms = get_active_rooms()
                    for admin_key, admin_data in connected_admins.items():
                        await admin_data['websocket'].send(json.dumps({
                            'type': 'rooms_list_update',
                            'rooms': [{
                                'room_id': room[0],
                                'kullanici_id': room[1],
                                'kullanici_ad': room[2],
                                'kullanici_email': room[3],
                                'message_count': room[4],
                                'last_message_time': room[5]
                            } for room in rooms]
                        }))

            elif message_type == 'get_room_messages' and connection_type == 'admin':
                room_id = data.get('room_id')
                messages = get_room_messages(room_id)
                await websocket.send(json.dumps({
                    'type': 'room_messages',
                    'room_id': room_id,
                    'messages': [{
                        'sender_type': msg[0],
                        'sender_name': msg[1],
                        'message': msg[2],
                        'timestamp': msg[3]
                    } for msg in messages]
                }))

    except websockets.exceptions.ConnectionClosed:
        pass
    except Exception as e:
        print(f"Error: {e}")
    finally:
        # Bağlantı temizliği
        if connection_type == 'admin':
            admin_id = f"admin_{kullanici_id}"
            if admin_id in connected_admins:
                del connected_admins[admin_id]
        else:
            user_key = f"user_{kullanici_id}"
            if user_key in connected_users:
                del connected_users[user_key]

# Ana server
async def main():
    init_db()
    print("Chat API başlatıldı - Port: 8765")
    async with websockets.serve(handle_user_connection, "0.0.0.0", 8765):
        await asyncio.Future()  # Sonsuza kadar çalış

if __name__ == "__main__":
    asyncio.run(main())
