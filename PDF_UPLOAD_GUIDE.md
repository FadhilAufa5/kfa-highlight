# PDF Upload & Conversion - Implementation Guide

## Overview
Aplikasi ini menggunakan **Imagick** untuk mengkonversi PDF ke gambar JPG secara otomatis saat upload.

## Features
- ✅ Upload file PDF (max 10MB)
- ✅ Automatic PDF to JPG conversion (first page only)
- ✅ Fallback to placeholder image jika Imagick tidak tersedia
- ✅ Automatic storage cleanup on delete
- ✅ Single active PDF per user
- ✅ Resolution: 150 DPI
- ✅ Quality: 90% JPEG compression

## File Structure
```
app/
├── Http/Controllers/
│   └── PdfUploadController.php    # Main upload & conversion logic
└── Models/
    └── PdfUpload.php               # Database model

storage/app/public/
├── pdfs/                           # Original PDF files
└── pdf-images/                     # Converted JPG images

routes/
└── web.php                         # Route definition
```

## How It Works

### 1. Upload Process (`store` method)
```php
POST /uploads
Request: 
- title: string (required)
- pdf_file: file (required, PDF only, max 10MB)
```

**Flow:**
1. Validate input (title + PDF file)
2. Store PDF in `storage/app/public/pdfs/`
3. Convert PDF to JPG using `convertPdfToImage()`
4. If conversion fails, generate placeholder with `generatePlaceholderImage()`
5. Deactivate other PDFs by the same user
6. Save to database
7. Return success message

### 2. PDF to Image Conversion

**Method: `convertPdfToImage()`**

```php
private function convertPdfToImage(string $pdfPath): ?string
```

**Logic:**
1. Check if Imagick extension is loaded
2. Create `pdf-images` directory if not exists
3. Initialize Imagick with 150 DPI resolution
4. Read first page of PDF: `$pdfPath . '[0]'`
5. Set format to JPG with 90% quality
6. Remove transparency, set white background
7. Save to `storage/app/public/pdf-images/`
8. Clean up Imagick resources
9. Return relative path or `null` on error

**Key Imagick Methods:**
- `setResolution(150, 150)` - Set DPI before reading
- `readImage($path . '[0]')` - Read only first page
- `setImageFormat('jpg')` - Output format
- `setImageCompressionQuality(90)` - JPEG quality
- `setImageBackgroundColor('white')` - Remove transparency
- `setImageAlphaChannel()` - Alpha channel handling
- `mergeImageLayers()` - Flatten layers

### 3. Fallback Mechanism

Jika Imagick tidak tersedia atau konversi gagal:

**Method: `generatePlaceholderImage()`**

Menggunakan PHP GD library untuk membuat placeholder:
- Size: 1200x900px
- Background: Light gray (#F3F4F6)
- Text: "PDF Document"
- Simple document icon

## Routes Available

```php
GET    /uploads              # List all uploads (index)
GET    /uploads/create       # Show upload form
POST   /uploads              # Process upload (store)
GET    /uploads/{id}/edit    # Edit upload details
PUT    /uploads/{id}         # Update upload (update)
DELETE /uploads/{id}         # Delete upload (destroy)
```

## Validation Rules

```php
'title' => 'required|string|max:255',
'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB
'is_active' => 'boolean',
'order' => 'integer|min:0',
```

## Installation Requirements

### 1. Imagick Extension (Required)
See: `IMAGICK_INSTALLATION.md`

### 2. Ghostscript (Required for PDF)
Download from: https://www.ghostscript.com/

### 3. PHP Requirements
- PHP 8.2+
- GD extension (for placeholder fallback)

## Testing

### Test Imagick Installation
```bash
php test-imagick.php
```

### Test with PDF File
```bash
php test-imagick.php path/to/sample.pdf
```

### Verify in Browser
1. Go to `/uploads/create`
2. Upload a PDF file
3. Check if image preview is generated

## Storage Paths

**PDF Files:**
- Physical: `storage/app/public/pdfs/filename.pdf`
- URL: `/storage/pdfs/filename.pdf`

**Image Files:**
- Physical: `storage/app/public/pdf-images/filename.jpg`
- URL: `/storage/pdf-images/filename.jpg`

## Error Handling

**All errors are logged to Laravel log:**
```php
\Log::error('PDF upload failed: ' . $e->getMessage());
\Log::error('PDF to Image conversion failed: ' . $e->getMessage());
```

**User-facing errors:**
- File upload failures return to form with error
- Conversion failures fallback to placeholder (silent)
- Delete failures show error message

## Security Features

✅ File type validation (PDF only)  
✅ File size limit (10MB)  
✅ User ownership verification (Policy)  
✅ Authorization checks on all actions  
✅ Automatic cleanup on errors  
✅ Safe file storage outside public root

## Performance Considerations

- **Resolution:** 150 DPI (balance between quality & file size)
- **Compression:** 90% (high quality JPEG)
- **First page only:** Faster conversion, smaller files
- **Memory:** Imagick auto-manages memory
- **Cleanup:** Resources properly freed after conversion

## Common Issues

### PDF conversion returns null
- Check: `php -r "echo extension_loaded('imagick') ? 'OK' : 'NOT LOADED';"`
- Verify Ghostscript: `gs --version`
- Check logs: `storage/logs/laravel.log`

### Permission errors
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Directory not created
Check `ensureDirectoryExists()` method handles both Laravel 9+ and older versions.

## Configuration

**In `.env`:**
```env
FILESYSTEM_DISK=public
```

**Max upload size (php.ini):**
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

## Future Enhancements

- [ ] Multi-page PDF support (generate multiple images)
- [ ] Custom resolution settings
- [ ] Batch upload
- [ ] Image optimization (WebP format)
- [ ] Queue jobs for large PDFs
- [ ] Progress indicator for conversion
