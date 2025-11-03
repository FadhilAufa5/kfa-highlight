<?php

namespace App\Http\Controllers;

use App\Models\PdfUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PdfUploadController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', PdfUpload::class);

        $uploads = PdfUpload::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('upload/index', [
            'uploads' => $uploads,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PdfUpload::class);

        return Inertia::render('upload/create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', PdfUpload::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $pdfFile = $request->file('pdf_file');
            $pdfPath = $pdfFile->store('pdfs', 'public');
            
            $fullPdfPath = storage_path('app/public/' . $pdfPath);

            $imagePath = null;
            $conversionMessage = '';

            // ✅ Pastikan folder pdf-images aman
            $this->ensureDirectoryExists('pdf-images');
            
            if (extension_loaded('imagick')) {
                try {
                    if (!file_exists($fullPdfPath)) {
                        throw new \Exception('PDF file not found after upload');
                    }

                    $imagick = new \Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($fullPdfPath . '[0]');
                    
                    $imagick->setImageFormat('jpg');
                    $imagick->setImageCompressionQuality(90);
                    $imagick->setImageBackgroundColor('white');
                    $imagick = $imagick->flattenImages();
                    
                    $imageFilename = pathinfo($pdfPath, PATHINFO_FILENAME) . '.jpg';
                    $imagePath = 'pdf-images/' . $imageFilename;

                    Storage::disk('public')->put($imagePath, $imagick->getImageBlob());
                    
                    $imagick->clear();
                    $imagick->destroy();
                    
                    $conversionMessage = ' Image preview generated successfully.';
                } catch (\Exception $e) {
                    \Log::error('PDF to Image conversion failed: ' . $e->getMessage());
                    $imagePath = $this->generatePlaceholderImage($pdfPath);
                    $conversionMessage = ' Note: Using placeholder image (Imagick error: ' . $e->getMessage() . ')';
                }
            } else {
                \Log::warning('Imagick extension not loaded - using placeholder');
                $imagePath = $this->generatePlaceholderImage($pdfPath);
                $conversionMessage = ' Note: Using placeholder image (Install Imagick for PDF preview)';
            }

            PdfUpload::where('user_id', auth()->id())
                ->where('is_active', true)
                ->update(['is_active' => false]);

            PdfUpload::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'original_filename' => $pdfFile->getClientOriginalName(),
                'pdf_path' => $pdfPath,
                'image_path' => $imagePath,
                'is_active' => true,
            ]);

            return redirect()->route('uploads.index')
                ->with('success', 'PDF uploaded successfully!' . $conversionMessage);
        } catch (\Exception $e) {
            \Log::error('PDF upload failed: ' . $e->getMessage());
            
            if (isset($pdfPath) && Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->delete($pdfPath);
            }
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['pdf_file' => 'Failed to upload PDF: ' . $e->getMessage()]);
        }
    }

    public function edit(PdfUpload $upload): Response
    {
        $this->authorize('update', $upload);

        return Inertia::render('upload/edit', [
            'upload' => $upload,
        ]);
    }

    public function update(Request $request, PdfUpload $upload)
    {
        $this->authorize('update', $upload);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        if (isset($validated['is_active']) && $validated['is_active'] === true) {
            PdfUpload::where('user_id', auth()->id())
                ->where('id', '!=', $upload->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $upload->update($validated);

        return redirect()->route('uploads.index')
            ->with('success', 'PDF updated successfully!');
    }

    public function destroy(PdfUpload $upload)
    {
        $this->authorize('delete', $upload);

        try {
            if ($upload->pdf_path && Storage::disk('public')->exists($upload->pdf_path)) {
                Storage::disk('public')->delete($upload->pdf_path);
            }
            if ($upload->image_path && Storage::disk('public')->exists($upload->image_path)) {
                Storage::disk('public')->delete($upload->image_path);
            }

            $upload->delete();

            return redirect()->route('uploads.index')
                ->with('success', 'PDF deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('PDF deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete PDF: ' . $e->getMessage()]);
        }
    }

    private function generatePlaceholderImage($pdfPath)
    {
        try {
            // ✅ Pastikan folder aman
            $this->ensureDirectoryExists('pdf-images');

            $width = 1200;
            $height = 900;
            
            $image = imagecreatetruecolor($width, $height);
            
            $bgColor = imagecolorallocate($image, 243, 244, 246);
            $textColor = imagecolorallocate($image, 75, 85, 99);
            $iconColor = imagecolorallocate($image, 59, 130, 246);
            
            imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
            
            $centerX = $width / 2;
            $centerY = $height / 2;
            $iconSize = 100;
            
            imagefilledrectangle(
                $image, 
                $centerX - $iconSize/2, 
                $centerY - $iconSize, 
                $centerX + $iconSize/2, 
                $centerY + $iconSize/2,
                $iconColor
            );
            
            $filename = pathinfo($pdfPath, PATHINFO_FILENAME);
            $text = "PDF Document";
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($text);
            
            imagestring($image, $font, ($width - $textWidth) / 2, $centerY + $iconSize, $text, $textColor);
            
            ob_start();
            imagejpeg($image, null, 90);
            $imageData = ob_get_clean();
            imagedestroy($image);
            
            $imageFilename = $filename . '.jpg';
            $imagePath = 'pdf-images/' . $imageFilename;
            
            Storage::disk('public')->put($imagePath, $imageData);
            
            return $imagePath;
        } catch (\Exception $e) {
            \Log::error('Placeholder generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ Pastikan folder aman untuk digunakan tanpa error mkdir()
     */
    private function ensureDirectoryExists(string $directory): void
    {
        try {
            if (method_exists(Storage::disk('public'), 'directoryExists')) {
                // Laravel 9+
                if (!Storage::disk('public')->directoryExists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
            } else {
                // Laravel versi lama
                if (!is_dir(storage_path('app/public/' . $directory))) {
                    @mkdir(storage_path('app/public/' . $directory), 0755, true);
                }
            }
        } catch (\Exception $e) {
            \Log::info("Directory {$directory} already exists or failed to create: " . $e->getMessage());
        }
    }
}
