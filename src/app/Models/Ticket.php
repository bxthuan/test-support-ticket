<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'category',
        'sentiment',
        'suggested_reply',
        'ai_processed_at',
        'ai_retry_count',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'ai_processed_at' => 'datetime',
        'ai_retry_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'Open',
        'ai_retry_count' => 0,
    ];

    public function isAIProcessed(): bool
    {
        return !is_null($this->ai_processed_at);
    }

    public function scopeProcessed($query)
    {
        return $query->whereNotNull('ai_processed_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('ai_processed_at');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', TicketStatus::OPEN->value);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', TicketStatus::RESOLVED->value);
    }
}
