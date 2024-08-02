<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'is_user', 'user_id'];

    // define a relationship to User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
