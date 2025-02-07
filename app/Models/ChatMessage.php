<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id', // Add this to allow mass assignment
        'user_id',         // Allows mass assignment
        'role',            // 'user' or 'assistant'
        'message',         // The message content
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
