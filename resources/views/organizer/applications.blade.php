@extends('layouts.app')

@section('content')
<style>
    /* Фильтр */
    .filter-bar { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; align-items: center; gap: 15px; }
    .filter-label { font-weight: 600; color: #555; }
    .filter-select { padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; font-size: 14px; cursor: pointer; }

    /* Карточка заявки */
    .app-card {
        background: #fff; border-radius: 12px; padding: 25px; margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #eee;
        display: flex; gap: 25px; align-items: flex-start; flex-wrap: wrap;
        position: relative; overflow: hidden;
    }
    .app-card::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 5px;
    }
    .app-submitted::before { background: #f39c12; }
    .app-approved::before { background: #27ae60; }
    .app-rejected::before { background: #e74c3c; }
    .app-default::before { background: #bdc3c7; }

    /* Информация о фильме */
    .app-info { flex: 1; min-width: 300px; }
    .app-title { font-size: 20px; font-weight: 700; margin: 0 0 10px; color: #2c3e50; }
    .app-meta { font-size: 14px; color: #666; line-height: 1.6; }
    .app-meta strong { color: #333; }
    .app-event { margin-top: 10px; background: #f0f7fb; padding: 8px 12px; border-radius: 6px; font-size: 13px; color: #2980b9; display: inline-block; }
    
    .file-links { margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
    .file-link {
        display: inline-flex; align-items: center; padding: 5px 10px; background: #eee; border-radius: 4px; 
        font-size: 12px; color: #555; text-decoration: none; transition: 0.2s;
    }
    .file-link:hover { background: #ddd; color: #000; }

    /* Панель решения */
    .review-panel {
        flex: 0 0 260px; background: #fafafa; padding: 20px; border-radius: 10px; border: 1px solid #eee;
    }
    .review-status { font-size: 14px; font-weight: bold; margin-bottom: 15px; text-align: center; padding: 8px; border-radius: 6px; }
    .rs-submitted { background: #fef9e7; color: #d35400; border: 1px solid #f1c40f; }
    .rs-approved { background: #e8f8f5; color: #27ae60; border: 1px solid #2ecc71; }
    .rs-rejected { background: #fdedec; color: #e74c3c; border: 1px solid #e74c3c; }

    .review-textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 10px; resize: vertical; font-size: 13px; }
    
    .review-actions { display: flex; gap: 8px; }
    .btn-app, .btn-rej {
        flex: 1; padding: 10px; border: none; border-radius: 6px; color: #fff; font-weight: bold; cursor: pointer; transition: 0.2s;
    }
    .btn-app { background: #27ae60; box-shadow: 0 3px 6px rgba(39, 174, 96, 0.3); }
    .btn-app:hover { background: #219150; transform: translateY(-2px); }
    .btn-rej { background: #e74c3c; box-shadow: 0 3px 6px rgba(231, 76, 60, 0.3); }
    .btn-rej:hover { background: #c0392b; transform: translateY(-2px); }

    .reviewer-comment { background: #e8f8f5; padding: 10px; border-radius: 6px; font-size: 13px; color: #2c3e50; border-left: 3px solid #27ae60; margin-top: 10px; }
@media (max-width: 768px) {
    .app-card { flex-direction: column; gap: 20px; padding: 15px; }
    .review-panel { flex: none; width: 100%; margin-top: 10px; }
    .filter-bar { flex-direction: column; align-items: stretch; gap: 8px; padding: 12px; }
    .filter-select { width: 100%; }
    .app-title { font-size: 18px; }
    .file-links { flex-wrap: wrap; }
}</style>


<div class="organizer-main">
    <!-- Фильтр -->
 <div class="filter-bar">
    <span class="filter-label">🔍 Фильтр:</span>
    <form method="GET" action="/organizer/applications" style="display:inline;">
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Все заявки</option>
            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>📥 Новые</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ Одобрены</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Отклонены</option>
        </select>
    </form>
</div>

    <!-- Список -->
    @forelse($applications as $app)
    <div class="app-card app-{{ in_array($app->status, ['submitted', 'approved', 'rejected']) ? $app->status : 'default' }}">
        
        <!-- Левая часть: Инфо -->
        <div class="app-info">
            <h3 class="app-title">🎬 {{ $app->film->title ?? 'Фильм удалён' }}</h3>
            <div class="app-meta">
                <div><strong>Режиссёр:</strong> {{ $app->film->director ?? '-' }}</div>
                <div><strong>Жанр:</strong> {{ $app->film->genre ?? '-' }} | <strong>Год:</strong> {{ $app->film->year ?? '-' }}</div>
                <div><strong>Синопсис:</strong> {{ Str::limit($app->film->synopsis, 100) }}</div>
            </div>

            <div class="app-event">📅 Мероприятие: {{ $app->event->title ?? 'Удалено' }}</div>

            <div class="file-links">
    @if($app->film->poster_path)
        <a href="{{ asset('storage/' . $app->film->poster_path) }}" target="_blank" class="file-link">🖼 Постер</a>
    @endif
   @if($app->film->trailer_url)
    <a href="{{ $app->film->trailer_url }}" target="_blank" class="file-link">🎞 Трейлер</a>
@endif
</div>
        </div>

        <!-- Правая часть: Решение -->
        <div class="review-panel">
            <div class="review-status rs-{{ $app->status }}">
                @switch($app->status)
                    @case('submitted') 📥 Новая заявка @break
                    @case('under_review') 👀 На проверке @break
                    @case('approved') ✅ Одобрено @break
                    @case('rejected') ❌ Отклонено @break
                    @default Неизвестно
                @endswitch
            </div>

            @if(in_array($app->status, ['submitted', 'under_review']))
                <form method="POST" action="/organizer/applications/{{ $app->id }}/review">
                    @csrf @method('PUT')
                    <label style="font-size: 12px; font-weight: 600; color: #777;">Комментарий:</label>
                    <textarea name="comment" class="review-textarea" rows="3" placeholder="Причина отказа или комментарий..."></textarea>
                    <div class="review-actions">
                        <button type="submit" name="status" value="approved" class="btn-app">✅</button>
                        <button type="submit" name="status" value="rejected" class="btn-rej">❌</button>
                    </div>
                </form>
            @else
                <div class="reviewer-comment">
                    <strong>Решение:</strong> {{ ucfirst($app->status) }}<br>
                    @if($app->comment)
                    <div style="margin-top:5px; opacity: 0.9;">💬 {{ $app->comment }}</div>
                    @endif
                    <div style="margin-top:8px; font-size:11px; opacity:0.6;">Проверил: {{ $app->reviewer->name ?? 'Админ' }}</div>
                </div>
            @endif
        </div>

    </div>
    @empty
    <div style="text-align: center; padding: 50px; background: #fff; border-radius: 12px; color: #777;">
        Заявок на ваши мероприятия пока нет.
    </div>
    @endforelse
</div>
@endsection