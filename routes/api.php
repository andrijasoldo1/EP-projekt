<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ChatbotController;
use App\Http\Controllers\API\LawController;
use App\Http\Controllers\API\SignatureController;
use App\Http\Controllers\API\DocumentController;
use Illuminate\Support\Facades\Route;

// **Authentication Routes (Sanctum)**
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// **Authenticated User Routes (Require Sanctum Token)**
Route::middleware('auth:sanctum')->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});

// **Chatbot Routes**
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/conversations', [ChatbotController::class, 'getConversations']);
    Route::get('/conversations/{conversationId}/history', [ChatbotController::class, 'getChatHistory']);
    Route::post('/conversations', [ChatbotController::class, 'createConversation']);
    Route::post('/conversations/{conversationId}/ask', [ChatbotController::class, 'ask']);
});

// **Law Routes**
Route::get('/laws', [LawController::class, 'index']);

// **Personal Signature Routes**
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/signature', [SignatureController::class, 'show']);  // View personal signature
    Route::post('/signature', [SignatureController::class, 'save']); // Save personal signature
});

// **Document Signing & Generation Routes**
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/signature/document', [DocumentController::class, 'saveSignature']); // Save signature for documents
    Route::get('/conversations/{conversationId}/generate-word', [DocumentController::class, 'generateWord']); // Generate Word document
    Route::get('/conversations/{conversationId}/generate-signed-pdf', [DocumentController::class, 'generateSignedPdf']); // Generate signed PDF
    Route::get('/verify-document/{conversationId}', [DocumentController::class, 'verifyDocument']); // Verify document authenticity
});
