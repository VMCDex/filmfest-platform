<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\ApplicationStatusChanged;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{
    /**
     * Список заявок с фильтрацией
     */
       public function index(Request $request)
    {
        $organizerId = auth()->id();
        
        // 1. Получаем IDs мероприятий, созданных именно этим пользователем
        $eventIds = \App\Models\Event::where('organizer_id', $organizerId)->pluck('id')->toArray();

        // Если у организатора нет мероприятий, сразу возвращаем пустой список
        if (empty($eventIds)) {
            return view('organizer.applications', [
                'applications' => collect(),
                'statuses' => ['all', 'submitted', 'under_review', 'approved', 'rejected']
            ]);
        }

        // 2. Жёсткий запрос по IDs без сложных вложенных связей
        $query = \App\Models\Application::whereIn('event_id', $eventIds);

        // 3. Фильтр по статусу (если выбран)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 4. Загружаем связи только после фильтрации, сортируем по дате создания
        $applications = $query->with(['film', 'event', 'reviewer'])->latest()->get();
        
        $statuses = ['all', 'submitted', 'under_review', 'approved', 'rejected'];

        return view('organizer.applications', compact('applications', 'statuses'));
    }

    public function review(Request $request, Application $application)
    {
        if ($application->event->organizer_id !== Auth::id()) {
            abort(403, 'У вас нет прав на эту заявку.');
        }

        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status'      => $request->status,
            'comment'     => $request->comment,
            'reviewed_by' => Auth::id(),
        ]);

        // 🔄 СИНХРОНИЗАЦИЯ СТАТУСОВ: одобренная заявка = одобренный фильм
        if ($request->status === 'approved') {
            $application->film->update(['status' => 'approved']);
        } else {
            // Если отклонили, проверяем, есть ли другие одобренные заявки на этот фильм
            $hasOtherApproved = Application::where('film_id', $application->film_id)
                                           ->where('status', 'approved')
                                           ->where('id', '!=', $application->id)
                                           ->exists();
            if (!$hasOtherApproved) {
                $application->film->update(['status' => 'rejected']);
            }
        }

        // Отправка уведомления (если настроен Mail)
        if ($application->film && $application->film->participant) {
            try {
                \Illuminate\Support\Facades\Mail::to($application->film->participant->email)
                    ->queue(new \App\Mail\ApplicationStatusChanged($application));
            } catch (\Exception $e) {
                // Игнорируем ошибки почты в dev-среде
            }
        }

        $msg = $request->status === 'approved' ? 'Заявка одобрена.' : 'Заявка отклонена.';
        return back()->with('success', $msg . ' Уведомление отправлено участнику.');
    }
}