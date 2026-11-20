<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $role
 * @property string $content
 */
class ChatMessage extends Model
{
    protected $fillable = [
        'role',
        'content',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }
}
