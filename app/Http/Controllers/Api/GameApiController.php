<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Word;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;

class GameApiController extends Controller
{
    protected $gameKey = 'api_game_data';

    public function startGame($categoryId)
    {
        // Buscar categoría
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        // Obtener 10 palabras aleatorias de la categoría
        $words = Word::where('category_id', $categoryId)
            ->with('options')
            ->inRandomOrder()
            ->take(10)
            ->get();

        if ($words->isEmpty()) {
            return response()->json(['error' => 'No hay suficientes palabras en esta categoría'], 400);
        }

        // Guardar progreso en sesión
        Session::put($this->gameKey, [
            'category_id' => $categoryId,
            'words' => $words->map(function ($word) {
                return [
                    'id' => $word->id,
                    'word' => $word->word,
                    'correct_meaning' => $word->correct_meaning,
                    'options' => $word->options->pluck('option_text')->toArray()
                ];
            })->toArray(),
            'answered' => [],
            'score' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Juego iniciado correctamente',
            'data' => [
                'total_questions' => count($words),
                'category' => ['id' => $categoryId, 'name' => $category->name],
                'first_question' => [
                    'word' => $words[0]['word'],
                    'options' => $words[0]['options']
                ]
            ]
        ]);
    }

    public function play($wordId)
    {
        $word = Word::with('options')->find($wordId);

        if (!$word) {
            return response()->json(['error' => 'Palabra no encontrada'], 404);
        }

        return response()->json([
            'word_id' => $word->id,
            'word' => $word->word,
            'options' => $word->options->shuffle()->pluck('option_text')
        ]);
    }

 

    // Verifica si la opción es correcta y devuelve la siguiente palabra
    public function checkAnswer(Request $request)
    {
        $optionText = $request->input('option_text');
        $wordId = $request->input('word_id');

        if (!$wordId || !$optionText) {
            return response()->json(['error' => 'Faltan parámetros'], 400);
        }

        $word = Word::with('options')->find($wordId);

        if (!$word) {
            return response()->json(['error' => 'Palabra no encontrada'], 404);
        }

        $correctMeaning = $word->correct_meaning;
        $isCorrect = $optionText === $correctMeaning;

        // Obtener siguiente palabra (puedes mejorar esto según tu lógica)
        $nextWord = Word::where('id', '>', $wordId)
            ->inRandomOrder()
            ->first();

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_answer' => $correctMeaning,
            'next_word' => $nextWord ? [
                'word_id' => $nextWord->id,
                'word' => $nextWord->word,
                'options' => $nextWord->options->shuffle()->pluck('option_text')
            ] : null,
            'message' => $nextWord ? 'Siguiente pregunta' : 'Fin del juego'
        ]);
    }


    public function results()
    {
        $data = Session::get($this->gameKey);

        if (!$data) {
            return response()->json(['error' => 'Sin datos del juego'], 400);
        }

        return response()->json([
            'score' => $data['score'],
            'total' => count($data['words']),
            'completed' => $data['score'] === count($data['words']),
            'message' => $data['score'] === count($data['words']) ? '¡Has completado todas!' : 'Sigue practicando'
        ]);
    }

    public function word($id)
    {
        // Buscar la palabra por ID con sus opciones relacionadas
        $word = Word::with('options')->find($id);

        if (!$word) {
            return response()->json(['error' => 'Palabra no encontrada'], 404);
        }

        return response()->json([
            'id' => $word->id,
            'word' => $word->word,
            'correct_meaning' => $word->correct_meaning,
            'options' => $word->options->pluck('option_text')->shuffle()->toArray()
        ]);
    }

    public function listWords()
    {
        // Obtener datos del juego desde la sesión
        $data = Session::get($this->gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        return response()->json([
            'total_questions' => count($data['words']),
            'words' => collect($data['words'])->map(function ($word) {
                return [
                    'id' => $word['id'],
                    'word' => $word['word'],
                    'correct_meaning' => $word['correct_meaning'],
                    'options' => $word['options'] ?? []
                ];
            })
        ]);
    }
}