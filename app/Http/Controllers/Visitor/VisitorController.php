<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Film;
use App\Models\Vote;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitorController extends Controller
{
    /**
     * Главная страница зрителя: афиша, фильмы для голосования, мои билеты
     */
       public function index()
{
    // Активные опубликованные мероприятия
    $events = \App\Models\Event::where('status', 'published')
                               ->where('end_time', '>', now())
                               ->orderBy('start_time')
                               ->get();

    // Фильмы для голосования (одобренные заявки)
    $films = \App\Models\Film::where('status', 'approved')->get();

    // ✅ Билеты текущего зрителя (с предзагрузкой связи event)
    $myTickets = \App\Models\Ticket::with('event')
                                   ->where('user_id', auth()->id())
                                   ->get();

    // Отзывы текущего зрителя
 $myReviews = \App\Models\Review::with('film') // ← Предзагрузка фильма
    ->where('user_id', auth()->id())
    ->latest()
    ->get();
    
    return view('visitor.index', compact('events', 'films', 'myTickets', 'myReviews'));
}

    /**
     * Покупка билета
     */
    public function buyTicket(Request $request, Event $event)
    {
        $request->validate([
            'seat_number' => 'required|string|max:10',
            'price'       => 'required|numeric|min:0'
        ]);

        // Проверка, не занято ли место
        $exists = Ticket::where('event_id', $event->id)
                        ->where('seat_number', $request->seat_number)
                        ->where('status', '!=', 'cancelled')
                        ->exists();

        if ($exists) {
            return back()->with('error', 'Место ' . $request->seat_number . ' уже занято.');
        }

        Ticket::create([
            'user_id'     => Auth::id(),
            'event_id'    => $event->id,
            'seat_number' => $request->seat_number,
            'price'       => $request->price,
            'status'      => 'paid',
            'paid_at'     => now()
        ]);

        return back()->with('success', 'Билет успешно оформлен!');
    }

    /**
     * Голосование за фильм
     */
    public function vote(Request $request, Film $film)
    {
        $ipHash = hash('sha256', request()->ip());
        $exists = Vote::where('film_id', $film->id)->where('ip_hash', $ipHash)->exists();

        if ($exists) {
            return back()->with('error', 'Вы уже голосовали за этот фильм.');
        }

        Vote::create([
            'user_id' => Auth::id(),
            'film_id' => $film->id,
            'ip_hash' => $ipHash
        ]);

        return back()->with('success', 'Ваш голос учтён! 🗳️');
    }

    /**
     * Отправка отзыва
     */
    public function submitReview(Request $request, Film $film)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text'   => 'required|string|max:500'
        ]);

        Review::create([
            'user_id' => Auth::id(),
            'film_id' => $film->id,
            'rating'  => $request->rating,
            'text'    => $request->text,
            'status'  => 'pending'
        ]);

        return back()->with('success', 'Отзыв отправлен на модерацию.');
    }
}