# PDF Conversion System

## Overview
Sistem konversi PDF ke PNG dengan arsitektur service dan queue yang proper, memungkinkan konversi asynchronous dengan dukungan multi-halaman.

## Komponen Utama

### 1. PdfConverterService
**Lokasi:** `app/Services/PdfConverterService.php`

Service class yang menangani logika konversi PDF menggunakan Imagick:
- Konversi PDF multi-halaman ke format PNG (150 DPI)
- Generate placeholder image jika Imagick tidak tersedia
- Mengelola file cleanup untuk images

**Method:**
- `convertToImages(string $pdfPath): ?array` - Konversi PDF ke array image paths
- `generatePlaceholder(string $pdfPath): ?string` - Generate placeholder image
- `deleteImages(string|array|null $imagePaths): void` - Hapus converted images

### 2. ProcessPdfConversion Job
**Lokasi:** `app/Jobs/ProcessPdfConversion.php`

Queue job untuk proses konversi asynchronous:
- Timeout: 300 detik (5 menit)
- Retry: 3 kali jika gagal
- Auto-fallback ke placeholder jika Imagick conversion gagal
- Update conversion status: pending → processing → completed/failed

### 3. PdfUpload Model
**Lokasi:** `app/Models/PdfUpload.php`

**Cast Attributes:**
- `image_path` → array (untuk support multi-halaman)
- `is_active` → boolean

**Fillable:**
- `conversion_status` - Status konversi (pending, processing, completed, failed)

### 4. PdfUploadController
**Lokasi:** `app/Http/Controllers/PdfUploadController.php`

**Flow Upload:**
1. Validate & simpan PDF file
2. Create PdfUpload record dengan status 'pending'
3. Dispatch ProcessPdfConversion job
4. Redirect dengan message "Conversion in progress..."

## Database Schema

### Migration: add_conversion_status_to_pdf_uploads_table
```sql
ALTER TABLE pdf_uploads ADD COLUMN conversion_status VARCHAR(255) DEFAULT 'pending' AFTER image_path;
```

**Conversion Status Values:**
- `pending` - Belum diproses
- `processing` - Sedang dikonversi
- `completed` - Berhasil dikonversi
- `failed` - Gagal dikonversi

## Frontend (welcome.tsx)

**Upload Interface Type:**
```typescript
interface Upload {
    id: number;
    title: string;
    image_path: string[] | null;  // Array untuk multi-halaman
    pdf_path: string;
    conversion_status: 'pending' | 'processing' | 'completed' | 'failed';
}
```

**Display Logic:**
- `pending/processing` → Loading spinner dengan message
- `failed` → Error state dengan icon merah
- `completed` → Tampilkan image pertama (image_path[0])
- Auto-reload setiap 5 detik untuk slideshow

## Cara Menggunakan

### Setup Queue Worker
Jalankan queue worker untuk memproses konversi:
```bash
php artisan queue:work
```

Atau gunakan Laravel Horizon (production):
```bash
php artisan horizon
```

### Upload PDF via Controller
```php
// Queue akan otomatis dihandle oleh PdfUploadController::store()
// Dispatch ProcessPdfConversion job secara otomatis
```

### Manual Dispatch (jika diperlukan)
```php
use App\Jobs\ProcessPdfConversion;

ProcessPdfConversion::dispatch($uploadId);
```

### Check Conversion Status
```php
$upload = PdfUpload::find($id);
echo $upload->conversion_status; // pending, processing, completed, failed
```

## Image Output Format

**Directory:** `storage/app/public/pdf-images/`

**Naming Convention:**
```
{basename}_{uniqueId}_page{index}.png
```

**Contoh:**
```
document_67890abc_page0.png
document_67890abc_page1.png
document_67890abc_page2.png
```

## Error Handling

### Service Level
- Imagick tidak tersedia → Return null → Fallback ke placeholder
- PDF file tidak ditemukan → Throw exception → Log error
- Konversi gagal → Return null → Log error

### Job Level
- Max 3 retry attempts dengan backoff
- Failed job handler: Update status ke 'failed'
- Log semua errors untuk debugging

### Controller Level
- Try-catch semua operations
- Cleanup file on error
- User-friendly error messages

## Configuration

### Queue Connection (.env)
```env
QUEUE_CONNECTION=database
```

### Imagick Settings
Bisa dimodifikasi di `PdfConverterService`:
```php
private const RESOLUTION = 150;          // DPI
private const COMPRESSION_QUALITY = 95;  // PNG quality
private const IMAGE_FORMAT = 'png';      // Output format
```

### Ghostscript Configuration (Windows)
Service akan otomatis mencari dan mengkonfigurasi Ghostscript di Windows:
- Mencari di `C:\Program Files\gs\` untuk gswin64c.exe
- Mencari di `C:\Program Files (x86)\gs\` untuk gswin32c.exe
- Auto-detect versi terbaru jika path spesifik tidak ditemukan
- Set environment variable `MAGICK_GHOSTSCRIPT_PATH` secara otomatis

**Instalasi Ghostscript:**
1. Download dari: https://www.ghostscript.com/releases/gsdnld.html
2. Install Ghostscript 10.x for Windows (64-bit)
3. Service akan auto-detect, tidak perlu konfigurasi manual

## Testing

### Test Imagick Availability
```bash
php -r "echo extension_loaded('imagick') ? 'OK' : 'NOT AVAILABLE';"
```

### Process Specific Job
```bash
php artisan queue:work --once
```

### Monitor Queue
```bash
php artisan queue:monitor
```

## Troubleshooting

### Queue tidak jalan
```bash
# Check jobs table
php artisan tinker
>>> \App\Models\Job::count()

# Manually process
php artisan queue:work --once
```

### Imagick Error
```bash
# Verify Imagick
php test-imagick.php

# Check logs
tail -f storage/logs/laravel.log
```

### Ghostscript Not Found (Windows)
Jika muncul error "Ghostscript not found":
1. Pastikan Ghostscript sudah terinstall
2. Check path manual:
```bash
dir "C:\Program Files\gs"
```
3. Jika terinstall di lokasi berbeda, tambahkan ke PATH atau update `$possiblePaths` di service
4. Restart web server setelah install Ghostscript
5. Check log untuk konfirmasi:
```bash
# Laravel log akan menampilkan:
# "Ghostscript configured: C:\Program Files\gs\gs10.xx.x\bin\gswin64c.exe"
```

### Storage Permission
```bash
# Windows
icacls storage /grant Users:F /T

# Linux/Mac
chmod -R 775 storage
chown -R www-data:www-data storage
```

## Future Improvements
- [ ] Multiple image slideshow di welcome page
- [ ] Real-time status update via WebSocket/Polling
- [ ] Image optimization/compression
- [ ] Thumbnail generation
- [ ] Batch conversion support
- [ ] Progress indicator per page
