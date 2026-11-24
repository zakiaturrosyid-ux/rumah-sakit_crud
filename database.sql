CREATE DATABASE IF NOT EXISTS rumah_sakit
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE rumah_sakit;

CREATE TABLE IF NOT EXISTS pasien (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nik VARCHAR(20) NOT NULL UNIQUE,
  jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
  tanggal_lahir DATE NOT NULL,
  alamat TEXT NOT NULL,
  no_telp VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dokter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  spesialis VARCHAR(100) NOT NULL,
  no_sip VARCHAR(30) NOT NULL UNIQUE,
  no_telp VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ruangan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_ruangan VARCHAR(100) NOT NULL,
  lantai VARCHAR(20) NOT NULL,
  kelas VARCHAR(50) NOT NULL,
  kapasitas INT NOT NULL DEFAULT 1,
  status ENUM('Tersedia', 'Terisi', 'Perawatan') NOT NULL DEFAULT 'Tersedia',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS obat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_obat VARCHAR(100) NOT NULL,
  jenis VARCHAR(50) NOT NULL,
  satuan VARCHAR(20) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS janji_temu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pasien_id INT NOT NULL,
  dokter_id INT NOT NULL,
  tanggal_appointment DATE NOT NULL,
  jam_appointment TIME NOT NULL,
  keluhan TEXT,
  status ENUM('Menunggu', 'Diperiksa', 'Selesai', 'Batal') NOT NULL DEFAULT 'Menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE,
  FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON DELETE CASCADE
);
