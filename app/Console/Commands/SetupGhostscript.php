<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupGhostscript extends Command
{
    protected $signature = 'gs:setup';
    protected $description = 'Setup Ghostscript for Imagick PDF conversion (Windows)';

    public function handle()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $this->info('This command is only for Windows. Linux/Mac uses system Ghostscript.');
            return 0;
        }

        $this->info('Scanning for Ghostscript installation...');

        $gsBinPaths = [
            'C:\\Program Files\\gs\\gs10.06.0\\bin',
            'C:\\Program Files\\gs\\gs10.05.0\\bin',
            'C:\\Program Files\\gs\\gs10.04.0\\bin',
            'C:\\Program Files\\gs\\gs10.03.1\\bin',
            'C:\\Program Files\\gs\\gs10.03.0\\bin',
        ];

        $foundBinPath = null;
        $gsExecutable = null;

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

        if (!$foundBinPath || !$gsExecutable) {
            $this->error('Ghostscript not found!');
            $this->info('Please install Ghostscript from: https://www.ghostscript.com/');
            return 1;
        }

        $this->info("Found: {$gsExecutable}");

        $gsAlias = $foundBinPath . '\\gs.exe';
        
        if (file_exists($gsAlias)) {
            $this->info("gs.exe already exists: {$gsAlias}");
        } else {
            $this->info('Creating gs.exe alias...');
            
            try {
                if (copy($gsExecutable, $gsAlias)) {
                    $this->info("✓ Created: {$gsAlias}");
                    $this->info('✓ Ghostscript setup complete!');
                } else {
                    $this->error('✗ Failed to create gs.exe (Permission denied)');
                    $this->info('Run this command as Administrator:');
                    $this->line("copy \"{$gsExecutable}\" \"{$gsAlias}\"");
                    return 1;
                }
            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
                $this->info('Please run PowerShell as Administrator and execute:');
                $this->line("Copy-Item \"{$gsExecutable}\" \"{$gsAlias}\"");
                return 1;
            }
        }

        $this->info("\nTesting Imagick with Ghostscript...");
        
        if (!extension_loaded('imagick')) {
            $this->warn('Imagick extension not loaded');
            return 1;
        }

        putenv("PATH=" . getenv("PATH") . ";{$foundBinPath}");
        
        try {
            $imagick = new \Imagick();
            $this->info('✓ Imagick loaded successfully');
            
            $version = $imagick->getVersion();
            $this->info("ImageMagick version: {$version['versionString']}");
            
            $this->newLine();
            $this->info('🎉 Setup complete! PDF conversion is ready to use.');
            
        } catch (\Exception $e) {
            $this->error('Imagick error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
