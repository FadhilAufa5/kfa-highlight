<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Ghostscript Auto-Configuration\n";
echo "==========================================\n\n";

echo "1. Checking OS: ";
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "Windows ✓\n\n";
} else {
    echo "Not Windows (Linux/Mac)\n";
    exit;
}

echo "2. Scanning for Ghostscript installations...\n";

$possiblePaths = [
    'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64.exe',
    'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64c.exe',
    'C:\\Program Files\\gs\\gs10.05.0\\bin\\gswin64.exe',
    'C:\\Program Files\\gs\\gs10.05.0\\bin\\gswin64c.exe',
    'C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64.exe',
    'C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe',
    'C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64.exe',
    'C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe',
];

$found = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        echo "   ✓ Found: {$path}\n";
        if (!$found) {
            $found = $path;
        }
    }
}

if (!$found) {
    echo "   ✗ No predefined paths found\n";
    echo "\n3. Trying auto-detection...\n";
    
    $dirs = glob('C:\\Program Files\\gs\\*', GLOB_ONLYDIR);
    if (!empty($dirs)) {
        $latestVersion = end($dirs);
        echo "   Latest version folder: {$latestVersion}\n";
        
        $possibleExes = [
            $latestVersion . '\\bin\\gswin64.exe',
            $latestVersion . '\\bin\\gswin64c.exe',
            $latestVersion . '\\bin\\gswin32.exe',
            $latestVersion . '\\bin\\gswin32c.exe',
        ];
        
        foreach ($possibleExes as $exe) {
            if (file_exists($exe)) {
                echo "   ✓ Found: {$exe}\n";
                $found = $exe;
                break;
            }
        }
    }
}

echo "\n4. Configuration Result:\n";
if ($found) {
    echo "   ✓ Ghostscript Path: {$found}\n";
    putenv("MAGICK_GHOSTSCRIPT_PATH={$found}");
    echo "   ✓ Environment variable set\n";
    
    echo "\n5. Testing Imagick with Ghostscript:\n";
    if (extension_loaded('imagick')) {
        echo "   ✓ Imagick extension loaded\n";
        
        try {
            $imagick = new Imagick();
            echo "   ✓ Imagick instance created\n";
            
            echo "\n6. Imagick Configuration:\n";
            $version = $imagick->getVersion();
            echo "   Version: {$version['versionString']}\n";
            
        } catch (Exception $e) {
            echo "   ✗ Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ✗ Imagick extension not loaded\n";
    }
} else {
    echo "   ✗ Ghostscript NOT found\n";
    echo "\n   Please install Ghostscript from:\n";
    echo "   https://www.ghostscript.com/releases/gsdnld.html\n";
}

echo "\n==========================================\n";
echo "Test Complete\n";
