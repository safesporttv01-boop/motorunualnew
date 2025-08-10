
-- Veritabanı oluştur
CREATE DATABASE IF NOT EXISTS moto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moto;

-- Kullanıcılar tablosu
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    soyad VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    telefon VARCHAR(20),
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ilan_hakki INT DEFAULT 3,
    is_admin BOOLEAN DEFAULT FALSE,
    aktif BOOLEAN DEFAULT TRUE
);

-- Motor markaları tablosu
CREATE TABLE markalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marka_adi VARCHAR(100) NOT NULL,
    aktif BOOLEAN DEFAULT TRUE
);

-- Motor modelleri tablosu
CREATE TABLE modeller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marka_id INT NOT NULL,
    model_adi VARCHAR(100) NOT NULL,
    aktif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (marka_id) REFERENCES markalar(id)
);

-- İlanlar tablosu
CREATE TABLE ilanlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    ilan_ismi VARCHAR(255) NOT NULL,
    marka_id INT NOT NULL,
    model_id INT NOT NULL,
    konum VARCHAR(255),
    fiyat DECIMAL(12,2),
    ilan_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ilan_no VARCHAR(20) UNIQUE,
    tipi VARCHAR(100),
    km INT,
    motor_hacmi INT,
    motor_gucu INT,
    zamanlama_tipi VARCHAR(100),
    silindir_sayisi VARCHAR(100),
    sogutma VARCHAR(100),
    renk VARCHAR(50),
    plaka_uyruk VARCHAR(100),
    takas BOOLEAN DEFAULT FALSE,
    iletisim_bilgi TEXT,
    aciklama TEXT,
    konum_detay TEXT,
    telefon VARCHAR(20),
    aktif BOOLEAN DEFAULT TRUE,
    onaylanmis BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id),
    FOREIGN KEY (marka_id) REFERENCES markalar(id),
    FOREIGN KEY (model_id) REFERENCES modeller(id)
);

-- İlan fotoğrafları tablosu
CREATE TABLE ilan_fotograflari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ilan_id INT NOT NULL,
    fotograf_yolu VARCHAR(500),
    sira_no INT DEFAULT 1,
    upload_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ilan_id) REFERENCES ilanlar(id) ON DELETE CASCADE
);

-- Ödeme kayıtları tablosu
CREATE TABLE odemeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    miktar DECIMAL(10,2),
    ilan_hakki INT,
    odeme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    odeme_durumu VARCHAR(50) DEFAULT 'beklemede',
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
);

-- Test markaları ekleme
INSERT INTO markalar (marka_adi) VALUES 
('Honda'),
('Yamaha'),
('Kawasaki'),
('Suzuki'),
('BMW'),
('Ducati'),
('KTM'),
('Aprilia');

-- Test modelleri ekleme
INSERT INTO modeller (marka_id, model_adi) VALUES 
(1, 'CBR600RR'),
(1, 'CB650R'),
(1, 'PCX 150'),
(1, 'Africa Twin'),
(2, 'YZF-R6'),
(2, 'MT-07'),
(2, 'NMAX 155'),
(2, 'Tenere 700'),
(3, 'Ninja 650'),
(3, 'Z900'),
(3, 'Versys 650'),
(4, 'GSX-R600'),
(4, 'V-Strom 650'),
(4, 'Burgman 400'),
(5, 'S1000RR'),
(5, 'F850GS'),
(6, 'Panigale V4'),
(6, 'Monster 821'),
(7, 'Duke 390'),
(7, '1290 Super Adventure'),
(8, 'RSV4 1100');

-- Admin kullanıcı ekleme (şifre: admin123)
INSERT INTO kullanicilar (ad, soyad, email, sifre, is_admin) VALUES 
('Admin', 'User', 'admin@motorunal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
