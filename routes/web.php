<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DigitalSignatureController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    return view('home');
})->name('home');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home.redirect');
    Route::get('/dashboard', [\App\Http\Controllers\PageController::class, 'index'])->name('dashboard');
    Route::get('/logout', function () {
        Auth::logout();
        return redirect()->route('home');
    })->name('logout');

    Route::resource('user', \App\Http\Controllers\UserController::class)
        ->except(['show', 'edit', 'create'])
        ->middleware(['role:admin']);

// Digital Signature Management Routes
    Route::prefix('digital-signatures')->name('digital-signatures.')->group(function () {
        Route::get('/', [DigitalSignatureController::class, 'index'])->name('index');
        Route::get('/create', [DigitalSignatureController::class, 'create'])->name('create');
        Route::post('/', [DigitalSignatureController::class, 'store'])->name('store');
        Route::get('/{digitalSignature}', [DigitalSignatureController::class, 'show'])->name('show');
        Route::get('/{digitalSignature}/download', [DigitalSignatureController::class, 'download'])->name('download');
        Route::get('/{digitalSignature}/certificate', [DigitalSignatureController::class, 'generateCertificate'])->name('certificate');
        Route::patch('/{digitalSignature}/revoke', [DigitalSignatureController::class, 'revoke'])->name('revoke');
    });

    // Profile and Settings Routes
    Route::get('profile', [\App\Http\Controllers\PageController::class, 'profile'])
        ->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\PageController::class, 'profileUpdate'])
        ->name('profile.update');
    Route::put('profile/deactivate', [\App\Http\Controllers\PageController::class, 'deactivate'])
        ->name('profile.deactivate')
        ->middleware(['role:staff']);

    Route::get('settings', [\App\Http\Controllers\PageController::class, 'settings'])
        ->name('settings.show')
        ->middleware(['role:admin']);
    Route::put('settings', [\App\Http\Controllers\PageController::class, 'settingsUpdate'])
        ->name('settings.update')
        ->middleware(['role:admin']);

    // Attachment Removal Route
    Route::delete('attachment', [\App\Http\Controllers\PageController::class, 'removeAttachment'])
        ->name('attachment.destroy');
    // Transaction Routes
    Route::prefix('transaction')->as('transaction.')->group(function () {
        Route::resource('incoming', \App\Http\Controllers\IncomingLetterController::class);
        Route::resource('outgoing', \App\Http\Controllers\OutgoingLetterController::class);
        Route::resource('{letter}/disposition', \App\Http\Controllers\DispositionController::class)->except(['show']);
    });
    // Agenda Routes
    Route::prefix('agenda')->as('agenda.')->group(function () {
        Route::get('incoming', [\App\Http\Controllers\IncomingLetterController::class, 'agenda'])->name('incoming');
        Route::get('incoming/print', [\App\Http\Controllers\IncomingLetterController::class, 'print'])->name('incoming.print');
        Route::get('outgoing', [\App\Http\Controllers\OutgoingLetterController::class, 'agenda'])->name('outgoing');
        Route::get('outgoing/print', [\App\Http\Controllers\OutgoingLetterController::class, 'print'])->name('outgoing.print');
    });
    // Gallery Routes
    Route::prefix('gallery')->as('gallery.')->group(function () {
        Route::get('incoming', [\App\Http\Controllers\LetterGalleryController::class, 'incoming'])->name('incoming');
        Route::get('outgoing', [\App\Http\Controllers\LetterGalleryController::class, 'outgoing'])->name('outgoing');
    });
    // Reference Data Routes
    Route::prefix('reference')->as('reference.')->middleware(['role:admin'])->group(function () {
        Route::resource('classification', \App\Http\Controllers\ClassificationController::class)->except(['show', 'create', 'edit']);
        Route::resource('status', \App\Http\Controllers\LetterStatusController::class)->except(['show', 'create', 'edit']);
    });
});

// Public verification routes (No authentication required)
Route::get('/verify-signature/{hash}', [DigitalSignatureController::class, 'verify'])->name('signature.verify');

// API Routes for verification
Route::prefix('api/v1')->group(function () {
    Route::get('/verify-signature/{hash}', [DigitalSignatureController::class, 'apiVerify'])->name('api.signature.verify');
});
