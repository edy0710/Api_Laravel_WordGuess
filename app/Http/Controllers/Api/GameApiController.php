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
        try {
            // Verificación robusta de autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'error' => 'Usuario no autenticado',
                    'solution' => 'Debes hacer login primero y enviar el token válido'
                ], 401);
            }

            // Validación de categoría
            $category = Category::withCount('words')->findOrFail($categoryId);

            // Verificar que la categoría tenga palabras
            if ($category->words_count < 1) {
                return response()->json([
                    'error' => 'Categoría sin palabras',
                    'message' => 'Esta categoría no contiene palabras disponibles',
                    'category_id' => $categoryId,
                    'category_name' => $category->name
                ], 422);
            }

            // Obtener palabras con sus opciones
            $words = Word::where('category_id', $categoryId)
                ->with(['options' => function($query) {
                    $query->inRandomOrder()->select('id', 'word_id', 'option_text');
                }])
                ->inRandomOrder()
                ->take(10)
                ->get()
                ->map(function($word) {
                    // Mezclar opciones para cada palabra
                    $word->options = $word->options->shuffle();
                    return $word;
                });

            // Preparar datos del juego
            $gameData = [
                'category_id' => $categoryId,
                'category_name' => $category->name,
                'words' => $words->toArray(),
                'answered' => [],
                'score' => 0,
                'started_at' => now()->toDateTimeString(),
                'last_activity' => now()->toDateTimeString()
            ];

            // Clave única por usuario y categoría
            $gameKey = 'game_session_' . $user->id . '_' . $categoryId;
            
            // Almacenar en cache (2 horas de duración)
            Cache::put($gameKey, $gameData, now()->addHours(2));

            return response()->json([
                'success' => true,
                'message' => 'Juego iniciado correctamente',
                'game_key' => $gameKey,
                'category' => [
                    'id' => $categoryId,
                    'name' => $category->name
                ],
                'game_details' => [
                    'total_questions' => $words->count(),
                    'first_word' => $words->first()->word,
                    'first_word_options' => $words->first()->options->pluck('option_text')
                ],
                'next_step' => 'Llamar a /game/play/' . $categoryId
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Categoría no encontrada',
                'available_categories' => Category::all()->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'word_count' => $cat->words_count
                    ];
                })
            ], 404);
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