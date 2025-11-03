# Setup Imagick untuk Windows

## Cara Install Imagick Extension di Windows

### Method 1: Menggunakan PECL (Recommended untuk Herd/Laravel Valet)

1. **Check PHP Version:**
```bash
php -v
```

2. **Download Imagick DLL:**
- Kunjungi: https://windows.php.net/downloads/pecl/releases/imagick/
- Download versi yang sesuai dengan PHP version Anda (misalnya: php_imagick-3.7.0-8.2-ts-vs16-x64.zip)
- Pilih:
  - `ts` jika Thread Safe
  - `nts` jika Non Thread Safe  
  - `x64` untuk 64-bit
  - `x86` untuk 32-bit

3. **Install Imagick:**
- Extract file zip
- Copy `php_imagick.dll` ke folder PHP extensions (biasanya: `C:\tools\php82\ext\`)
- Copy semua file DLL lainnya ke folder PHP root (biasanya: `C:\tools\php82\`)

4. **Enable Extension di php.ini:**
```ini
extension=imagick
```

5. **Restart PHP/Web Server:**
```bash
# Jika menggunakan Herd
# Restart melalui Herd UI atau:
net stop "Herd PHP"
net start "Herd PHP"
```

6. **Verify Installation:**
```bash
php -m | findstr imagick
php -r "echo extension_loaded('imagick') ? 'OK' : 'FAIL';"
```

### Method 2: Install ImageMagick + Imagick

1. **Install ImageMagick:**
- Download dari: https://imagemagick.org/script/download.php#windows
- Pilih: ImageMagick-7.x.x-Q16-HDRI-x64-dll.exe
- Install dengan options:
  - ✅ Install legacy utilities (e.g. convert)
  - ✅ Add application directory to PATH

2. **Verify ImageMagick:**
```bash
magick --version
convert --version
```

3. **Install Imagick Extension (follow Method 1 steps 2-6)**

### Method 3: Alternative - Use GhostScript (Fallback)

Jika Imagick sulit diinstall, gunakan GhostScript:

1. **Download GhostScript:**
- https://www.ghostscript.com/download/gsdnld.html
- Install Ghostscript 10.x for Windows (64-bit)

2. **Add to PATH:**
```bash
# Add to system PATH:
C:\Program Files\gs\gs10.xx.x\bin
```

3. **Update Controller untuk menggunakan GhostScript sebagai fallback**

### Testing

Setelah install, test dengan:

```bash
php artisan tinker
```

```php
$pdf = 'path/to/test.pdf';
$imagick = new Imagick();
$imagick->setResolution(150, 150);
$imagick->readImage($pdf . '[0]');
$imagick->setImageFormat('jpg');
echo "Success!";
```

## Troubleshooting

### Error: "The specified module could not be found"
- Pastikan semua DLL dependencies ada di folder PHP root
- Copy `CORE_RL_*.dll`, `IM_MOD_*.dll` dari archive ke folder PHP

### Error: "Call to undefined function Imagick::__construct()"
- Imagick extension belum enabled di php.ini
- Restart PHP setelah enable extension

### Error: "Failed to read the file"
- PDF path salah atau file tidak exist
- Check file permissions

## Rekomendasi untuk Development

Jika sulit install Imagick, gunakan:
1. **Laravel Vapor** atau **Laravel Cloud** untuk production (sudah include Imagick)
2. **Docker** dengan PHP image yang sudah include Imagick
3. **Fallback ke placeholder image** untuk development
