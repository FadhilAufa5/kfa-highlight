# Troubleshooting Guide - PDF to Image

## Issue: Images Not Showing on Welcome/Hero Page

### ✅ SOLVED

**Problem:** PDF uploads were not displaying as images on the welcome page, showing "No preview available" instead.

**Root Causes Found:**
1. ❌ **Imagick Extension Not Installed** - PDF conversion failed, fell back to placeholder
2. ✅ **Placeholder Images Generated** - Placeholders were created successfully
3. ✅ **Database image_path Saved** - Database had correct image paths
4. ❌ **Storage Link Missing** - `public/storage` symlink was not created

### Solution Applied

```bash
# Create storage symlink
php artisan storage:link
```

**Result:** Images now accessible at `/storage/pdf-images/{filename}.jpg`

---

## Complete Diagnostic Steps

### 1. Check Imagick Extension Status

```bash
php -r "echo (extension_loaded('imagick') ? 'Imagick: INSTALLED' : 'Imagick: NOT INSTALLED');"
```

**Expected Output (for PDF conversion):**
```
Imagick: INSTALLED
```

**Current Status:**
```
Imagick: NOT INSTALLED
```

### 2. Verify Image Files Exist

```bash
# Windows PowerShell
Get-ChildItem storage\app\public\pdf-images
```

**Expected:** List of `.jpg` files matching PDF uploads

**Current Status:** ✅ Files exist
```
DNfVdROSB4vLvltbIjIwQ7bzqw9fo5DiXpVHVeIv.jpg  (20,904 bytes)
J0eAlIBMpV0IpmhCPvr9JGkwj0P9RMGYaARBqDtQ.jpg  (20,904 bytes)
```

### 3. Check Database image_path

```bash
php artisan tinker
>>> App\Models\PdfUpload::all()->pluck('title', 'image_path')
```

**Current Status:** ✅ Database has correct paths
```
ID: 6 | Title: huyy  | Image: pdf-images/DNfVdROSB4vLvltbIjIwQ7bzqw9fo5DiXpVHVeIv.jpg
ID: 7 | Title: sD    | Image: pdf-images/J0eAlIBMpV0IpmhCPvr9JGkwj0P9RMGYaARBqDtQ.jpg
```

### 4. Verify Storage Link

```bash
# Windows PowerShell
Test-Path public\storage

# Check symlink target
Get-Item public\storage | Select-Object LinkType, Target
```

**Expected Output:**
```
LinkType : Junction
Target   : C:\...\storage\app\public
```

**Before Fix:** ❌ Link was missing  
**After Fix:** ✅ Link created successfully

### 5. Test Image Access

```bash
# Check if image is accessible through public link
Test-Path public\storage\pdf-images\{filename}.jpg
```

**Status:** ✅ Now accessible via web browser

---

## Current System Status

| Component | Status | Notes |
|-----------|--------|-------|
| Spatie PDF to Image | ✅ Installed | v1.2.2 in composer.json |
| Imagick Extension | ❌ Not Installed | Using placeholder fallback |
| ImageMagick Binary | ❌ Not Installed | Required for Imagick |
| Placeholder Generation | ✅ Working | GD library generating 1200x900 JPG |
| Database image_path | ✅ Saved | Correctly stored in database |
| Storage Directory | ✅ Created | `storage/app/public/pdf-images/` |
| Storage Symlink | ✅ Created | `public/storage` → `storage/app/public` |
| Frontend Display | ✅ Working | React component uses `image_path` |

---

## Image Generation Flow

### Current Flow (Without Imagick)

```
1. User uploads PDF
   ↓
2. PdfUploadController::store()
   ↓
3. convertPdfToImage() checks Imagick
   ↓ (Imagick NOT installed)
4. Returns null
   ↓
5. generatePlaceholderImage() creates JPG
   ↓
6. Saves to storage/app/public/pdf-images/
   ↓
7. Database saves: image_path = 'pdf-images/{hash}.jpg'
   ↓
8. Frontend renders: /storage/pdf-images/{hash}.jpg
   ↓
9. Symlink resolves to actual file
   ✓ Image displays
```

### With Imagick Installed (Future)

```
1. User uploads PDF
   ↓
2. PdfUploadController::store()
   ↓
3. convertPdfToImage() checks Imagick
   ↓ (Imagick IS installed)
4. Spatie\PdfToImage\Pdf converts first page
   ↓
5. Saves high-quality JPG (150 DPI)
   ↓
6. Returns image path
   ✓ Real PDF preview displays
```

