<?php

namespace App\Http\Controllers\Jury;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\JuryScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JuryController extends Controller
{
    /**
     * Список фильмов для оценки + уже оценённые
     */
        public function index()
    {
        $juryId = auth()->id();

        // Берём только одобренные фильмы, участвующие в опубликованных мероприятиях
        $films = Film::where('status', 'approved')
            ->whereHas('applications', function ($q) {
                $q->where('status', 'approved')
                  ->whereHas('event', function ($eq) { $eq->where('status', 'published'); });
            })
            ->with(['juryScores' => function ($q) use ($juryId) {
                $q->where('jury_id', $juryId)->latest();
            }])
            ->distinct()
            ->orderByDesc('created_at')
            ->get();

        $unscored = $films->filter(function ($film) {
            return $film->juryScores->isEmpty();
        });
        
        $scored = $films->filter(function ($film) {
            return $film->juryScores->isNotEmpty();
        });

        return view('jury.films', compact('unscored', 'scored'));
    }

    /**
     * Сохранение оценки
     */
    public function store(Request $request)
    {
        $request->validate([
            'film_id'               => 'required|exists:films,id',
            'criterion_script'      => 'required|numeric|min:0|max:10',
            'criterion_director'    => 'required|numeric|min:0|max:10',
            'criterion_acting'      => 'required|numeric|min:0|max:10',
            'criterion_cinematography' => 'required|numeric|min:0|max:10',
            'criterion_sound'       => 'required|numeric|min:0|max:10',
        ]);

        // Проверка на повторную оценку
        $exists = JuryScore::where('jury_id', Auth::id())
                           ->where('film_id', $request->film_id)
                           ->exists();
        if ($exists) {
            return back()->with('error', 'Вы уже выставили оценку этому фильму.');
        }

        JuryScore::create([
            'jury_id'    => Auth::id(),
            'film_id'    => $request->film_id,
            'criterion_script'       => $request->criterion_script,
            'criterion_director'     => $request->criterion_director,
            'criterion_acting'       => $request->criterion_acting,
            'criterion_cinematography' => $request->criterion_cinematography,
            'criterion_sound'        => $request->criterion_sound,
        ]);

        return back()->with('success', 'Оценка успешно сохранена!');
    }
}