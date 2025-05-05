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

    public function categories()
    {
        $categories = Category::withCount('words')->get();
        return response()->json($categories);
    }

    public function startGame($categoryId)
    {
        // Cargar palabras con sus opciones relacionadas
        $words = Word::where('category_id', $categoryId)
            ->with(['options' => function ($query) {
                $query->select('id', 'word_id', 'option_text');
            }])
            ->inRandomOrder()
            ->take(10)
            ->get();

        if ($words->isEmpty()) {
            return response()->json(['error' => 'No hay suficientes palabras'], 404);
        }

        // Guardar en sesión (opcional)
        session([$this->gameKey => [
            'category_id' => $categoryId,
            'words' => $words->toArray(),
            'answered' => [],
            'score' => 0
        ]]);

        return response()->json([
            'total_questions' => $words->count(),
            'words' => $words->map(function ($word) {
                return [
                    'id' => $word['id'],
                    'word' => $word['word'],
                    'options' => $word['options'],
                    'correct_meaning' => $word['correct_meaning']
                ];
            })
        ]);
    }

    public function play()
    {
        $data = Session::get($this->gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $currentQuestionIndex = count($data['answered']);

        if ($currentQuestionIndex >= count($data['words'])) {
            return response()->json(['finished' => true]);
        }

        $currentWord = $data['words'][$currentQuestionIndex];

        return response()->json([
            'word' => $currentWord['word'],
            'correct_meaning' => $currentWord['correct_meaning'],
            'options' => $currentWord['options'],
            'question_number' => $currentQuestionIndex + 1,
            'total_questions' => count($data['words'])
        ]);
    }

    public function checkAnswer(Request $request)
    {
        $data = Session::get($this->gameKey);

        if (!$data) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $currentQuestionIndex = count($data['answered']);
        $currentWord = $data['words'][$currentQuestionIndex] ?? null;

        if (!$currentWord) {
            return response()->json(['error' => 'No hay más preguntas'], 400);
        }

        $isCorrect = $request->input('option') === $currentWord['correct_meaning'];

        if ($isCorrect) {
            $data['score']++;
            $data['answered'][] = $currentWord['id'];
        }

        Session::put($this->gameKey, $data);

        return response()->json([
            'is_correct' => $isCorrect,
            'score' => $data['score'],
            'next_question' => $currentQuestionIndex + 1,
            'total_questions' => count($data['words']),
            'finished' => $currentQuestionIndex + 1 >= count($data['words'])
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
}