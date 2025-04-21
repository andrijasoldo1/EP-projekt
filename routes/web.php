<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\LawController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Chatbot Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/chatbot/{conversationId?}', [ChatbotController::class, 'showChatbot'])->name('chatbot.view');
    Route::post('/chatbot/{conversationId}/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');
    Route::post('/conversation/create', [ChatbotController::class, 'createConversation'])->name('conversation.create');

    // Document Generation Routes
    Route::get('/conversation/{conversationId}/generate-word', [DocumentController::class, 'generateWord'])
        ->name('document.generateWord');
    Route::get('/conversation/{conversationId}/generate-pdf', [DocumentController::class, 'generatePdf'])
        ->name('document.generatePdf');
    Route::get('/conversation/{conversationId}/generate-signed-pdf', [DocumentController::class, 'generateSignedPdf'])
        ->name('document.generateSignedPdf');
    Route::get('/conversation/{conversationId}/preview-pdf', [DocumentController::class, 'previewPdf'])
        ->name('document.previewPdf');
    Route::get('/verify-document/{conversationId}', [DocumentController::class, 'verifyDocument'])
        ->name('document.verify');
    Route::get('/documents', [DocumentController::class, 'listDocuments'])
        ->name('document.list');
});
Route::get('/signature', [SignatureController::class, 'show'])->name('signature.show');
Route::post('/save-signature', [SignatureController::class, 'save'])->name('saveSignature');

// Profile Management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/laws', [LawController::class, 'index'])->name('laws.index');

require __DIR__.'/auth.php';
