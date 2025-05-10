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
        // Verificar autenticación primero
        if (!auth()->check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Debes iniciar sesión primero',
                'solution' => 'Realiza una petición POST a /api/login con tus credenciales'
            ], 401);
        }

        $user = auth()->user();
        
        try {
            // Verificar si la categoría existe y tiene palabras
            $category = Category::withCount(['words' => function($query) {
                $query->where('is_active', true); // Suponiendo que tienes este campo
            }])->findOrFail($categoryId);

            if ($category->words_count < 10) {
                return response()->json([
                    'error' => 'Not enough words',
                    'message' => 'La categoría debe tener al menos 10 palabras activas',
                    'current_word_count' => $category->words_count,
                    'category_id' => $categoryId
                ], 422);
            }

            // Obtener 10 palabras aleatorias con opciones mezcladas
            $words = Word::where('category_id', $categoryId)
                ->where('is_active', true)
                ->with(['options' => function($query) {
                    $query->select('id', 'word_id', 'option_text')
                        ->inRandomOrder();
                }])
                ->inRandomOrder()
                ->take(10)
                ->get();

            // Preparar datos del juego
            $gameData = [
                'user_id' => $user->id,
                'category_id' => $categoryId,
                'category_name' => $category->name,
                'words' => $words->map(function($word) {
                    return [
                        'id' => $word->id,
                        'word' => $word->word,
                        'correct_meaning' => $word->correct_meaning,
                        'options' => $word->options->pluck('option_text')->shuffle()
                    ];
                })->toArray(),
                'current_question' => 0,
                'score' => 0,
                'started_at' => now()->toDateTimeString()
            ];

            // Almacenar en cache
            $gameKey = 'game_'.$user->id.'_'.$categoryId;
            Cache::put($gameKey, $gameData, now()->addHours(2));

            return response()->json([
                'status' => 'success',
                'message' => 'Juego iniciado correctamente',
                'data' => [
                    'category' => $category->only(['id', 'name']),
                    'total_questions' => count($gameData['words']),
                    'first_question' => [
                        'word' => $gameData['words'][0]['word'],
                        'options' => $gameData['words'][0]['options']
                    ],
                    'game_key' => $gameKey
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Category not found',
                'available_categories' => Category::active()->get(['id', 'name'])
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
                'trace' => env('APP_DEBUG') ? $e->getTrace() : null
            ], 500);
        }
    }

    public function play($categoryId, Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $gameKey = 'game_session_' . $user->id . '_' . $categoryId;
        $data = Cache::get($gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json([
                'error' => 'Juego no iniciado para esta categoría',
                'solution' => [
                    '1. Primero llama a /game/start/' . $categoryId,
                    '2. Asegúrate de usar el mismo token de autenticación'
                ]
            ], 400);
        }

        $answeredCount = count($data['answered']);
        $totalQuestions = count($data['words']);

        if ($answeredCount >= $totalQuestions) {
            return response()->json(['finished' => true]);
        }

        $currentWord = $data['words'][$answeredCount];
        $options = Option::where('word_id', $currentWord['id'])
                    ->pluck('option_text')
                    ->toArray();

        return response()->json([
            'category_id' => $categoryId,
            'word' => $currentWord['word'],
            'id' => $currentWord['id'],
            'options' => $options,
            'question_number' => $answeredCount + 1,
            'total_questions' => $totalQuestions,
            'remaining' => $totalQuestions - $answeredCount
        ]);
    }

    public function checkAnswer(Request $request)
    {
        $data = Session::get($this->gameKey);

        if (!$data) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $currentQuestionIndex = count($data['answered']);

        if ($currentQuestionIndex >= count($data['words'])) {
            return response()->json(['error' => 'No hay más preguntas'], 400);
        }

        $currentWord = $data['words'][$currentQuestionIndex];

        // Verificar respuesta correcta usando el significado real
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