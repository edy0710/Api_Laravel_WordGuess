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



    

    public function dailyWord()
    {
        // Genera una clave única por día
        $cacheKey = 'daily_word_' . now()->format('Y-m-d');

        // Si ya hay una palabra guardada en caché, devuélvela
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Si no, busca una palabra aleatoria
        $word = Word::inRandomOrder()->first();

        if (!$word) {
            return response()->json(['error' => 'No hay palabras disponibles'], 404);
        }

        // Obtén las opciones desde la base de datos
        $options = $word->options->pluck('option_text')->shuffle()->toArray();

        // Datos a guardar en caché
        $data = [
            'id' => $word->id,
            'word' => $word->word,
            'correct_meaning' => $word->correct_meaning,
            'options' => $options,
            'generated_at' => now()->toDateTimeString(),
            'expires_in' => now()->endOfDay()->diffForHumans(now(), true)
        ];

        // Guardar durante 24 horas (hasta medianoche)
        Cache::put($cacheKey, $data, now()->endOfDay());

        return response()->json($data);
    }
    
    public function checkDailyWord(Request $request)
    {
        $optionText = $request->input('option_text');
        
        if (!$optionText) {
            return response()->json(['error' => 'No se recibió opción'], 400);
        }

        // Clave de la palabra del día
        $cacheKey = 'daily_word_' . now()->format('Y-m-d');
        
        if (!Cache::has($cacheKey)) {
            return response()->json(['error' => 'La palabra del día no existe aún'], 404);
        }

        // Recuperar palabra del cache
        $dailyWord = Cache::get($cacheKey);

        // Validar respuesta
        $isCorrect = $dailyWord['correct_meaning'] === $optionText;

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_answer' => $dailyWord['correct_meaning'],
            'message' => $isCorrect ? '✅ ¡Respuesta correcta!' : '❌ Inténtalo nuevamente mañana'
        ]);
    }
}