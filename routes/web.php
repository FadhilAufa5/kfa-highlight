<?php

use App\Http\Controllers\PdfUploadController;
use App\Http\Controllers\UserController;
use App\Models\PdfUpload;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    $activeUploads = PdfUpload::with('user')
        ->where('is_active', true)
        ->orderBy('order')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($upload) {
            return [
                'id' => $upload->id,
                'title' => $upload->title,
                'image_path' => $upload->image_path,
                'pdf_path' => $upload->pdf_path,
                'conversion_status' => $upload->conversion_status ?? 'pending',
            ];
        });

    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'uploads' => $activeUploads,
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $totalUploads = PdfUpload::where('user_id', auth()->id())->count();
        $activeUploads = PdfUpload::where('user_id', auth()->id())->where('is_active', true)->count();
        $totalUsers = \App\Models\User::count();
        
        return Inertia::render('dashboard', [
            'stats' => [
                'totalUploads' => $totalUploads,
                'activeUploads' => $activeUploads,
                'totalUsers' => $totalUsers,
            ],
        ]);
    })->name('dashboard');

    Route::resource('uploads', PdfUploadController::class);
    Route::resource('users', UserController::class);
});

require __DIR__.'/settings.php';
