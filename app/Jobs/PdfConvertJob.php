<?php

namespace App\Jobs;

use App\Models\PdfUpload;
use App\Services\PdfConverterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PdfConvertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public function __construct(
        public int $uploadId
    ) {}

    public function handle(PdfConverterService $converter): void
    {
        $upload = PdfUpload::find($this->uploadId);

        if (!$upload) {
            Log::warning("PDF upload not found: {$this->uploadId}");
            return;
        }

        $upload->update(['conversion_status' => 'processing']);
        Log::info("Starting PDF conversion: {$upload->pdf_path}");

        $imagePaths = $converter->convertToImages($upload->pdf_path);

        if (!$imagePaths) {
            Log::warning("Imagick conversion failed, generating placeholder");
            $placeholderPath = $converter->generatePlaceholder($upload->pdf_path);
            
            if ($placeholderPath) {
                $imagePaths = [$placeholderPath];
            }
        }

        if ($imagePaths) {
            $upload->update([
                'image_path' => $imagePaths,
                'conversion_status' => 'completed',
            ]);
            Log::info("PDF conversion completed: " . count($imagePaths) . " image(s) created");
        } else {
            $upload->update(['conversion_status' => 'failed']);
            Log::error("PDF conversion failed completely: {$upload->pdf_path}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        $upload = PdfUpload::find($this->uploadId);
        
        if ($upload) {
            $upload->update(['conversion_status' => 'failed']);
        }

        Log::error("PDF conversion job failed: " . $exception->getMessage());
    }
}
