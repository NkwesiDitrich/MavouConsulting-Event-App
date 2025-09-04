<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'sender_id',
        'subject',
        'message',
        'recipient_type',
        'recipient_ids',
        'sent_at'
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'sent_at' => 'datetime'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

