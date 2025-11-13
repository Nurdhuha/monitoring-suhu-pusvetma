SET default_storage_engine=InnoDB;

-- 1. Tabel untuk Admin (Pengguna)
-- Tabel ini akan menyimpan data admin yang bisa login.
-- Di Laravel, tabel ini biasanya dibuat otomatis oleh 'php artisan make:auth' atau Breeze/Jetstream.
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `users_email_unique` (`email`)
) 
COMMENT='Menyimpan data admin/pengguna yang bisa login';


-- 2. Tabel untuk Alat (Devices)
-- Ini adalah tabel induk untuk setiap 'alat' monitoring yang dimiliki.
CREATE TABLE IF NOT EXISTS `devices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'Nama alat, misal: Gudang Blok A',
  `location` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Lokasi fisik alat',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Admin yang bertanggung jawab/menambahkan alat ini',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_devices_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE -- Jika user dihapus, devicenya juga terhapus
)
COMMENT='Mencatat setiap alat monitoring utama';


-- 3. Tabel untuk Sections (Coolroom & Freezer)
-- Tabel ini adalah inti dari permintaan Anda.
-- Setiap 'device' bisa memiliki BANYAK 'section' (Coolroom atau Freezer).
CREATE TABLE IF NOT EXISTS `sections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `device_id` BIGINT UNSIGNED NOT NULL COMMENT 'Merujuk ke alat (device) mana section ini terpasang',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nama section, misal: Freezer Daging Sapi, Coolroom Buah',
  `type` ENUM('coolroom', 'freezer') NOT NULL COMMENT 'Tipe section',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_sections_device_id`
    FOREIGN KEY (`device_id`)
    REFERENCES `devices` (`id`)
    ON DELETE CASCADE -- Jika device dihapus, section di dalamnya ikut terhapus
)
COMMENT='Mencatat bagian Coolroom atau Freezer dari sebuah alat';


-- 4. Tabel untuk Data Suhu (Temperature Readings)
-- Tabel ini akan menyimpan data suhu yang masuk dari setiap section.
-- Tabel ini akan menjadi sangat besar seiring waktu.
CREATE TABLE IF NOT EXISTS `temperature_readings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id` BIGINT UNSIGNED NOT NULL COMMENT 'Merujuk ke section (Coolroom/Freezer) mana suhu ini diambil',
  `temperature` DECIMAL(5, 2) NOT NULL COMMENT 'Nilai suhu, misal: -18.50 atau 5.25',
  `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pasti kapan suhu ini dicatat',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_readings_section_id`
    FOREIGN KEY (`section_id`)
    REFERENCES `sections` (`id`)
    ON DELETE CASCADE, -- Jika section dihapus, datanya ikut terhapus
  
  -- Index untuk mempercepat query pencarian data berdasarkan rentang waktu
  INDEX `idx_section_time` (`section_id`, `recorded_at`)
)
COMMENT='Menyimpan histori data suhu dari setiap section'