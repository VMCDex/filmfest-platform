<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\Event;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilmController extends Controller
{
    /**
     * Список фильмов + форма добавления
     */
    public function index()
    {
        $films = Film::where('participant_id', Auth::id())->orderByDesc('created_at')->get();
        return view('participant.films', compact('films'));
    }

    /**
     * Сохранение нового фильма
     */
           public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'synopsis'    => 'required|string',
            'director'    => 'required|string|max:255',
            'country'     => 'required|string|max:100',
            'year'        => 'required|integer|digits:4',
            'duration'    => 'required|integer|min:1',
            'genre'       => 'required|string|max:100',
            'poster'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'trailer_url' => 'nullable|url|max:255',
        ]);

        $posterPath = $request->hasFile('poster') ? $request->file('poster')->store('posters', 'public') : null;

        Film::create([
            'title'          => $validated['title'],
            'synopsis'       => $validated['synopsis'],
            'director'       => $validated['director'],
            'country'        => $validated['country'],
            'year'           => $validated['year'],
            'duration'       => $validated['duration'],
            'genre'          => $validated['genre'],
            'poster_path'    => $posterPath,
            'trailer_url'    => $validated['trailer_url'] ?? null,
            'participant_id' => Auth::id(),
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Фильм успешно добавлен! Ожидает проверки организатором.');
    }

    /**
     * Страница заявок (фильм → мероприятие)
     */
    public function applications()
    {
        $participantFilmIds = Film::where('participant_id', Auth::id())->pluck('id');
        $films = Film::whereIn('id', $participantFilmIds)->get();

        $events = Event::where('status', 'published')
                       ->where('end_time', '>', now()->subDays(365))
                       ->get();

        $apps = Application::whereIn('film_id', $participantFilmIds)
                           ->with('event')
                           ->orderByDesc('created_at')
                           ->get();

        return view('participant.applications', compact('films', 'events', 'apps'));
    }

    /**
     * Отправка заявки
     */
    public function submitApplication(Request $request)
    {
        $validated = $request->validate([
            'film_id'  => 'required|exists:films,id',
            'event_id' => 'required|exists:events,id',
        ]);

        $exists = Application::where('film_id', $validated['film_id'])
                             ->where('event_id', $validated['event_id'])
                             ->exists();

        if ($exists) {
            return back()->with('error', 'Заявка на этот фильм для данного мероприятия уже существует.');
        }

        $app = Application::create([
            'film_id'  => (int) $validated['film_id'],
            'event_id' => (int) $validated['event_id'],
            'status'   => 'submitted',
        ]);

        return back()->with('success', 'Заявка #' . $app->id . ' успешно отправлена! Организатор увидит её в списке.');
    }
}