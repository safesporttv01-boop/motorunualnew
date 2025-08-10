<?php
require_once __DIR__ . '/../config/database.php';

function temizle($veri) {
    return htmlspecialchars(strip_tags(trim($veri)));
}

function sifreHashle($sifre) {
    return password_hash($sifre, PASSWORD_DEFAULT);
}

function sifreDogrula($sifre, $hash) {
    return password_verify($sifre, $hash);
}

function girisKontrol() {
    return isset($_SESSION['kullanici_id']);
}

function adminKontrol() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

function getMarkalar() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM markalar WHERE aktif = TRUE ORDER BY marka_adi");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getModeller($marka_id = null) {
    global $pdo;
    if ($marka_id) {
        $stmt = $pdo->prepare("SELECT * FROM modeller WHERE marka_id = ? AND aktif = TRUE ORDER BY model_adi");
        $stmt->execute([$marka_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM modeller WHERE aktif = TRUE ORDER BY model_adi");
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function kullaniciKaydet($ad, $soyad, $email, $sifre, $telefon = null) {
    global $pdo;
    try {
        $hashedSifre = sifreHashle($sifre);
        $stmt = $pdo->prepare("INSERT INTO kullanicilar (ad, soyad, email, sifre, telefon) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$ad, $soyad, $email, $hashedSifre, $telefon]);
    } catch (PDOException $e) {
        return false;
    }
}

function kullaniciGiris($email, $sifre) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE email = ? AND aktif = TRUE");
        $stmt->execute([$email]);
        $kullanici = $stmt->fetch();

        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            // Önceki session verilerini temizle
            session_unset();

            // Yeni session verilerini set et
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['kullanici_ad'] = $kullanici['ad'];
            $_SESSION['kullanici_soyad'] = $kullanici['soyad'];
            $_SESSION['kullanici_email'] = $kullanici['email'];
            $_SESSION['is_admin'] = $kullanici['is_admin'];
            $_SESSION['ilan_hakki'] = $kullanici['ilan_hakki'];
            $_SESSION['giris_zamani'] = time();

            // Session'ı yeniden başlat
            session_regenerate_id(true);

            return true;
        }

        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function getKullanici($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function ilanNoOlustur() {
    return 'IL' . date('Y') . rand(100000, 999999);
}

function ilanKaydet($data) {
    global $pdo;
    try {
        $ilan_no = ilanNoOlustur();

        $stmt = $pdo->prepare("
            INSERT INTO ilanlar (kullanici_id, ilan_ismi, marka_id, model_id, konum, fiyat, 
                               ilan_no, tipi, km, motor_hacmi, motor_gucu, zamanlama_tipi, 
                               silindir_sayisi, sogutma, renk, plaka_uyruk, takas, 
                               iletisim_bilgi, aciklama, telefon) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $_SESSION['kullanici_id'],
            $data['ilan_ismi'],
            $data['marka_id'],
            $data['model_id'],
            $data['konum'],
            $data['fiyat'],
            $ilan_no,
            $data['tipi'],
            $data['km'],
            $data['motor_hacmi'],
            $data['motor_gucu'],
            $data['zamanlama_tipi'],
            $data['silindir_sayisi'],
            $data['sogutma'],
            $data['renk'],
            $data['plaka_uyruk'],
            isset($data['takas']) ? 1 : 0,
            $data['iletisim_bilgi'],
            $data['aciklama'],
            $data['telefon']
        ]);

        if ($result) {
            $ilan_id = $pdo->lastInsertId();

            // Fotoğraf yükleme
            if (isset($_FILES['fotograflar']) && !empty($_FILES['fotograflar']['name'][0])) {
                fotografYukle($ilan_id, $_FILES['fotograflar']);
            }

            // İlan hakkını azalt (minimum 1 kalacak şekilde)
            $stmt = $pdo->prepare("UPDATE kullanicilar SET ilan_hakki = GREATEST(ilan_hakki - 1, 0) WHERE id = ?");
            $stmt->execute([$_SESSION['kullanici_id']]);

            return $ilan_id;
        }

        return false;
    } catch (PDOException $e) {
        return false;
    }
}

function fotografYukle($ilan_id, $files) {
    global $pdo;

    // Upload dizinini oluştur
    $upload_dir = __DIR__ . '/../assets/images/ilanlar/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file_tmp = $files['tmp_name'][$i];
            $file_type = $files['type'][$i];
            $file_size = $files['size'][$i];

            if (!in_array($file_type, $allowed_types)) {
                continue;
            }


            if ($file_size > $max_size) {
                continue;
            }


            $file_extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $file_name = $ilan_id . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;


            if (move_uploaded_file($file_tmp, $file_path)) {

                $web_path = 'assets/images/ilanlar/' . $file_name;
                $stmt = $pdo->prepare("INSERT INTO ilan_fotograflari (ilan_id, fotograf_yolu, sira_no) VALUES (?, ?, ?)");
                $stmt->execute([$ilan_id, $web_path, $i + 1]);
            }
        }
    }
}

function getIlanlar($limit = 20, $marka_id = null, $model_id = null) {
    global $pdo;

    $where = "WHERE i.aktif = TRUE AND i.onaylanmis = TRUE";
    $params = [];

    if ($marka_id) {
        $where .= " AND i.marka_id = ?";
        $params[] = $marka_id;
    }

    if ($model_id) {
        $where .= " AND i.model_id = ?";
        $params[] = $model_id;
    }

    $sql = "
        SELECT i.*, k.ad, k.soyad, m.marka_adi, mo.model_adi,
               (SELECT fotograf_yolu FROM ilan_fotograflari WHERE ilan_id = i.id ORDER BY sira_no LIMIT 1) as ilk_fotograf
        FROM ilanlar i
        LEFT JOIN kullanicilar k ON i.kullanici_id = k.id
        LEFT JOIN markalar m ON i.marka_id = m.id
        LEFT JOIN modeller mo ON i.model_id = mo.id
        $where
        ORDER BY i.ilan_tarihi DESC
        LIMIT ?
    ";

    $params[] = $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getIlan($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT i.*, k.ad, k.soyad, k.telefon as kullanici_telefon, m.marka_adi, mo.model_adi
        FROM ilanlar i
        LEFT JOIN kullanicilar k ON i.kullanici_id = k.id
        LEFT JOIN markalar m ON i.marka_id = m.id
        LEFT JOIN modeller mo ON i.model_id = mo.id
        WHERE i.id = ? AND i.aktif = TRUE
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getIlanFotograflari($ilan_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM ilan_fotograflari WHERE ilan_id = ? ORDER BY sira_no");
    $stmt->execute([$ilan_id]);
    return $stmt->fetchAll();
}

function profilGuncelle($kullanici_id, $ad, $soyad, $telefon) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE kullanicilar SET ad = ?, soyad = ?, telefon = ? WHERE id = ?");
        return $stmt->execute([$ad, $soyad, $telefon, $kullanici_id]);
    } catch (PDOException $e) {
        return false;
    }
}

function getKullaniciIlanlari($kullanici_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT i.*, m.marka_adi, mo.model_adi,
               (SELECT fotograf_yolu FROM ilan_fotograflari WHERE ilan_id = i.id ORDER BY sira_no LIMIT 1) as ilk_fotograf
        FROM ilanlar i
        LEFT JOIN markalar m ON i.marka_id = m.id
        LEFT JOIN modeller mo ON i.model_id = mo.id
        WHERE i.kullanici_id = ?
        ORDER BY i.ilan_tarihi DESC
    ");
    $stmt->execute([$kullanici_id]);
    return $stmt->fetchAll();
}

function ilanHakkiEkle($kullanici_id, $ilan_sayisi) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE kullanicilar SET ilan_hakki = ilan_hakki + ? WHERE id = ?");
        return $stmt->execute([$ilan_sayisi, $kullanici_id]);
    } catch (PDOException $e) {
        return false;
    }
}

function searchUsers($query) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE ad LIKE ? OR soyad LIKE ? OR email LIKE ?");
    $searchTerm = "%" . $query . "%"; // Wildcard search
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}
?>