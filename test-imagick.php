<?php

/**
 * Test Imagick Installation and PDF Conversion
 * 
 * Usage: php test-imagick.php [path-to-pdf-file]
 */

// Check if Imagick is loaded
echo "=== Imagick Installation Check ===\n\n";

if (!extension_loaded('imagick')) {
    echo "❌ Imagick extension is NOT loaded!\n";
    echo "Please follow IMAGICK_INSTALLATION.md to install Imagick.\n";
    exit(1);
}

echo "✅ Imagick extension is loaded!\n\n";

// Display Imagick version
$imagick = new Imagick();
$version = $imagick->getVersion();
echo "Imagick Version: " . $version['versionString'] . "\n\n";

// Check supported formats
echo "=== Checking PDF Support ===\n\n";
$formats = $imagick->queryFormats();
$pdfSupported = in_array('PDF', $formats);

if ($pdfSupported) {
    echo "✅ PDF format is supported!\n\n";
} else {
    echo "❌ PDF format is NOT supported!\n";
    echo "You may need to install Ghostscript.\n\n";
}

// Test PDF conversion if file provided
if ($argc > 1) {
    $pdfPath = $argv[1];
    
    echo "=== Testing PDF Conversion ===\n\n";
    
    if (!file_exists($pdfPath)) {
        echo "❌ File not found: {$pdfPath}\n";
        exit(1);
    }
    
    echo "Input PDF: {$pdfPath}\n";
    
    try {
        // Create output path
        $outputPath = dirname($pdfPath) . '/' . pathinfo($pdfPath, PATHINFO_FILENAME) . '_test.jpg';
        
        // Convert PDF to image
        $imagick = new Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage($pdfPath . '[0]'); // First page only
        $imagick->setImageFormat('jpg');
        $imagick->setImageCompressionQuality(90);
        $imagick->setImageBackgroundColor('white');
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        $imagick->writeImage($outputPath);
        $imagick->clear();
        $imagick->destroy();
        
        echo "✅ Conversion successful!\n";
        echo "Output image: {$outputPath}\n";
        echo "File size: " . number_format(filesize($outputPath) / 1024, 2) . " KB\n";
        
    } catch (Exception $e) {
        echo "❌ Conversion failed!\n";
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
    
} else {
    echo "=== Usage ===\n\n";
    echo "To test PDF conversion, run:\n";
    echo "php test-imagick.php path/to/your/file.pdf\n";
}

echo "\n=== Test Complete ===\n";
