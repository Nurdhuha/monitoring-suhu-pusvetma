# Instruksi Mengaktifkan PHP Zip Extension

## Error yang Terjadi
```
Class "ZipArchive" not found
```

## Penyebab
PHP extension `zip` belum diaktifkan. Extension ini diperlukan untuk membaca file Excel (.xlsx).

## Solusi

### 1. Buka File php.ini
Lokasi file: `C:\xampp\php\php.ini`

### 2. Cari dan Aktifkan Extension Zip

Cari baris berikut (gunakan Ctrl+F):
```ini
;extension=zip
```

Hapus tanda `;` di depannya sehingga menjadi:
```ini
extension=zip
```

Jika tidak menemukan baris tersebut, tambahkan baris baru:
```ini
extension=zip
```

### 3. Simpan File php.ini

### 4. Restart Services

1. Stop server Laravel (Ctrl+C di terminal)
2. Restart XAMPP Control Panel:
   - Stop Apache
   - Start Apache
3. Jalankan kembali Laravel:
   ```bash
   php artisan serve
   ```

### 5. Verifikasi

Jalankan command berikut untuk memastikan extension zip sudah aktif:
```bash
php -m | findstr zip
```

Jika berhasil, Anda akan melihat output:
```
zip
```

### 6. Test Import Excel

Setelah extension zip aktif, coba import file Excel lagi melalui aplikasi.

## Catatan Tambahan

Jika masih error setelah mengaktifkan extension zip, pastikan:

1. File `php_zip.dll` ada di folder `C:\xampp\php\ext\`
2. Restart komputer jika diperlukan
3. Periksa versi PHP Anda dengan `php -v`

## Alternative: Gunakan CSV

Jika tidak bisa mengaktifkan extension zip, Anda masih bisa menggunakan file CSV untuk import data, karena CSV tidak memerlukan ZipArchive.
