# Imagick Installation Guide for Windows (Herd/Laragon)

## Prerequisites
- PHP 8.2 or higher
- Windows OS

## Installation Steps

### 1. Download Imagick Extension

1. Visit the PECL Imagick Windows builds: https://windows.php.net/downloads/pecl/releases/imagick/
2. Download the appropriate version for your PHP:
   - Check your PHP version: `php -v`
   - Check if you're using Thread Safe (TS) or Non-Thread Safe (NTS): `php -i | findstr "Thread"`
   - Download the matching DLL file (e.g., `php_imagick-3.7.0-8.2-ts-vs16-x64.zip`)

### 2. Extract and Install

1. Extract the downloaded ZIP file
2. Copy `php_imagick.dll` to your PHP extensions directory:
   - Herd: `C:\Users\{YourUsername}\.config\herd\bin\{php-version}\ext\`
   - Laragon: `C:\laragon\bin\php\{php-version}\ext\`
3. Copy all other DLL files (CORE_RL_*.dll) to your PHP root directory:
   - Herd: `C:\Users\{YourUsername}\.config\herd\bin\{php-version}\`
   - Laragon: `C:\laragon\bin\php\{php-version}\`

### 3. Enable the Extension

1. Open your `php.ini` file:
   - Run: `php --ini` to find the location
2. Add this line at the end of the file:
   ```
   extension=imagick
   ```
3. Save the file

### 4. Install Ghostscript (Required for PDF conversion)

1. Download Ghostscript from: https://www.ghostscript.com/releases/gsdnld.html
2. Install the Windows 64-bit version
3. Add Ghostscript to your system PATH:
   - Default installation path: `C:\Program Files\gs\gs10.XX.X\bin`
   - Add this path to your Windows Environment Variables

### 5. Restart Your Web Server

- If using Herd: Restart Herd from the system tray
- If using Laragon: Stop and start Apache/Nginx

### 6. Verify Installation

Run this command:
```bash
php -r "echo extension_loaded('imagick') ? 'Imagick is loaded' : 'Imagick NOT loaded';"
```

You should see: `Imagick is loaded`

Also verify Ghostscript:
```bash
gs --version
```

## Troubleshooting

### Issue: "The specified module could not be found"
- Make sure you copied ALL DLL files from the ZIP, not just `php_imagick.dll`
- Verify that the DLL files are in the correct PHP directory

### Issue: "PDF conversion fails"
- Ensure Ghostscript is installed and added to PATH
- Restart your terminal/command prompt after adding to PATH
- Test Ghostscript: `gs --version`

### Issue: Extension not loading
- Check that you're editing the correct `php.ini` file (use `php --ini`)
- Verify the PHP version matches the DLL version (TS vs NTS)
- Make sure the line `extension=imagick` is not commented out (no `;` at the start)

## Testing the Implementation

After installation, upload a PDF file through your application. The system will:
1. Store the PDF in `storage/app/public/pdfs/`
2. Convert the first page to JPG in `storage/app/public/pdf-images/`
3. Display the image preview

If Imagick is not available, the system will automatically generate a placeholder image.
