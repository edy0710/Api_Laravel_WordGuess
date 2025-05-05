<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relación con categorías completadas
     */
    public function completedCategories()
    {
        return $this->belongsToMany(Category::class, 'user_completed_categories')
                   ->withTimestamps()
                   ->withPivot(['created_at', 'updated_at']);
    }

    /**
     * Historial de palabras del usuario
     */
    public function wordHistory()
    {
        return $this->hasMany(UserWordHistory::class)
                   ->with(['word' => function($query) {
                       $query->with('category');
                   }]);
    }

    /**
     * Palabras falladas por el usuario
     */
    public function failedWords()
    {
        return $this->wordHistory()
                   ->where('answered_correctly', false)
                   ->orderBy('times_shown', 'desc');
    }

    /**
     * Posición en el ranking global
     */
    public function getRankingPositionAttribute()
    {
        return self::where('points', '>', $this->points)
                 ->count() + 1;
    }

    /**
     * Porcentaje de categorías completadas
     */
    public function getCompletionPercentageAttribute()
    {
        $totalCategories = Category::count();
        return $totalCategories > 0 
            ? round(($this->completedCategories()->count() / $totalCategories) * 100)
            : 0;
    }

    /**
     * Palabras difíciles (falladas más de 2 veces)
     */
    public function difficultWords($limit = 5)
    {
        return $this->wordHistory()
                   ->where('answered_correctly', false)
                   ->where('times_shown', '>=', 2)
                   ->with('word.category')
                   ->orderBy('times_shown', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Progreso en una categoría específica
     */
    public function categoryProgress($categoryId)
    {
        $totalWords = Word::where('category_id', $categoryId)->count();
        $correctWords = $this->wordHistory()
                            ->whereHas('word', function($query) use ($categoryId) {
                                $query->where('category_id', $categoryId);
                            })
                            ->where('answered_correctly', true)
                            ->count();

        return $totalWords > 0 ? round(($correctWords / $totalWords) * 100) : 0;
    }
}