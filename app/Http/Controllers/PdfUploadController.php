<?php

namespace App\Http\Controllers;

use App\Models\PdfUpload;
use App\Jobs\PdfConvertJob;
use App\Services\PdfConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

            PdfUpload::where('user_id', auth()->id())
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $upload = PdfUpload::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'original_filename' => $pdfFile->getClientOriginalName(),
                'pdf_path' => $pdfPath,
                'image_path' => null,
                'is_active' => true,
                'conversion_status' => 'pending',
            ]);

            PdfConvertJob::dispatch($upload->id);

            return redirect()->route('uploads.index')
                ->with('success', 'PDF uploaded successfully! Conversion in progress...');
        } catch (\Exception $e) {
            Log::error('PDF upload failed: ' . $e->getMessage());
            
            if (isset($pdfPath) && Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->delete($pdfPath);
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

    public function destroy(PdfUpload $upload, PdfConverterService $converter)
    {
        $this->authorize('delete', $upload);

        try {
            if ($upload->pdf_path && Storage::disk('public')->exists($upload->pdf_path)) {
                Storage::disk('public')->delete($upload->pdf_path);
            }

            $converter->deleteImages($upload->image_path);

            $upload->delete();

            return redirect()->route('uploads.index')
                ->with('success', 'PDF deleted successfully!');
        } catch (\Exception $e) {
            Log::error('PDF deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete PDF: ' . $e->getMessage()]);
        }
    }


}
