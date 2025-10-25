<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'text',
        'type',
        'metadata',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $with = ['sender', 'statuses'];

    protected $appends = ['overall_status'];

    /**
     * Mesajın ait olduğu konuşma
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Mesajı gönderen kullanıcı
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Mesajın durumları
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(MessageStatus::class);
    }

    /**
     * Belirli bir kullanıcı için mesaj durumunu al
     */
    public function getStatusForUser($userId): ?MessageStatus
    {
        return $this->statuses()->where('user_id', $userId)->first();
    }

    /**
     * Mesajın genel durumunu belirle (grup sohbetleri için)
     */
    protected function overallStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->conversation->type === 'private') {
                    $status = $this->statuses()->where('user_id', '!=', $this->sender_id)->first();
                    return $status ? $status->status : 'pending';
                }

                // Grup sohbeti için
                $totalMembers = $this->conversation->members()
                    ->where('user_id', '!=', $this->sender_id)
                    ->count();

                if ($totalMembers === 0) {
                    return 'delivered';
                }

                $readCount = $this->statuses()->where('status', 'read')->count();
                $deliveredCount = $this->statuses()->where('status', 'delivered')->count();

                // Herkes okuduysa
                if ($readCount === $totalMembers) {
                    return 'read';
                }

                // Herkese ulaştıysa
                if (($readCount + $deliveredCount) === $totalMembers) {
                    return 'delivered';
                }

                return 'pending';
            }
        );
    }
}

