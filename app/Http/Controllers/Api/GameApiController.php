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

    public function play()
    {
        $data = Session::get($this->gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $answeredCount = count($data['answered']);
        $totalQuestions = count($data['words']);

        if ($answeredCount >= $totalQuestions) {
            return response()->json(['finished' => true]);
        }

        $currentWord = $data['words'][$answeredCount];
        $options = Option::where('word_id', $currentWord['id'])->pluck('option_text');

        return response()->json([
            'word' => $currentWord['word'],
            'id' => $currentWord['id'],
            'options' => $options->shuffle()->toArray(),
            'question_number' => $answeredCount + 1,
            'total_questions' => $totalQuestions,
            'score' => $data['score'],
            'answered_count' => $answeredCount
        ]);
    }

    public function checkAnswer(Request $request)
    {
        $data = Session::get($this->gameKey);

        if (!$data) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $answeredCount = count($data['answered']);

        if ($answeredCount >= count($data['words'])) {
            return response()->json(['error' => 'Ya respondiste todas las preguntas'], 400);
        }

        $currentWord = $data['words'][$answeredCount];

        $isCorrect = $request->input('option') === $currentWord['correct_meaning'];

        if ($isCorrect) {
            $data['score']++;
            $data['answered'][] = $currentWord['id'];
        }

        Session::put($this->gameKey, $data);

        return response()->json([
            'is_correct' => $isCorrect,
            'score' => $data['score'],
            'next_question' => $answeredCount + 1,
            'total_questions' => count($data['words']),
            'finished' => $answeredCount + 1 >= count($data['words'])
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

    public function listWords()
    {
        $data = Session::get($this->gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $words = collect($data['words'])->map(function ($word) {
            return [
                'id' => $word['id'],
                'word' => $word['word'],
                'correct_meaning' => $word['correct_meaning'],
                'options' => Option::where('word_id', $word['id'])->pluck('option_text')->toArray()
            ];
        });

        return response()->json([
            'total_questions' => count($data['words']),
            'answered_ids' => $data['answered'],
            'words' => $words
        ]);
    }
}