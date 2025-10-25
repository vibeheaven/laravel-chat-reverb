<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'message_id',
        'user_id',
        'status',
        'updated_at'
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /**
     * Mesaj ilişkisi
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Kullanıcı ilişkisi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

