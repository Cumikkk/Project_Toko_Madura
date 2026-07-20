-- =========================================
-- DATABASE TOKO MADURA
-- =========================================

CREATE DATABASE IF NOT EXISTS toko_madura
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE toko_madura;

-- =========================================
-- TABEL USERS
-- =========================================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('master','investor','outlet') NOT NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- TABEL INVESTOR
-- =========================================
CREATE TABLE investor (
    id_investor INT AUTO_INCREMENT PRIMARY KEY,
    id_master INT NOT NULL,
    id_user INT NOT NULL UNIQUE,
    nama_investor VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_hp VARCHAR(20),

    CONSTRAINT fk_investor_master
        FOREIGN KEY (id_master)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_investor_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- TABEL OUTLET
-- =========================================
CREATE TABLE outlet (
    id_outlet INT AUTO_INCREMENT PRIMARY KEY,
    id_investor INT NOT NULL,
    id_user INT NOT NULL UNIQUE,
    kode_outlet VARCHAR(20) NOT NULL UNIQUE,
    nama_outlet VARCHAR(100) NOT NULL,
    alamat TEXT,
    status ENUM('aktif','tutup') DEFAULT 'aktif',

    CONSTRAINT fk_outlet_investor
        FOREIGN KEY (id_investor)
        REFERENCES investor(id_investor)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_outlet_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- TABEL PENJUALAN
-- =========================================
CREATE TABLE penjualan (
    id_penjualan INT AUTO_INCREMENT PRIMARY KEY,
    id_outlet INT NOT NULL,
    tanggal DATE NOT NULL,
    total_penjualan DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_penjualan_outlet
        FOREIGN KEY (id_outlet)
        REFERENCES outlet(id_outlet)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- TABEL PERSENTASE POTONGAN
-- =========================================
CREATE TABLE persentase_potongan (
    id_persentase INT AUTO_INCREMENT PRIMARY KEY,
    persen DECIMAL(5,2) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE DEFAULT NULL
) ENGINE=InnoDB;

-- =========================================
-- TABEL PERHITUNGAN BAGI HASIL
-- =========================================
CREATE TABLE perhitungan_bagi_hasil (
    id_bagi_hasil INT AUTO_INCREMENT PRIMARY KEY,
    id_investor INT NOT NULL,
    periode VARCHAR(20) NOT NULL,
    total_potongan DECIMAL(15,2) NOT NULL DEFAULT 0,
    bagian_investor DECIMAL(15,2) NOT NULL DEFAULT 0,
    bagian_outlet DECIMAL(15,2) NOT NULL DEFAULT 0,
    tanggal_hitung DATE NOT NULL,

    CONSTRAINT fk_bagi_hasil_investor
        FOREIGN KEY (id_investor)
        REFERENCES investor(id_investor)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- INDEX TAMBAHAN
-- =========================================
CREATE INDEX idx_penjualan_tanggal ON penjualan(tanggal);
CREATE INDEX idx_penjualan_outlet ON penjualan(id_outlet);
CREATE INDEX idx_outlet_investor ON outlet(id_investor);

-- =========================================
-- DATA DUMMY
-- =========================================

-- MASTER
INSERT INTO users (nama, username, password, role)
VALUES
('Master Utama', 'master', '123456', 'master');

-- INVESTOR
INSERT INTO users (nama, username, password, role)
VALUES
('Investor A', 'investor_a', '123456', 'investor'),
('Investor B', 'investor_b', '123456', 'investor');

INSERT INTO investor (id_master, id_user, nama_investor, alamat, no_hp)
VALUES
(1, 2, 'Investor A', 'Sidoarjo', '081234567890'),
(1, 3, 'Investor B', 'Surabaya', '081234567891');

-- OUTLET
INSERT INTO users (nama, username, password, role)
VALUES
('Outlet 1', 'outlet1', '123456', 'outlet'),
('Outlet 2', 'outlet2', '123456', 'outlet'),
('Outlet 3', 'outlet3', '123456', 'outlet');

INSERT INTO outlet (id_investor, id_user, kode_outlet, nama_outlet, alamat)
VALUES
(1, 4, 'OTL001', 'Outlet Taman', 'Taman'),
(1, 5, 'OTL002', 'Outlet Krian', 'Krian'),
(2, 6, 'OTL003', 'Outlet Waru', 'Waru');

-- PERSENTASE POTONGAN AKTIF
INSERT INTO persentase_potongan (persen, tanggal_mulai)
VALUES (20.00, '2026-01-01');

-- PENJUALAN
INSERT INTO penjualan (id_outlet, tanggal, total_penjualan)
VALUES
(1, '2026-07-01', 5000000),
(1, '2026-07-02', 6000000),
(2, '2026-07-01', 4000000),
(3, '2026-07-01', 7000000);

-- CONTOH HASIL BAGI HASIL
INSERT INTO perhitungan_bagi_hasil
(id_investor, periode, total_potongan, bagian_investor, bagian_outlet, tanggal_hitung)
VALUES
(1, '2026-07', 3000000, 1500000, 1500000, '2026-07-31');