<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWordHistory extends Model
{
    use HasFactory;

    protected $table = 'user_word_history'; // Añade esta línea

    protected $fillable = ['user_id', 'word_id', 'answered_correctly', 'times_shown'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function word()
    {
        return $this->belongsTo(Word::class);
    }
}