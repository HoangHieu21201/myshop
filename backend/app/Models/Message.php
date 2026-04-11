<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'is_read',
    ];

    // Người gửi là ai?
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Người nhận là ai?
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}