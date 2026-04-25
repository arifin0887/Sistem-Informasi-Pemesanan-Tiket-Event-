-- =========================
-- DATABASE
-- =========================
CREATE DATABASE event_tiket;
USE event_tiket;

-- =========================
-- TABEL USERS
-- =========================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('user','petugas','admin') DEFAULT 'user'
);

-- =========================
-- TABEL VENUE
-- =========================
CREATE TABLE venue (
    id_venue INT AUTO_INCREMENT PRIMARY KEY,
    nama_venue VARCHAR(100),
    alamat TEXT,
    kapasitas INT
);

-- =========================
-- TABEL EVENT (DITAMBAH max_beli)
-- =========================
CREATE TABLE event (
    id_event INT AUTO_INCREMENT PRIMARY KEY,
    nama_event VARCHAR(150),
    tanggal DATE,
    id_venue INT,
    max_beli INT DEFAULT 5,

    FOREIGN KEY (id_venue) REFERENCES venue(id_venue)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================
-- TABEL TIKET (DITAMBAH kategori_tiket)
-- =========================
CREATE TABLE tiket (
    id_tiket INT AUTO_INCREMENT PRIMARY KEY,
    id_event INT,
    nama_tiket VARCHAR(50),
    kategori_tiket VARCHAR(50),
    harga INT,
    kuota INT,

    FOREIGN KEY (id_event) REFERENCES event(id_event)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================
-- TABEL VOUCHER
-- =========================
CREATE TABLE voucher (
    id_voucher INT AUTO_INCREMENT PRIMARY KEY,
    kode_voucher VARCHAR(20),
    potongan INT,
    kuota INT,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif'
);

-- =========================
-- TABEL ORDERS (DITAMBAH metode_bayar & bukti_transfer)
-- =========================
CREATE TABLE orders (
    id_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    tanggal_order DATETIME DEFAULT CURRENT_TIMESTAMP,
    total INT,
    status ENUM('pending','menunggu_verifikasi','paid','cancel') DEFAULT 'pending',
    id_voucher INT NULL,

    metode_bayar VARCHAR(50),
    bukti_transfer VARCHAR(255),

    FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (id_voucher) REFERENCES voucher(id_voucher)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================
-- TABEL ORDER DETAIL
-- =========================
CREATE TABLE order_detail (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_order INT,
    id_tiket INT,
    qty INT,
    subtotal INT,

    FOREIGN KEY (id_order) REFERENCES orders(id_order)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (id_tiket) REFERENCES tiket(id_tiket)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================
-- TABEL ATTENDEE
-- =========================
CREATE TABLE attendee (
    id_attendee INT AUTO_INCREMENT PRIMARY KEY,
    id_detail INT,
    kode_tiket VARCHAR(50),
    status_checkin ENUM('belum','sudah') DEFAULT 'belum',
    waktu_checkin DATETIME NULL,

    FOREIGN KEY (id_detail) REFERENCES order_detail(id_detail)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);