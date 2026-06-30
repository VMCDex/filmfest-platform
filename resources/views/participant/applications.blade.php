@extends('layouts.app')

@section('content')
<style>
    .app-wrapper { max-width: 800px; margin: 0 auto; }
    .app-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; overflow: hidden; margin-bottom: 25px; }
    .app-header { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); color: #fff; padding: 15px 20px; font-weight: bold; }
    .app-body { padding: 20px; }
    .form-row { display: flex; gap: 15px; margin-bottom: 15px; align-items: flex-end; }
    .form-group { margin-bottom: 0; flex: 1; }
    .form-label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #555; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9; font-size: 14px; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #8e44ad; background: #fff; box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.15); }
    .btn-apply { padding: 10px 20px; background: #8e44ad; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; white-space: nowrap; }
    .btn-apply:hover { background: #7d3c98; transform: translateY(-1px); }
    .app-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 10px; }
    .app-table th { background: #f8f9fa; padding: 10px; text-align: left; border-bottom: 2px solid #eee; color: #555; }
    .app-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
    .status-tag { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; color: #fff; }
    .tag-submitted { background: #3498db; } .tag-reviewed { background: #f39c12; } .tag-approved { background: #27ae60; } .tag-rejected { background: #e74c3c; }
    .empty-state { text-align: center; padding: 30px; color: #777; }
/* === Адаптация участника === */
@media (max-width: 900px) {
    .participant-wrapper { flex-direction: column; gap: 20px; }
    .p-sidebar, .p-main { width: 100%; flex: none; }
}
@media (max-width: 600px) {
    .p-header, .app-header { font-size: 16px; padding: 12px 15px; }
    .p-body, .app-body { padding: 15px; }
    .form-group { margin-bottom: 12px; }
    .btn-add, .btn-apply { width: 100%; padding: 14px; font-size: 14px; }
    .film-card, .app-table { font-size: 13px; }
    .app-table th, .app-table td { padding: 8px 6px; white-space: normal; }
    .app-wrapper { max-width: 100%; padding: 0 10px; }
}</style>

<div class="app-wrapper">
    <!-- Форма подачи -->
    <div class="app-card">
        <div class="app-header">📤 Подать заявку на участие</div>
        <div class="app-body">
            @if($films->isEmpty())
                <div class="empty-state">Для подачи заявки сначала добавьте фильм и дождитесь его одобрения (статус ✅ Допущен).</div>
            @elseif($events->isEmpty())
                <div class="empty-state">Нет открытых мероприятий для подачи заявок. Попробуйте позже.</div>
            @else
            <form method="POST" action="/participant/applications/submit">
                @csrf
                @if($errors->any())
    <div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; margin-bottom:15px;">
        <ul style="margin:0; padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Выберите фильм</label>
                        <select name="film_id" class="form-control" required>
                            <option value="" disabled selected>-- Ваш одобренный фильм --</option>
                            @foreach($films as $film)
                                <option value="{{ $film->id }}">{{ $film->title }} ({{ $film->year }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Выберите мероприятие</label>
                        <select name="event_id" class="form-control" required>
                            <option value="" disabled selected>-- Активное мероприятие --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }} ({{ \Carbon\Carbon::parse($event->start_time)->format('d.m.Y') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-apply">Отправить заявку</button>
                </div>
            </form>
            @endif
        </div>
    </div>

    <!-- История заявок -->
    <div class="app-card">
        <div class="app-header">📋 История заявок</div>
        <div class="app-body" style="padding: 0;">
            @if($apps->isEmpty())
                <div class="empty-state">Заявок пока не было.</div>
            @else
            <table class="app-table">
                <thead>
                    <tr><th>Мероприятие</th><th>Дата подачи</th><th>Статус</th></tr>
                </thead>
                <tbody>
                    @foreach($apps as $app)
                    <tr>
                        <td>{{ $app->event->title ?? 'Удалено' }}</td>
                        <td>{{ $app->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <span class="status-tag tag-{{ $app->status }}">
                                @switch($app->status)
                                    @case('submitted') 📥 Отправлено @break
                                    @case('under_review') 👀 На рассмотрении @break
                                    @case('approved') ✅ Участвует @break
                                    @case('rejected') ❌ Отказ @break
                                @endswitch
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection