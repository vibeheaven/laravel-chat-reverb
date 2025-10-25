<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'type',
        'created_by'
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Konuşmaya ait mesajlar
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Konuşmaya ait son mesaj
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Konuşmanın üyeleri
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Konuşmayı oluşturan kullanıcı
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Belirli bir kullanıcı için okunmamış mesaj sayısı
     */
    public function unreadMessagesCount($userId)
    {
        return Message::where('conversation_id', $this->id)
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('statuses', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'read');
            })
            ->count();
    }

    /**
     * Bir kullanıcının konuşmadaki rolünü kontrol et
     */
    public function isAdmin($userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->wherePivot('role', 1)
            ->exists();
    }

    /**
     * Grup resmi URL'si veya varsayılan avatar
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->image) {
                    return asset('storage/' . $this->image);
                }

                // Grup için varsayılan avatar
                if ($this->type === 'group') {
                    $name = urlencode($this->name ?? 'Group');
                    return "https://ui-avatars.com/api/?name={$name}&size=200&background=0D8ABC&color=fff";
                }

                return '';
            }
        );
    }

    /**
     * Private konuşma için diğer kullanıcıyı al
     */
    public function getOtherUser($currentUserId)
    {
        if ($this->type === 'private') {
            return $this->members()->where('user_id', '!=', $currentUserId)->first();
        }
        return null;
    }
}

