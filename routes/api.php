<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ChatbotController;
use App\Http\Controllers\API\LawController;
use App\Http\Controllers\API\SignatureController;
use App\Http\Controllers\API\DocumentController;
use App\Events\LawsUpdated;
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

Route::get('/test-broadcast', function () {
    $laws = [
        ['title' => 'Zakon o pečatu institucija BiH', 'url' => 'http://www.mpr.gov.ba/web_dokumenti/Zakon%20o%20pecatu%20institucija%20BiH%20-%2012-98_bs.pdf'],
        ['title' => 'Zakon o izmjenama Zakona o pečatu', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20izmjenama%20Zakona%20o%20pecatu%20-%2014-03.pdf'],
        ['title' => 'Zakon o zastavi BiH', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20zastavi%20BiH%20-%2019%20-%2001.pdf'],
        ['title' => 'Zakon o pravobranilaštvu', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20pravobranilastvu%20-%208%20-%2002.pdf'],
        ['title' => 'Zakon o slobodi pristupa informacijama', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20slobodi%20pristupa.pdf'],
    ];

    broadcast(new LawsUpdated($laws));

    return response()->json(['status' => 'event broadcasted']);
});
