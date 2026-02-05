<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'client_user_id',
        'company_id',
        'type',
        'title',
        'description',
        'message',
        'read_at',
        'action_url',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the Company that owns this notification.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the route key name.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): self
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }

        return $this;
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): self
    {
        $this->update(['read_at' => null]);

        return $this;
    }

    /**
     * Scope: get only unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: get only read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: recent first.
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }
}
