<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Ticket;
use App\Models\Review;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Панель отчётов и статистики
     */
public function index()
{
    $organizerId = auth()->id();
    $eventIds = Event::where('organizer_id', $organizerId)->pluck('id');

    // 1. Статистика заявок по статусам
    $appsByStatus = Application::whereIn('event_id', $eventIds)
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

    $appsData = [
        'labels' => ['📥 Новые', '👀 На рассмотрении', '✅ Одобрено', '❌ Отклонено'],
        'data'   => [
            $appsByStatus['submitted'] ?? 0,
            $appsByStatus['under_review'] ?? 0,
            $appsByStatus['approved'] ?? 0,
            $appsByStatus['rejected'] ?? 0
        ]
    ];

    // 2. Динамика продаж билетов
    $ticketDynamicsRaw = Ticket::whereIn('event_id', $eventIds)
        ->where('created_at', '>=', now()->subDays(30))
        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    $ticketDynamics = array();
    foreach ($ticketDynamicsRaw as $item) {
        $ticketDynamics[] = [
            'date' => $item->date,
            'count' => (int) $item->count
        ];
    }

    // 3. Статистика отзывов
    $filmIds = Application::whereIn('event_id', $eventIds)->pluck('film_id');
    $reviewsByRating = Review::whereIn('film_id', $filmIds)
        ->selectRaw('rating, COUNT(*) as count')
        ->groupBy('rating')
        ->pluck('count', 'rating')
        ->toArray();

    $reviewsDataLabels = array('1★', '2★', '3★', '4★', '5★');
    $reviewsDataValues = array();
    for ($i = 1; $i <= 5; $i++) {
        $reviewsDataValues[] = isset($reviewsByRating[$i]) ? $reviewsByRating[$i] : 0;
    }
    
    $reviewsData = [
        'labels' => $reviewsDataLabels,
        'data'   => $reviewsDataValues
    ];

    // === НОВОЕ: Результаты зрительского голосования ===
    $votesByFilm = \App\Models\Vote::join('films', 'votes.film_id', '=', 'films.id')
        ->whereIn('films.id', $filmIds)
        ->selectRaw('films.title, COUNT(*) as votes_count')
        ->groupBy('films.id', 'films.title')
        ->orderByDesc('votes_count')
        ->take(10) // Топ-10 фильмов
        ->get();

    $votesData = [
        'labels' => $votesByFilm->pluck('title')->toArray(),
        'data'   => $votesByFilm->pluck('votes_count')->map(function($v) { return (int)$v; })->toArray()
    ];

    // === НОВОЕ: Оценки жюри по фильмам ===
    $juryScoresByFilm = \App\Models\JuryScore::join('films', 'jury_scores.film_id', '=', 'films.id')
        ->whereIn('films.id', $filmIds)
        ->selectRaw('films.title, AVG(total_score) as avg_score, COUNT(*) as scores_count')
        ->groupBy('films.id', 'films.title')
        ->orderByDesc('avg_score')
        ->take(10) // Топ-10 фильмов
        ->get();

    $juryData = [
        'labels' => $juryScoresByFilm->pluck('title')->toArray(),
        'data'   => $juryScoresByFilm->pluck('avg_score')->map(function($v) { return round((float)$v, 1); })->toArray(),
        'counts' => $juryScoresByFilm->pluck('scores_count')->map(function($v) { return (int)$v; })->toArray()
    ];

    return view('organizer.reports', compact(
        'appsData', 'ticketDynamics', 'reviewsData', 
        'votesData', 'juryData' // ← Добавили новые переменные
    ));
}

    /**
     * Экспорт заявок в XLSX (через CSV, который корректно открывает Excel)
     */
    public function exportXlsx()
    {
        $organizerId = Auth::id();
        $eventIds = Event::where('organizer_id', $organizerId)->pluck('id');
        $applications = Application::whereIn('event_id', $eventIds)->with(['film', 'event'])->get();

        $filename = "kiff_reports_" . now()->format('Y-m-d_H-i') . ".csv";
        $headers = [
            "Content-type" => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            // BOM для корректного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['ID', 'Фильм', 'Режиссёр', 'Мероприятие', 'Статус', 'Дата подачи']);
            foreach ($applications as $app) {
                fputcsv($file, [
                    $app->id,
                    $app->film->title ?? '-',
                    $app->film->director ?? '-',
                    $app->event->title ?? '-',
                    $app->status,
                    $app->created_at->format('d.m.Y H:i')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Экспорт в PDF (генерирует чистый HTML для печати/сохранения в PDF через браузер)
     */
    public function exportPdf()
    {
        $organizerId = Auth::id();
        $eventIds = Event::where('organizer_id', $organizerId)->pluck('id');
        $applications = Application::whereIn('event_id', $eventIds)->with(['film', 'event'])->get();
        
        $stats = [
            'total_apps' => $applications->count(),
            'approved'   => $applications->where('status', 'approved')->count(),
            'rejected'   => $applications->where('status', 'rejected')->count(),
        ];

        return view('organizer.reports_pdf', compact('applications', 'stats'));
    }
}