<?php

/**
 * Script to regenerate image_path for existing PDF uploads
 * This will create placeholder images for all PDFs that don't have image_path
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PdfUpload;
use Illuminate\Support\Facades\Storage;

echo "=== Regenerate Images for PDF Uploads ===\n\n";

// Check if Imagick is loaded
$hasImagick = extension_loaded('imagick');
echo "Imagick Status: " . ($hasImagick ? "INSTALLED ✓" : "NOT INSTALLED ✗") . "\n";
echo "Strategy: " . ($hasImagick ? "Will try PDF conversion" : "Will generate placeholders only") . "\n\n";

// Get all uploads without image_path or with null image_path
$uploads = PdfUpload::whereNull('image_path')
    ->orWhere('image_path', '')
    ->get();

echo "Found {$uploads->count()} uploads without images\n\n";

if ($uploads->isEmpty()) {
    echo "No uploads need image generation. Checking all uploads...\n";
    $allUploads = PdfUpload::all();
    foreach ($allUploads as $upload) {
        echo "ID: {$upload->id} | Title: {$upload->title} | Image: " . ($upload->image_path ?? 'NULL') . "\n";
    }
    exit(0);
}

foreach ($uploads as $upload) {
    echo "Processing: {$upload->title} (ID: {$upload->id})\n";
    echo "  PDF Path: {$upload->pdf_path}\n";
    
    $imagePath = generatePlaceholderImage($upload->pdf_path);
    
    if ($imagePath) {
        $upload->image_path = $imagePath;
        $upload->save();
        echo "  ✓ Image created: {$imagePath}\n";
    } else {
        echo "  ✗ Failed to create image\n";
    }
    
    echo "\n";
}

echo "Done! All images processed.\n";

/**
 * Generate placeholder image for PDF
 */
function generatePlaceholderImage($pdfPath): ?string
{
    try {
        // Ensure directory exists
        $directory = 'pdf-images';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $width = 1200;
        $height = 900;
        
        $image = imagecreatetruecolor($width, $height);
        
        if ($image === false) {
            throw new Exception('Failed to create image resource');
        }
        
        $bgColor = imagecolorallocate($image, 243, 244, 246);
        $textColor = imagecolorallocate($image, 75, 85, 99);
        $iconColor = imagecolorallocate($image, 59, 130, 246);
        
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
        
        $centerX = $width / 2;
        $centerY = $height / 2;
        $iconSize = 100;
        
        imagefilledrectangle(
            $image, 
            (int)($centerX - $iconSize/2), 
            (int)($centerY - $iconSize), 
            (int)($centerX + $iconSize/2), 
            (int)($centerY + $iconSize/2),
            $iconColor
        );
        
        $filename = pathinfo($pdfPath, PATHINFO_FILENAME);
        $text = "PDF Document";
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        
        imagestring($image, $font, (int)(($width - $textWidth) / 2), (int)($centerY + $iconSize), $text, $textColor);
        
        ob_start();
        imagejpeg($image, null, 90);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        if ($imageData === false || empty($imageData)) {
            throw new Exception('Failed to generate image data');
        }
        
        $imageFilename = $filename . '.jpg';
        $imagePath = 'pdf-images/' . $imageFilename;
        
        Storage::disk('public')->put($imagePath, $imageData);
        
        return $imagePath;
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
        return null;
    }
}