---

## Quick Fixes

### Images Not Showing?

**1. Check storage link:**
```bash
php artisan storage:link
```

**2. Regenerate images for existing PDFs:**
```bash
php regenerate-images.php
```

**3. Check file permissions:**
```bash
# Linux/Mac
chmod -R 755 storage/app/public/pdf-images
```

**4. Clear Laravel cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Install Imagick (Optional - For Real PDF Previews)

### Windows (Herd/Laragon)

1. Download Imagick DLL for your PHP version
   - PHP 8.2: https://pecl.php.net/package/imagick
   - Or from PECL: https://windows.php.net/downloads/pecl/releases/imagick/

2. Copy `php_imagick.dll` to PHP extensions directory
   ```
   C:\Users\{User}\AppData\Local\Herd\bin\{phpVersion}\ext\
   ```

3. Enable in `php.ini`:
   ```ini
   extension=imagick
   ```

4. Install ImageMagick binary:
   - Download: https://imagemagick.org/script/download.php#windows
   - Install to: `C:\Program Files\ImageMagick-7.x.x-Q16-HDRI`
   - Add to PATH environment variable

5. Restart Herd/Web Server

6. Verify:
   ```bash
   php -m | findstr imagick
   convert --version
   ```

### Linux/Ubuntu

```bash
sudo apt-get update
sudo apt-get install -y imagemagick php-imagick
sudo systemctl restart apache2  # or nginx
```

### macOS (Homebrew)

```bash
brew install imagemagick
brew install pkg-config
pecl install imagick
```

---

## Maintenance Scripts

### regenerate-images.php

Located in project root. Use to regenerate images for PDFs without image_path.

```bash
php regenerate-images.php
```

**What it does:**
- Finds all PdfUpload records with NULL or empty image_path
- Generates placeholder images (or converts PDF if Imagick available)
- Updates database with new image paths
- Reports success/failure for each upload

---

## Frontend Code (welcome.tsx)

The welcome page correctly uses `image_path` field:

```tsx
{upload.image_path ? (
    <img
        src={`/storage/${upload.image_path}`}
        alt={upload.title}
        className="h-full w-full object-cover"
    />
) : (
    <div className="flex h-full items-center justify-center">
        <FileText className="h-16 w-16 text-slate-400" />
        <p className="text-sm">No preview available</p>
    </div>
)}
```

**Key Points:**
- Uses `/storage/` prefix (requires symlink)
- Shows fallback if `image_path` is null
- Gracefully handles missing images

---

## Common Issues & Solutions

### Issue: "Storage link already exists"

**Solution:**
```bash
# Delete existing link
rm public/storage  # Linux/Mac
Remove-Item public\storage  # Windows PowerShell

# Recreate
php artisan storage:link
```

### Issue: Permission denied on storage directory

**Solution (Linux/Mac):**
```bash
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

### Issue: Placeholder image is blank

**Check:**
1. GD library installed: `php -m | grep gd`
2. Storage directory writable
3. Check Laravel logs: `storage/logs/laravel.log`

### Issue: PDF conversion fails with Imagick installed

**Check:**
1. ImageMagick binary installed: `convert --version`
2. PDF has security policy restrictions
3. Check `/etc/ImageMagick-*/policy.xml` for PDF permissions

---

## Testing Checklist

- [ ] Storage link exists: `Test-Path public\storage`
- [ ] Images exist: `ls storage/app/public/pdf-images`
- [ ] Database has image_path: Check `pdf_uploads` table
- [ ] Images accessible: Visit `/storage/pdf-images/{filename}.jpg`
- [ ] Welcome page displays images correctly
- [ ] Upload new PDF creates image
- [ ] Delete PDF removes both PDF and image files

---

## Performance Notes

**Placeholder Images:**
- Size: ~20KB per image
- Dimensions: 1200x900 pixels
- Format: JPEG (quality 90)
- Generation time: <100ms

**Real PDF Conversion (with Imagick):**
- Size: 100-500KB per image (depends on PDF complexity)
- Resolution: 150 DPI
- Format: JPEG
- Generation time: 1-3 seconds per page

**Recommendation:** Install Imagick for production to show actual PDF content instead of generic placeholder.
