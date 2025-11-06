<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfConverterService
{
    private const RESOLUTION = 150;
    private const COMPRESSION_QUALITY = 95;
    private const IMAGE_FORMAT = 'png';

    public function convertToImages(string $pdfPath): ?array
    {
        try {
            $this->ensureDirectoryExists('pdf-images');

            if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                Log::warning('Imagick extension not available');
                return null;
            }

            $this->configureGhostscript();

            $fullPdfPath = storage_path('app/public/' . $pdfPath);
            
            if (!file_exists($fullPdfPath)) {
                throw new \Exception('PDF file not found: ' . $fullPdfPath);
            }

            $baseName = pathinfo($pdfPath, PATHINFO_FILENAME);
            $uniqueId = uniqid();
            $outputPaths = [];

            $imagick = new \Imagick();
            $imagick->setResolution(self::RESOLUTION, self::RESOLUTION);
            $imagick->readImage($fullPdfPath);

            $numPages = $imagick->getNumberImages();
            Log::info("Converting PDF with {$numPages} page(s): {$pdfPath}");

            foreach ($imagick as $pageIndex => $page) {
                $page->setImageBackgroundColor('white');
                $flattened = $page->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

                $flattened->setImageFormat(self::IMAGE_FORMAT);
                $flattened->setImageCompressionQuality(self::COMPRESSION_QUALITY);
                $flattened->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);

                $imageFilename = "{$baseName}_{$uniqueId}_page{$pageIndex}." . self::IMAGE_FORMAT;
                $imagePath = "pdf-images/{$imageFilename}";
                $fullImagePath = storage_path("app/public/{$imagePath}");

                if (!$flattened->writeImage($fullImagePath)) {
                    throw new \Exception("Failed to save image for page {$pageIndex}");
                }

                if (!file_exists($fullImagePath) || filesize($fullImagePath) === 0) {
                    throw new \Exception("Empty image file for page {$pageIndex}");
                }

                $outputPaths[] = $imagePath;

                $flattened->clear();
                $flattened->destroy();
            }

            $imagick->clear();
            $imagick->destroy();

            Log::info("Successfully converted {$numPages} page(s) to images");
            return $outputPaths;

        } catch (\ImagickException $e) {
            Log::error('Imagick conversion error: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('PDF conversion failed: ' . $e->getMessage());
            return null;
        }
    }

    public function generatePlaceholder(string $pdfPath): ?string
    {
        try {
            $this->ensureDirectoryExists('pdf-images');

            $width = 1200;
            $height = 900;
            
            $image = imagecreatetruecolor($width, $height);
            
            if ($image === false) {
                throw new \Exception('Failed to create image resource');
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
            
            $text = "PDF Document";
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($text);
            
            imagestring($image, $font, (int)(($width - $textWidth) / 2), (int)($centerY + $iconSize), $text, $textColor);
            
            ob_start();
            imagepng($image, null, 9);
            $imageData = ob_get_clean();
            imagedestroy($image);
            
            if ($imageData === false || empty($imageData)) {
                throw new \Exception('Failed to generate image data');
            }
            
            $filename = pathinfo($pdfPath, PATHINFO_FILENAME);
            $imageFilename = $filename . '_placeholder.png';
            $imagePath = 'pdf-images/' . $imageFilename;
            
            Storage::disk('public')->put($imagePath, $imageData);
            
            return $imagePath;
        } catch (\Exception $e) {
            Log::error('Placeholder generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function ensureDirectoryExists(string $directory): void
    {
        try {
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
        } catch (\Exception $e) {
            Log::info("Directory {$directory} already exists or failed to create: " . $e->getMessage());
        }
    }

    public function deleteImages(string|array|null $imagePaths): void
    {
        if (empty($imagePaths)) {
            return;
        }

        $paths = is_string($imagePaths) ? json_decode($imagePaths, true) : $imagePaths;
        
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function configureGhostscript(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $gsBinPaths = [
                'C:\\Program Files\\gs\\gs10.06.0\\bin',
                'C:\\Program Files\\gs\\gs10.05.0\\bin',
                'C:\\Program Files\\gs\\gs10.04.0\\bin',
                'C:\\Program Files\\gs\\gs10.03.1\\bin',
                'C:\\Program Files\\gs\\gs10.03.0\\bin',
                'C:\\Program Files\\gs\\gs10.02.1\\bin',
                'C:\\Program Files\\gs\\gs10.02.0\\bin',
                'C:\\Program Files\\gs\\gs10.01.2\\bin',
                'C:\\Program Files\\gs\\gs10.01.1\\bin',
                'C:\\Program Files\\gs\\gs10.01.0\\bin',
                'C:\\Program Files\\gs\\gs10.00.0\\bin',
                'C:\\Program Files (x86)\\gs\\gs10.06.0\\bin',
                'C:\\Program Files (x86)\\gs\\gs10.05.0\\bin',
                'C:\\Program Files (x86)\\gs\\gs10.04.0\\bin',
                'C:\\Program Files (x86)\\gs\\gs10.03.1\\bin',
                'C:\\Program Files (x86)\\gs\\gs10.03.0\\bin',
            ];

            $foundBinPath = null;
            foreach ($gsBinPaths as $binPath) {
                if (is_dir($binPath)) {
                    $gswin64c = $binPath . '\\gswin64c.exe';
                    $gswin32c = $binPath . '\\gswin32c.exe';
                    
                    if (file_exists($gswin64c)) {
                        $foundBinPath = $binPath;
                        $gsExecutable = $gswin64c;
                        break;
                    } elseif (file_exists($gswin32c)) {
                        $foundBinPath = $binPath;
                        $gsExecutable = $gswin32c;
                        break;
                    }
                }
            }

            if (!$foundBinPath) {
                $dirs = glob('C:\\Program Files\\gs\\*', GLOB_ONLYDIR);
                if (!empty($dirs)) {
                    $latestVersion = end($dirs);
                    $binPath = $latestVersion . '\\bin';
                    
                    if (is_dir($binPath)) {
                        $gswin64c = $binPath . '\\gswin64c.exe';
                        $gswin32c = $binPath . '\\gswin32c.exe';
                        
                        if (file_exists($gswin64c)) {
                            $foundBinPath = $binPath;
                            $gsExecutable = $gswin64c;
                        } elseif (file_exists($gswin32c)) {
                            $foundBinPath = $binPath;
                            $gsExecutable = $gswin32c;
                        }
                    }
                }
            }

            if ($foundBinPath && isset($gsExecutable)) {
                putenv("MAGICK_GHOSTSCRIPT_PATH={$gsExecutable}");
                putenv("PATH=" . getenv("PATH") . ";{$foundBinPath}");
                
                $gsAlias = $foundBinPath . '\\gs.exe';
                if (!file_exists($gsAlias)) {
                    try {
                        @copy($gsExecutable, $gsAlias);
                        if (file_exists($gsAlias)) {
                            Log::info("Created gs.exe alias: {$gsAlias}");
                        }
                    } catch (\Exception $e) {
                        Log::warning("Could not create gs.exe alias (requires admin): " . $e->getMessage());
                    }
                }
                
                Log::info("Ghostscript configured: {$gsExecutable}");
                Log::info("Ghostscript bin added to PATH: {$foundBinPath}");
            } else {
                Log::warning('Ghostscript not found. PDF conversion may fail.');
                Log::warning('Please install Ghostscript from: https://www.ghostscript.com/');
            }
        }
    }
}
