<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Word;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

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
        try {
            \Log::info("Attempting to fetch word ID: ".$wordId); // Debug
            
            $word = Word::with(['options' => function($query) {
                $query->select('id', 'word_id', 'option_text');
            }])->find($wordId);

            if (!$word) {
                \Log::error("Word not found: ".$wordId);
                return response()->json(['error' => 'Word not found'], 404);
            }

            // Debug: Verifica las opciones cargadas
            \Log::debug("Word options: ".json_encode($word->options));

            return response()->json([
                'word_id' => $word->id,
                'word' => $word->word,
                'options' => $word->options->shuffle()->pluck('option_text')
            ]);

        } catch (\Exception $e) {
            \Log::error("Error in play(): ".$e->getMessage());
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage() // Solo en desarrollo
            ], 500);
        }
    }

 

    // Verifica si la opción es correcta y devuelve la siguiente palabra
    public function checkAnswer(Request $request)
    {
        // Validación de entrada
        $request->validate([
            'option_text' => 'required|string',
            'word_id' => 'required|integer|exists:words,id'
        ]);

        try {
            $optionText = $request->input('option_text');
            $wordId = $request->input('word_id');
            $userIp = $request->ip();

            // Obtener palabra actual con sus opciones
            $currentWord = Word::with('options')->findOrFail($wordId);
            $isCorrect = $optionText === $currentWord->correct_meaning;

            // Registrar evento de respuesta
            WordEvent::create([
                'word_id' => $wordId,
                'event_type' => 'answer',
                'is_correct' => $isCorrect,
                'user_ip' => $userIp
            ]);

            // Obtener siguiente palabra (mejor lógica de selección)
            $nextWord = Word::where('category_id', $currentWord->category_id)
                ->where('id', '!=', $wordId)
                ->inRandomOrder()
                ->first();

            // Preparar respuesta
            $response = [
                'is_correct' => $isCorrect,
                'correct_answer' => $currentWord->correct_meaning,
                'current_word' => [
                    'id' => $currentWord->id,
                    'word' => $currentWord->word,
                    'example' => $currentWord->example_sentence // Asumiendo que existe este campo
                ],
                'stats' => [
                    'total_attempts' => WordEvent::where('word_id', $wordId)->count(),
                    'accuracy' => WordEvent::where('word_id', $wordId)
                                    ->where('event_type', 'answer')
                                    ->avg('is_correct') * 100
                ]
            ];

            // Agregar siguiente palabra si existe
            if ($nextWord) {
                $response['next_word'] = [
                    'word_id' => $nextWord->id,
                    'word' => $nextWord->word,
                    'options' => $nextWord->options->shuffle()->pluck('option_text')
                ];
                $response['message'] = 'Siguiente pregunta';
                
                // Registrar evento de consulta para la siguiente palabra
                WordEvent::create([
                    'word_id' => $nextWord->id,
                    'event_type' => 'query',
                    'user_ip' => $userIp
                ]);
            } else {
                $response['message'] = 'Fin del juego. ¡Has completado todas las palabras!';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la respuesta',
                'details' => $e->getMessage()
            ], 500);
        }
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


    public function listAllWords()
    {
        // Obtener todas las palabras con sus opciones
        $words = Word::with(['options' => function($query) {
            $query->select('id', 'word_id', 'option_text');
        }])->get();

        if ($words->isEmpty()) {
            return response()->json([
                'error' => 'No hay palabras disponibles'
            ], 404);
        }

        return response()->json([
            'total_words' => $words->count(),
            'words' => $words->map(function ($word) {
                return [
                    'id' => $word->id,
                    'word' => $word->word,
                    'correct_meaning' => $word->correct_meaning,
                    'options' => $word->options->pluck('option_text')->shuffle()->toArray()
                ];
            })
        ]);
    }

    // Método para obtener la palabra del día
    public function dailyWord()
    {
        $cacheKey = 'daily_word_' . now()->format('Y-m-d');

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Obtener una palabra aleatoria con sus opciones
        $word = Word::with(['options' => function ($query) {
            $query->select('id', 'word_id', 'option_text');
        }])->inRandomOrder()->first();

        if (!$word) {
            return response()->json(['error' => 'No hay palabras disponibles'], 404);
        }

        // Mezclar las opciones para que no estén siempre en el mismo orden
        $options = $word->options->pluck('option_text')->toArray();
        shuffle($options);

        $data = [
            'id' => $word->id,
            'word' => $word->word,
            'correct_meaning' => $word->correct_meaning,
            'options' => $options,
            'generated_at' => now()->toDateTimeString(),
            'expires_in' => now()->endOfDay()->diffForHumans(now(), true)
        ];

        // Guardar en caché hasta el final del día
        Cache::put($cacheKey, $data, now()->endOfDay());

        return response()->json($data);
    }

    // Método para verificar la respuesta
    public function checkDailyWord(Request $request)
    {
        $optionText = $request->input('option_text');

        if (!$optionText) {
            return response()->json(['error' => 'Falta opción de respuesta'], 400);
        }

        $cacheKey = 'daily_word_' . now()->format('Y-m-d');

        if (!Cache::has($cacheKey)) {
            return response()->json(['error' => 'La palabra del día no está disponible aún'], 500);
        }

        $dailyWord = Cache::get($cacheKey);

        $isCorrect = $optionText === $dailyWord['correct_meaning'];

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_meaning' => $dailyWord['correct_meaning'],
            'message' => $isCorrect ? '✅ ¡Has acertado!' : '❌ Inténtalo mañana'
        ]);
    }

    public function allWords()
    {
        $words = Word::with(['options' => function ($query) {
            $query->select('id', 'word_id', 'option_text');
        }])->get();

        return response()->json([
            'total_words' => $words->count(),
            'words' => $words->map(function ($word) {
                return [
                    'id' => $word->id,
                    'word' => $word->word
                ];
            })
        ]);
    }

    public function getWordsByCountAndCategory($count, $category, $order = 'asc')
    {
        // Validar parámetros
        if (!is_numeric($count) || $count <= 0) {
            return response()->json(['error' => 'La cantidad debe ser un número positivo'], 400);
        }

        if (!is_numeric($category)) {
            return response()->json(['error' => 'La categoría debe ser un ID numérico'], 400);
        }

        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            return response()->json(['error' => 'La orientacion debe ser "asc" o "desc"'], 400);
        }

        // Verificar si la categoría existe
        $categoryExists = Category::find($category);
        if (!$categoryExists) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        // Obtener palabras con sus opciones
        $words = Word::where('category_id', $category)
            ->with(['options' => function($query) {
                $query->select('id', 'word_id', 'option_text');
            }])
            ->orderBy('word', $order)
            ->take($count)
            ->get();

        if ($words->isEmpty()) {
            return response()->json(['error' => 'No se encontraron palabras en esta categoría'], 404);
        }

        return response()->json([
            'total_words' => $words->count(),
            'category' => [
                'id' => $categoryExists->id,
                'name' => $categoryExists->name
            ],
            'order' => $order,
            'words' => $words->map(function ($word) {
                return [
                    'id' => $word->id,
                    'word' => $word->word,
                    'correct_meaning' => $word->correct_meaning,
                    'options' => $word->options->pluck('option_text')->shuffle()->toArray()
                ];
            })
        ]);
    }

    private function logWordEvent($wordId, $eventType, $isCorrect = null)
    {
        WordEvent::create([
            'word_id' => $wordId,
            'event_type' => $eventType,
            'is_correct' => $isCorrect,
            'user_ip' => Request::ip()
        ]);
    }
}