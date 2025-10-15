-- =====================================================
-- DATABASE: kas_database
-- Sistem Keuangan / Kas Kelas Sederhana
-- Dibuat oleh ChatGPT untuk Geppie (2025)
-- =====================================================

-- 1️⃣ Buat database baru
CREATE DATABASE IF NOT EXISTS kas_database
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE kas_database;

-- 2️⃣ Tabel: anggota
CREATE TABLE IF NOT EXISTS anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3️⃣ Tabel: kas
CREATE TABLE IF NOT EXISTS kas (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    id_anggota INT NOT NULL,
    jenis ENUM('masuk', 'keluar') NOT NULL,
    jumlah INT NOT NULL,
    keterangan VARCHAR(255),
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4️⃣ Contoh data anggota
INSERT INTO anggota (nama) VALUES 
('Hesya Rahmandini'),
('Geppie'),
('Dinda Puspita'),
('Rafi Pratama');

-- 5️⃣ Contoh transaksi awal
INSERT INTO kas (tanggal, id_anggota, jenis, jumlah, keterangan) VALUES
('2025-10-01', 1, 'masuk', 10000, 'Iuran Bulanan Oktober'),
('2025-10-02', 2, 'masuk', 10000, 'Iuran Bulanan Oktober'),
('2025-10-05', 3, 'keluar', 5000, 'Beli Snack untuk Rapat'),
('2025-10-07', 4, 'masuk', 10000, 'Iuran Bulanan Oktober');
