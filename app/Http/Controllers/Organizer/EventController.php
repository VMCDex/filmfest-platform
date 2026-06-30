<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('organizer_id', auth()->id())
                       ->orderBy('start_time', 'desc')
                       ->get();
        return view('organizer.events', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'start_time'  => 'required|date|after_or_equal:today',
            'end_time'    => 'required|date|after:start_time',
            'venue'       => 'required|string|max:255',
            'capacity'    => 'required|integer|min:1',
        ]);

        Event::create([
            'title'       => $request->title,
            'description' => $request->description,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'venue'       => $request->venue,
            'capacity'    => $request->capacity,
            'organizer_id' => auth()->id(),
            'status'      => 'draft',
        ]);

        return redirect('/organizer/events')->with('success', 'Мероприятие создано!');
    }

    public function edit(Event $event)
    {
        // Проверка: редактировать может только владелец
        if ($event->organizer_id !== auth()->id()) {
            abort(403, 'У вас нет прав на редактирование этого мероприятия.');
        }
        return view('organizer.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        if ($event->organizer_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'start_time'  => 'required|date|after_or_equal:today',
            'end_time'    => 'required|date|after:start_time',
            'venue'       => 'required|string|max:255',
            'capacity'    => 'required|integer|min:1',
        ]);

        $event->update($request->only(['title', 'description', 'start_time', 'end_time', 'venue', 'capacity']));
        return redirect('/organizer/events')->with('success', 'Мероприятие обновлено!');
    }

        public function publish(Event $event)
    {
        if ($event->organizer_id !== auth()->id()) {
            abort(403, 'Нет прав.');
        }
        if ($event->status !== 'draft') {
            return back()->with('error', 'Опубликовать можно только черновик.');
        }
        $event->update(['status' => 'published']);
        return back()->with('success', '✅ Мероприятие опубликовано! Теперь участники видят его в списке заявок.');
    }

    public function archive(Event $event)
    {
        if ($event->organizer_id !== auth()->id()) {
            abort(403);
        }
        // Используем статус 'completed' как архив (чтобы не менять БД сейчас)
        $event->update(['status' => 'completed']);
        return back()->with('success', 'Мероприятие отправлено в архив.');
    }

    public function destroy(Event $event)
    {
        if ($event->organizer_id !== auth()->id()) {
            abort(403);
        }
        $event->delete();
        return back()->with('success', 'Мероприятие удалено!');
    }
}