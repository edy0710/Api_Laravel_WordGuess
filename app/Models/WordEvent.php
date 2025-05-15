<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordEvent extends Model
{
    protected $fillable = [
        'word_id', 
        'event_type', 
        'is_correct',
        'user_ip'
    ];
    
    public function word()
    {
        return $this->belongsTo(Word::class);
    }
}
