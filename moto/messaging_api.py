#!/usr/bin/env python3
"""
MotorUnal Mesajlaşma API'si
WebSocket tabanlı anlık mesajlaşma sistemi
"""

import asyncio
import websockets
import json
import mysql.connector
from datetime import datetime
import logging

# Logging ayarları
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Veritabanı bağlantı ayarları
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'moto',
    'charset': 'utf8mb4'
}

# Aktif bağlantıları sakla
connected_users = {}

class MessagingServer:
    def __init__(self):
        self.connections = {}
        
    def get_db_connection(self):
        """Veritabanı bağlantısı oluştur"""
        try:
            connection = mysql.connector.connect(**DB_CONFIG)
            return connection
        except mysql.connector.Error as e:
            logger.error(f"Veritabanı bağlantı hatası: {e}")
            return None
    
    async def register_user(self, websocket, user_data):
        """Kullanıcıyı kaydet"""
        user_id = user_data.get('kullanici_id')
        if user_id:
            self.connections[user_id] = {
                'websocket': websocket,
                'user_data': user_data
            }
            logger.info(f"Kullanıcı bağlandı: {user_data.get('kullanici_ad')} (ID: {user_id})")
            
            # Kullanıcının okunmamış mesaj sayısını gönder
            unread_count = await self.get_unread_message_count(user_id)
            await self.send_to_user(user_id, {
                'type': 'unread_count',
                'count': unread_count
            })
    
    async def unregister_user(self, websocket):
        """Kullanıcı bağlantısını kaldır"""
        user_id = None
        for uid, conn_data in list(self.connections.items()):
            if conn_data['websocket'] == websocket:
                user_id = uid
                break
        
        if user_id:
            del self.connections[user_id]
            logger.info(f"Kullanıcı bağlantısı kesildi: {user_id}")
    
    async def send_to_user(self, user_id, message):
        """Belirli bir kullanıcıya mesaj gönder"""
        if user_id in self.connections:
            try:
                await self.connections[user_id]['websocket'].send(json.dumps(message))
                return True
            except websockets.exceptions.ConnectionClosed:
                if user_id in self.connections:
                    del self.connections[user_id]
                return False
        return False
    
    async def get_unread_message_count(self, user_id):
        """Kullanıcının okunmamış mesaj sayısını getir"""
        db = self.get_db_connection()
        if not db:
            return 0
            
        try:
            cursor = db.cursor()
            query = """
                SELECT COUNT(*) FROM mesajlar m
                JOIN konusmalar k ON m.konusma_id = k.id
                WHERE (k.alici_id = %s OR k.gonderen_id = %s)
                AND m.gonderen_id != %s
                AND m.okundu = FALSE
            """
            cursor.execute(query, (user_id, user_id, user_id))
            result = cursor.fetchone()
            return result[0] if result else 0
        except Exception as e:
            logger.error(f"Okunmamış mesaj sayısı alınırken hata: {e}")
            return 0
        finally:
            db.close()
    
    async def create_conversation(self, gonderen_id, alici_id, ilan_id, baslik):
        """Yeni konuşma oluştur veya mevcut olanı getir"""
        db = self.get_db_connection()
        if not db:
            return None
            
        try:
            cursor = db.cursor()
            check_query = """
                SELECT id FROM konusmalar 
                WHERE ((gonderen_id = %s AND alici_id = %s) OR (gonderen_id = %s AND alici_id = %s))
                AND ilan_id = %s
            """
            cursor.execute(check_query, (gonderen_id, alici_id, alici_id, gonderen_id, ilan_id))
            existing = cursor.fetchone()
            
            if existing:
                return existing[0]
            
            insert_query = """
                INSERT INTO konusmalar (gonderen_id, alici_id, ilan_id, baslik)
                VALUES (%s, %s, %s, %s)
            """
            cursor.execute(insert_query, (gonderen_id, alici_id, ilan_id, baslik))
            db.commit()
            
            return cursor.lastrowid
            
        except Exception as e:
            logger.error(f"Konuşma oluşturulurken hata: {e}")
            return None
        finally:
            db.close()
    
    async def save_message(self, konusma_id, gonderen_id, mesaj):
        """Mesajı veritabanına kaydet"""
        db = self.get_db_connection()
        if not db:
            return None
            
        try:
            cursor = db.cursor()
            insert_query = """
                INSERT INTO mesajlar (konusma_id, gonderen_id, mesaj)
                VALUES (%s, %s, %s)
            """
            cursor.execute(insert_query, (konusma_id, gonderen_id, mesaj))
            
            update_query = """
                UPDATE konusmalar SET son_mesaj_tarihi = NOW()
                WHERE id = %s
            """
            cursor.execute(update_query, (konusma_id,))
            
            db.commit()
            return cursor.lastrowid
            
        except Exception as e:
            logger.error(f"Mesaj kaydedilirken hata: {e}")
            return None
        finally:
            db.close()
    
    async def get_conversation_participants(self, konusma_id):
        """Konuşma katılımcılarını getir"""
        db = self.get_db_connection()
        if not db:
            return None, None
            
        try:
            cursor = db.cursor()
            query = "SELECT gonderen_id, alici_id FROM konusmalar WHERE id = %s"
            cursor.execute(query, (konusma_id,))
            result = cursor.fetchone()
            
            if result:
                return result[0], result[1]
            return None, None
            
        except Exception as e:
            logger.error(f"Konuşma katılımcıları alınırken hata: {e}")
            return None, None
        finally:
            db.close()
    
    async def handle_message(self, websocket, data):
        """Gelen mesajları işle"""
        message_type = data.get('type')
        
        if message_type == 'register':
            await self.register_user(websocket, data)
            
        elif message_type == 'start_conversation':
            gonderen_id = data.get('gonderen_id')
            alici_id = data.get('alici_id')
            ilan_id = data.get('ilan_id')
            baslik = data.get('baslik', 'İlan Hakkında')
            
            konusma_id = await self.create_conversation(gonderen_id, alici_id, ilan_id, baslik)
            
            response = {
                'type': 'conversation_started',
                'success': bool(konusma_id)
            }
            if konusma_id:
                response['konusma_id'] = konusma_id
            else:
                response['error'] = 'Konuşma oluşturulamadı'
            
            await websocket.send(json.dumps(response))
            
        elif message_type == 'send_message':
            konusma_id = data.get('konusma_id')
            gonderen_id = data.get('gonderen_id')
            mesaj = data.get('mesaj')
            
            if konusma_id and gonderen_id and mesaj:
                mesaj_id = await self.save_message(konusma_id, gonderen_id, mesaj)
                
                if mesaj_id:
                    gonderen, alici = await self.get_conversation_participants(konusma_id)
                    
                    message_data = {
                        'type': 'new_message',
                        'mesaj_id': mesaj_id,
                        'konusma_id': konusma_id,
                        'gonderen_id': gonderen_id,
                        'mesaj': mesaj,
                        'tarih': datetime.now().isoformat()
                    }
                    
                    await self.send_to_user(gonderen_id, {**message_data, 'status': 'sent'})
                    
                    if gonderen != alici:
                        target_user = alici if gonderen == gonderen_id else gonderen
                        await self.send_to_user(target_user, message_data)
                        
                        unread_count = await self.get_unread_message_count(target_user)
                        await self.send_to_user(target_user, {
                            'type': 'unread_count',
                            'count': unread_count
                        })
    
    async def handle_client(self, websocket):
        """WebSocket bağlantılarını yönet (websockets 12.x uyumlu)"""
        try:
            async for message in websocket:
                try:
                    data = json.loads(message)
                    await self.handle_message(websocket, data)
                except json.JSONDecodeError:
                    logger.error("Geçersiz JSON mesajı alındı")
                except Exception as e:
                    logger.error(f"Mesaj işlenirken hata: {e}")
        except websockets.exceptions.ConnectionClosed:
            pass
        finally:
            await self.unregister_user(websocket)

# Server'ı başlat
async def main():
    server = MessagingServer()
    logger.info("Mesajlaşma sunucusu başlatılıyor...")
    logger.info("Port: 8765")
    
    async with websockets.serve(server.handle_client, "localhost", 8765):
        logger.info("Mesajlaşma sunucusu başlatıldı!")
        await asyncio.Future()  # Run forever

if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        logger.info("Sunucu kapatılıyor...")
