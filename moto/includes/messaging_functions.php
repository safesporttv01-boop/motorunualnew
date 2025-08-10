<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Mesajlaşma sistemi için yardımcı fonksiyonlar
 */

function getKonusmalar($kullanici_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT k.*, 
                   CASE 
                       WHEN k.gonderen_id = ? THEN alici.ad 
                       ELSE gonderen.ad 
                   END as karsi_taraf_ad,
                   CASE 
                       WHEN k.gonderen_id = ? THEN alici.soyad 
                       ELSE gonderen.soyad 
                   END as karsi_taraf_soyad,
                   CASE 
                       WHEN k.gonderen_id = ? THEN k.alici_id 
                       ELSE k.gonderen_id 
                   END as karsi_taraf_id,
                   i.ilan_ismi,
                   i.fiyat,
                   (SELECT fotograf_yolu FROM ilan_fotograflari WHERE ilan_id = i.id ORDER BY sira_no LIMIT 1) as ilan_fotograf,
                   (SELECT mesaj FROM mesajlar WHERE konusma_id = k.id ORDER BY gonderim_tarihi DESC LIMIT 1) as son_mesaj,
                   (SELECT COUNT(*) FROM mesajlar WHERE konusma_id = k.id AND gonderen_id != ? AND okundu = FALSE) as okunmamis_sayisi
            FROM konusmalar k
            LEFT JOIN kullanicilar gonderen ON k.gonderen_id = gonderen.id
            LEFT JOIN kullanicilar alici ON k.alici_id = alici.id
            LEFT JOIN ilanlar i ON k.ilan_id = i.id
            WHERE (k.gonderen_id = ? OR k.alici_id = ?) 
            AND ((k.gonderen_id = ? AND k.gonderen_aktif = TRUE) OR (k.alici_id = ? AND k.alici_aktif = TRUE))
            ORDER BY k.son_mesaj_tarihi DESC
        ");
        
        $stmt->execute([$kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id, $kullanici_id]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Konuşmalar alınırken hata: " . $e->getMessage());
        return [];
    }
}

function getKonusmaMesajlari($konusma_id, $kullanici_id) {
    global $pdo;
    
    try {
        // Önce kullanıcının bu konuşmaya erişim hakkı olup olmadığını kontrol et
        $stmt = $pdo->prepare("
            SELECT id FROM konusmalar 
            WHERE id = ? AND (gonderen_id = ? OR alici_id = ?)
        ");
        $stmt->execute([$konusma_id, $kullanici_id, $kullanici_id]);
        
        if (!$stmt->fetch()) {
            return [];
        }
        
        // Mesajları getir
        $stmt = $pdo->prepare("
            SELECT m.*, k.ad, k.soyad
            FROM mesajlar m
            LEFT JOIN kullanicilar k ON m.gonderen_id = k.id
            WHERE m.konusma_id = ?
            ORDER BY m.gonderim_tarihi ASC
        ");
        $stmt->execute([$konusma_id]);
        
        $mesajlar = $stmt->fetchAll();
        
        // Okunmamış mesajları okundu olarak işaretle
        $stmt = $pdo->prepare("
            UPDATE mesajlar SET okundu = TRUE 
            WHERE konusma_id = ? AND gonderen_id != ? AND okundu = FALSE
        ");
        $stmt->execute([$konusma_id, $kullanici_id]);
        
        return $mesajlar;
        
    } catch (PDOException $e) {
        error_log("Konuşma mesajları alınırken hata: " . $e->getMessage());
        return [];
    }
}

function getKonusmaDetay($konusma_id, $kullanici_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT k.*, 
                   CASE 
                       WHEN k.gonderen_id = ? THEN alici.ad 
                       ELSE gonderen.ad 
                   END as karsi_taraf_ad,
                   CASE 
                       WHEN k.gonderen_id = ? THEN alici.soyad 
                       ELSE gonderen.soyad 
                   END as karsi_taraf_soyad,
                   CASE 
                       WHEN k.gonderen_id = ? THEN k.alici_id 
                       ELSE k.gonderen_id 
                   END as karsi_taraf_id,
                   i.ilan_ismi,
                   i.fiyat,
                   (SELECT fotograf_yolu FROM ilan_fotograflari WHERE ilan_id = i.id ORDER BY sira_no LIMIT 1) as ilan_fotograf
            FROM konusmalar k
            LEFT JOIN kullanicilar gonderen ON k.gonderen_id = gonderen.id
            LEFT JOIN kullanicilar alici ON k.alici_id = alici.id
            LEFT JOIN ilanlar i ON k.ilan_id = i.id
            WHERE k.id = ? AND (k.gonderen_id = ? OR k.alici_id = ?)
        ");
        
        $stmt->execute([$kullanici_id, $kullanici_id, $kullanici_id, $konusma_id, $kullanici_id, $kullanici_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Konuşma detayı alınırken hata: " . $e->getMessage());
        return null;
    }
}

function getToplumOkunmamisMesajSayisi($kullanici_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM mesajlar m
            JOIN konusmalar k ON m.konusma_id = k.id
            WHERE (k.alici_id = ? OR k.gonderen_id = ?)
            AND m.gonderen_id != ?
            AND m.okundu = FALSE
        ");
        $stmt->execute([$kullanici_id, $kullanici_id, $kullanici_id]);
        
        $result = $stmt->fetchColumn();
        return $result ? $result : 0;
        
    } catch (PDOException $e) {
        error_log("Okunmamış mesaj sayısı alınırken hata: " . $e->getMessage());
        return 0;
    }
}

function konusmaSil($konusma_id, $kullanici_id) {
    global $pdo;
    
    try {
        // Kullanıcının bu konuşmaya erişim hakkı olup olmadığını kontrol et
        $stmt = $pdo->prepare("
            SELECT gonderen_id, alici_id FROM konusmalar 
            WHERE id = ? AND (gonderen_id = ? OR alici_id = ?)
        ");
        $stmt->execute([$konusma_id, $kullanici_id, $kullanici_id]);
        $konusma = $stmt->fetch();
        
        if (!$konusma) {
            return false;
        }
        
        // Kullanıcının rolüne göre aktiflik durumunu güncelle
        if ($konusma['gonderen_id'] == $kullanici_id) {
            $stmt = $pdo->prepare("UPDATE konusmalar SET gonderen_aktif = FALSE WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE konusmalar SET alici_aktif = FALSE WHERE id = ?");
        }
        
        $stmt->execute([$konusma_id]);
        
        // Eğer her iki taraf da konuşmayı silmişse, konuşmayı tamamen sil
        $stmt = $pdo->prepare("
            SELECT gonderen_aktif, alici_aktif FROM konusmalar WHERE id = ?
        ");
        $stmt->execute([$konusma_id]);
        $durum = $stmt->fetch();
        
        if (!$durum['gonderen_aktif'] && !$durum['alici_aktif']) {
            $stmt = $pdo->prepare("DELETE FROM konusmalar WHERE id = ?");
            $stmt->execute([$konusma_id]);
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Konuşma silinirken hata: " . $e->getMessage());
        return false;
    }
}
?>