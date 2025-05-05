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
        // Seleccionamos 10 palabras únicas de la categoría
        $words = Word::where('category_id', $categoryId)
            ->inRandomOrder()
            ->take(10)
            ->get()
            ->unique('id'); // Aseguramos que los IDs sean únicos

        if ($words->isEmpty()) {
            return response()->json(['error' => 'No hay suficientes palabras'], 404);
        }

        // Guardamos progreso en sesión (opcional) o base de datos
        session([$this->gameKey => [
            'category_id' => $categoryId,
            'words' => $words->toArray(),
            'answered' => [],
            'score' => 0
        ]]);

        return response()->json([
            'total_questions' => $words->count(),
            'words' => $words->map(fn($word) => ['id' => $word['id'], 'word' => $word['word']]),
        ]);
    }

    public function play()
    {
        $data = session($this->gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $currentQuestionIndex = count($data['answered']); // cuántas ha acertado ya

        if ($currentQuestionIndex >= count($data['words'])) {
            return response()->json(['finished' => true]);
        }

        $currentWord = $data['words'][$currentQuestionIndex];

        // Obtener opciones mezcladas
        $options = Option::where('word_id', $currentWord['id'])->get();

        return response()->json([
            'word' => $currentWord['word'],
            'options' => $options->pluck('option_text'),
            'question_number' => $currentQuestionIndex + 1,
            'total_questions' => count($data['words'])
        ]);
    }

    public function checkAnswer(Request $request)
    {
        $data = session($this->gameKey);

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

        session([$this->gameKey => $data]);

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
        $data = session($this->gameKey);

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