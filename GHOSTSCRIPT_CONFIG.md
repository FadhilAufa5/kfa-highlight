# Ghostscript Auto-Configuration untuk Windows

## Overview
PdfConverterService sekarang memiliki auto-detection Ghostscript untuk Windows, memastikan konversi PDF berjalan lancar tanpa konfigurasi manual.

## Fitur Baru

### Auto-Detection Ghostscript (Windows Only)
Service akan otomatis:
1. Mencari Ghostscript di lokasi standar Windows
2. Detect versi terbaru yang terinstall
3. Set environment variable `MAGICK_GHOSTSCRIPT_PATH`
4. Log status konfigurasi untuk debugging

### Path yang Dicek (berurutan)

#### 64-bit Ghostscript (Priority)
```
C:\Program Files\gs\gs10.04.0\bin\gswin64c.exe
C:\Program Files\gs\gs10.03.1\bin\gswin64c.exe
C:\Program Files\gs\gs10.03.0\bin\gswin64c.exe
C:\Program Files\gs\gs10.02.1\bin\gswin64c.exe
C:\Program Files\gs\gs10.02.0\bin\gswin64c.exe
C:\Program Files\gs\gs10.01.2\bin\gswin64c.exe
C:\Program Files\gs\gs10.01.1\bin\gswin64c.exe
C:\Program Files\gs\gs10.01.0\bin\gswin64c.exe
C:\Program Files\gs\gs10.00.0\bin\gswin64c.exe
```

#### 32-bit Ghostscript (Fallback)
```
C:\Program Files (x86)\gs\gs10.04.0\bin\gswin32c.exe
C:\Program Files (x86)\gs\gs10.03.1\bin\gswin32c.exe
C:\Program Files (x86)\gs\gs10.03.0\bin\gswin32c.exe
```

#### Auto-detect Latest Version
Jika path spesifik tidak ditemukan, service akan:
1. Scan semua folder di `C:\Program Files\gs\`
2. Ambil versi terbaru
3. Check `bin\gswin64c.exe` atau `bin\gswin32c.exe`

## Cara Kerja

### 1. Deteksi OS
```php
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
```
Hanya berjalan di Windows, sistem lain menggunakan default Imagick config.

### 2. Check Path Predefined
Loop melalui `$possiblePaths` dan check `file_exists()`.

### 3. Auto-detect Fallback
Jika tidak ada path yang match, gunakan `glob()` untuk scan versi terbaru.

### 4. Set Environment Variable
```php
putenv("MAGICK_GHOSTSCRIPT_PATH={$gsPath}");
```

### 5. Logging
Log hasil konfigurasi untuk debugging:
- ✅ `Ghostscript configured: {path}` - Ditemukan di predefined path
- ✅ `Ghostscript auto-detected: {path}` - Ditemukan via auto-detect
- ⚠️ `Ghostscript not found` - Tidak ditemukan di path manapun
- ⚠️ `Ghostscript not installed` - Folder gs tidak ada

## Instalasi Ghostscript

### Download
https://www.ghostscript.com/releases/gsdnld.html

### Pilih Versi
- **Recommended:** Ghostscript 10.03.1 atau lebih baru
- **Architecture:** 64-bit (gswin64c.exe) untuk Windows 64-bit

### Instalasi
1. Run installer dengan default settings
2. Path akan otomatis: `C:\Program Files\gs\gs10.xx.x\`
3. Tidak perlu tambahkan ke PATH (service sudah auto-detect)
4. Restart web server (Laravel/Herd)

## Verifikasi

### Check Manual
```bash
# Windows Command Prompt
dir "C:\Program Files\gs"

# Check spesifik versi
dir "C:\Program Files\gs\gs10.03.1\bin\gswin64c.exe"
```

### Check via PHP
```php
php artisan tinker
>>> putenv("MAGICK_GHOSTSCRIPT_PATH=C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe");
>>> $im = new Imagick();
>>> $im->readImage('path/to/test.pdf');
```

### Check Log
Setelah upload PDF, check `storage/logs/laravel.log`:
```log
[2025-11-06 06:45:12] local.INFO: Ghostscript configured: C:\Program Files\gs\gs10.03.1\bin\gswin64c.exe
[2025-11-06 06:45:13] local.INFO: Converting PDF with 3 page(s): pdfs/test.pdf
[2025-11-06 06:45:15] local.INFO: Successfully converted 3 page(s) to images
```

## Troubleshooting

### Ghostscript Not Found
**Problem:** Log menampilkan `Ghostscript not found`

**Solution:**
1. Pastikan Ghostscript terinstall
2. Check path instalasi:
   ```bash
   dir "C:\Program Files\gs"
   ```
3. Jika terinstall di lokasi berbeda:
   - Update `$possiblePaths` di `PdfConverterService.php`
   - Atau tambahkan ke system PATH
4. Restart web server

### PDF Conversion Failed
**Problem:** Conversion status stuck di "processing" atau "failed"

**Solution:**
1. Check Imagick extension:
   ```bash
   php -m | findstr imagick
   ```
2. Check Ghostscript executable:
   ```bash
   "C:\Program Files\gs\gs10.03.1\bin\gswin64c.exe" --version
   ```
3. Check logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```
4. Test manual conversion:
   ```bash
   php artisan tinker
   >>> $converter = app(\App\Services\PdfConverterService::class);
   >>> $result = $converter->convertToImages('pdfs/test.pdf');
   >>> var_dump($result);
   ```

### Permission Denied
**Problem:** `Failed to save image for page X`

**Solution:**
```bash
# Windows - Give write permission to storage folder
icacls storage /grant Users:F /T
```

### Different Installation Path
**Problem:** Ghostscript terinstall di custom location (e.g., `D:\Tools\gs\`)

**Solution:**
Edit `PdfConverterService.php` dan tambahkan path custom:
```php
$possiblePaths = [
    'D:\\Tools\\gs\\gs10.03.1\\bin\\gswin64c.exe', // Custom path
    'C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe',
    // ... existing paths
];
```

## Platform Support

### Windows ✅
- Auto-detection aktif
- Support gswin64c.exe dan gswin32c.exe
- Environment variable otomatis

### Linux/Mac 🔄
- Menggunakan default Imagick configuration
- Ghostscript biasanya sudah di PATH
- Tidak perlu konfigurasi tambahan

## Performance Impact

### Overhead
- Check `file_exists()`: ~1ms per path
- Total overhead: ~10-20ms (hanya sekali saat conversion start)

### Optimization
Path yang paling umum diletakkan di awal array untuk mempercepat detection.

## Future Improvements
- [ ] Support custom path via .env variable
- [ ] Cache detected path untuk menghindari re-scan
- [ ] Support portable Ghostscript
- [ ] Add GUI tool untuk test configuration
