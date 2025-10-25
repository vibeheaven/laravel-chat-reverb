<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DosyaCommandController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\MentionController;
use App\Http\Controllers\MessageController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Kullanıcı listesi (yeni konuşma başlatmak için)
    Route::get('/users', function () {
        $users = User::whereNot('id', auth()->id())
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url,
                ];
            });
        return response()->json($users);
    });

    // Konuşmalar
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::put('/conversations/{conversation}', [ConversationController::class, 'update']);
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy']);
    
    // Grup üyeleri
    Route::post('/conversations/{conversation}/members', [ConversationController::class, 'addMember']);
    Route::delete('/conversations/{conversation}/members/{user}', [ConversationController::class, 'removeMember']);
    Route::put('/conversations/{conversation}/members/{user}/role', [ConversationController::class, 'updateMemberRole']);

    // Mesajlar
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::put('/messages/{message}/status', [MessageController::class, 'updateStatus']);
    Route::post('/conversations/{conversation}/mark-all-read', [MessageController::class, 'markAllAsRead']);
    
    // Mention sistemi
    Route::get('/conversations/{conversation}/mentionable-users', [MentionController::class, 'getMentionableUsers']);
    Route::post('/conversations/{conversation}/mention', [MentionController::class, 'sendMentionMessage']);
    
    // Dosya komutları
    Route::get('/dosya/search', [DosyaCommandController::class, 'searchDosya']);
    Route::post('/conversations/{conversation}/dosya', [DosyaCommandController::class, 'sendDosyaToChat']);
    
    // Dosya yükleme
    Route::post('/conversations/{conversation}/upload', [FileUploadController::class, 'upload']);
    Route::get('/messages/{message}/download', [FileUploadController::class, 'download']);
});

require __DIR__ . '/auth.php';
