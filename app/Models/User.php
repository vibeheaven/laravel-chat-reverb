<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = ['avatar_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Kullanıcının katıldığı konuşmalar
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Kullanıcının gönderdiği mesajlar
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Kullanıcının avatar URL'si (ui-avatars.com)
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&size=200&background=random"
        );
    }

    /**
     * İki kullanıcı arasında private konuşma var mı kontrol et
     */
    public function getPrivateConversationWith($userId)
    {
        return Conversation::where('type', 'private')
            ->whereHas('members', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
    }
}
