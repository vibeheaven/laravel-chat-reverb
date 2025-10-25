<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'joined_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Konuşma ilişkisi
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Kullanıcı ilişkisi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kullanıcının yönetici olup olmadığını kontrol et
     */
    public function isAdmin(): bool
    {
        return $this->role === 1;
    }
}

