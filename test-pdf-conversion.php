<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing PDF Conversion\n";
echo "==========================================\n\n";

// Configure Ghostscript
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $gsPath = 'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64.exe';
    if (file_exists($gsPath)) {
        putenv("MAGICK_GHOSTSCRIPT_PATH={$gsPath}");
        echo "✓ Ghostscript configured: {$gsPath}\n\n";
    }
}

// Find a PDF file in storage
$pdfFiles = glob(storage_path('app/public/pdfs/*.pdf'));

if (empty($pdfFiles)) {
    echo "✗ No PDF files found in storage/app/public/pdfs/\n";
    echo "  Please upload a PDF file first via the web interface.\n";
    exit(1);
}

$testPdf = $pdfFiles[0];
echo "Testing with: " . basename($testPdf) . "\n";
echo "Full path: {$testPdf}\n\n";

try {
    echo "1. Creating Imagick instance...\n";
    $imagick = new Imagick();
    $imagick->setResolution(150, 150);
    
    echo "2. Reading PDF...\n";
    $imagick->readImage($testPdf);
    
    $numPages = $imagick->getNumberImages();
    echo "   ✓ PDF loaded successfully\n";
    echo "   ✓ Number of pages: {$numPages}\n\n";
    
    echo "3. Converting first page...\n";
    $imagick->setIteratorIndex(0);
    $imagick->setImageBackgroundColor('white');
    $flattened = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
    $flattened->setImageFormat('png');
    $flattened->setImageCompressionQuality(95);
    $flattened->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
    
    $testOutput = storage_path('app/public/test-conversion.png');
    $flattened->writeImage($testOutput);
    
    if (file_exists($testOutput) && filesize($testOutput) > 0) {
        echo "   ✓ Conversion successful!\n";
        echo "   ✓ Output: {$testOutput}\n";
        echo "   ✓ File size: " . number_format(filesize($testOutput) / 1024, 2) . " KB\n";
        
        // Cleanup
        @unlink($testOutput);
        echo "   ✓ Test file cleaned up\n";
    } else {
        echo "   ✗ Conversion failed - output file empty or missing\n";
    }
    
    $flattened->clear();
    $flattened->destroy();
    $imagick->clear();
    $imagick->destroy();
    
    echo "\n✓ PDF conversion test PASSED\n";
    
} catch (ImagickException $e) {
    echo "\n✗ Imagick Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n==========================================\n";
echo "Test Complete - System Ready!\n";
