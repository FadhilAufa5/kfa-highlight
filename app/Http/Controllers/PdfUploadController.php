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
        return Inertia::render('upload/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $pdfFile = $request->file('pdf_file');
        $pdfPath = $pdfFile->store('pdfs', 'public');

        $imagePath = null;
        if (class_exists(\Imagick::class)) {
            try {
                $imagick = new \Imagick(storage_path('app/public/' . $pdfPath) . '[0]');
                $imagick->setImageFormat('jpg');
                $imagick->setImageCompressionQuality(90);
                
                $imageFilename = pathinfo($pdfPath, PATHINFO_FILENAME) . '.jpg';
                $imagePath = 'pdf-images/' . $imageFilename;
                
                Storage::disk('public')->put($imagePath, $imagick->getImageBlob());
                $imagick->clear();
            } catch (\Exception $e) {
            }
        }

        PdfUpload::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'original_filename' => $pdfFile->getClientOriginalName(),
            'pdf_path' => $pdfPath,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('uploads.index')
            ->with('success', 'PDF uploaded successfully!');
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
            'order' => 'integer',
        ]);

        $upload->update($validated);

        return redirect()->route('uploads.index')
            ->with('success', 'PDF updated successfully!');
    }

    public function destroy(PdfUpload $upload)
    {
        $this->authorize('delete', $upload);

        if ($upload->pdf_path) {
            Storage::disk('public')->delete($upload->pdf_path);
        }
        if ($upload->image_path) {
            Storage::disk('public')->delete($upload->image_path);
        }

        $upload->delete();

        return redirect()->route('uploads.index')
            ->with('success', 'PDF deleted successfully!');
    }
}
