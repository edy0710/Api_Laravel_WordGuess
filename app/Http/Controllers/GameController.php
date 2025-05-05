<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Word;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GameController extends Controller
{
    public function index()
    {
        $categories = Category::distinct()->get(['name', 'description', 'id']);
        return view('game.categories', compact('categories'));
    }

    public function startGame($categoryId)
    {
        Session::put('category_id', $categoryId);
        Session::put('score', 0);
        Session::put('current_question', 0);
        Session::put('answered_questions', []);
        Session::put('repeated_questions', []);
        Session::put('total_questions_shown', 0);

        return redirect()->route('game.play');
    }

    public function play()
    {
        $categoryId = Session::get('category_id');
        $answeredQuestions = Session::get('answered_questions', []);
        $repeatedQuestions = Session::get('repeated_questions', []);
        $totalShown = Session::get('total_questions_shown', 0);

        if ($totalShown >= 10) {
            return redirect()->route('game.results');
        }

        // Si hay palabras falladas, mostrarlas primero
        if (!empty($repeatedQuestions)) {
            $wordIdToRepeat = $repeatedQuestions[array_rand($repeatedQuestions)];
            $word = Word::find($wordIdToRepeat);
        } else {
            // Seleccionar una palabra nueva
            $excludedIds = array_merge($answeredQuestions, $repeatedQuestions);
            $word = Word::where('category_id', $categoryId)
                ->whereNotIn('id', $excludedIds)
                ->inRandomOrder()
                ->first();
        }

        // Si ya no quedan nuevas palabras, elegir una repetida
        if (!$word && !empty($repeatedQuestions)) {
            $wordIdToRepeat = $repeatedQuestions[array_rand($repeatedQuestions)];
            $word = Word::find($wordIdToRepeat);
        }

        if (!$word) {
            return redirect()->route('game.results');
        }

        $options = $word->options()->inRandomOrder()->get();

        Session::put('current_word_id', $word->id);
        Session::increment('total_questions_shown');

        return view('game.play', [
            'word' => $word,
            'options' => $options,
            'totalQuestions' => 10
        ]);
    }

    public function checkAnswer(Request $request)
    {
        $optionId = $request->input('option');
        $wordId = Session::get('current_word_id');

        $option = Option::find($optionId);
        $isCorrect = $option && $option->is_correct && $option->word_id == $wordId;

        $answeredQuestions = Session::get('answered_questions', []);
        $repeatedQuestions = Session::get('repeated_questions', []);

        if ($isCorrect) {
            Session::increment('score');

            if (!in_array($wordId, $answeredQuestions)) {
                $answeredQuestions[] = $wordId;
                Session::put('answered_questions', $answeredQuestions);
            }

            if (($key = array_search($wordId, $repeatedQuestions)) !== false) {
                unset($repeatedQuestions[$key]);
                Session::put('repeated_questions', array_values($repeatedQuestions));
            }
        } else {
            if (!in_array($wordId, $repeatedQuestions) && !in_array($wordId, $answeredQuestions)) {
                $repeatedQuestions[] = $wordId;
                Session::put('repeated_questions', $repeatedQuestions);
            }
        }

        Session::increment('current_question');
        return redirect()->route('game.play');
    }

    public function results(Request $request)
    {
        $user = $request->user();
        $score = Session::get('score', 0);
        $totalQuestions = Session::get('total_questions_shown', 10);
        $categoryId = Session::get('category_id');

        if ($score === 10) {
            $alreadyCompleted = $user->completedCategories()
                ->where('category_id', $categoryId)
                ->exists();

            if (!$alreadyCompleted) {
                $user->increment('points', 100);
                $user->completedCategories()->attach($categoryId);

                Session::flash('achievement', [
                    'title' => '¡Categoría completada!',
                    'message' => 'Has ganado 100 puntos por completar esta categoría',
                    'totalPoints' => $user->fresh()->points
                ]);
            }
        }

        $totalCategories = max(Category::count(), 1);

        return view('game.results', [
            'score' => $score,
            'totalQuestions' => $totalQuestions,
            'userPoints' => $user->points,
            'completedCategories' => $user->completedCategories()->count(),
            'totalCategories' => $totalCategories
        ]);
    }

    public function __construct()
    {
        $this->middleware('auth')->except(['index']); // Excluir inicio para invitados
    }
}