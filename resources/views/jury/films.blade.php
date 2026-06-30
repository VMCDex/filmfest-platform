@extends('layouts.app')

@section('content')
<style>
    .jury-wrapper { display: grid; gap: 25px; grid-template-columns: 1fr; }
    @media (min-width: 1024px) { .jury-wrapper { grid-template-columns: 1fr 1fr; } }
    
    .j-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; overflow: hidden; }
    .j-header { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); color: #fff; padding: 15px 20px; font-weight: bold; }
    .j-body { padding: 20px; }
    
    .film-block { border-left: 4px solid #8e44ad; background: #fafafa; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
    .film-title { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
    .film-meta { font-size: 13px; color: #666; margin-bottom: 12px; }
    
    .score-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-top: 10px; }
    .score-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
    .score-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; text-align: center; box-sizing: border-box; }
    .score-group input:focus { outline: none; border-color: #8e44ad; box-shadow: 0 0 0 2px rgba(142, 68, 173, 0.15); }
    
    .btn-score { grid-column: 1 / -1; padding: 12px; background: linear-gradient(to right, #8e44ad, #9b59b6); color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 5px; transition: 0.2s; }
    .btn-score:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(142, 68, 173, 0.4); }
    
    .scored-card { background: #f0fdf4; border-left-color: #27ae60; }
    .score-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; border-bottom: 1px dashed #ccc; }
    .score-row:last-child { border-bottom: none; font-weight: bold; color: #2c3e50; margin-top: 6px; padding-top: 6px; border-top: 1px solid #ccc; }
    .empty-state { text-align: center; padding: 30px; color: #777; font-style: italic; }
    /* === Адаптация жюри === */
@media (max-width: 1024px) {
    .jury-wrapper { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .j-header { font-size: 16px; padding: 12px 15px; }
    .j-body { padding: 15px; }
    .score-form { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .score-group input { padding: 10px; font-size: 14px; }
    .btn-score { padding: 14px; font-size: 15px; margin-top: 10px; }
    .film-block { padding: 12px; }
    .film-title { font-size: 15px; }
}
@media (max-width: 480px) {
    .score-form { grid-template-columns: 1fr; }
    .score-group input { padding: 12px; }
    .film-meta { font-size: 12px; }
}
</style>

<div class="jury-wrapper">
    <!-- Оценить -->
    <div class="j-card">
        <div class="j-header">🎬 Фильмы для оценки</div>
        <div class="j-body">
            @forelse($unscored as $film)
<div class="film-block">
    <!-- Шапка с постером и инфо -->
    <div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:12px;">
        @if($film->poster_path)
            <img src="{{ asset('storage/' . $film->poster_path) }}" alt="Постер" 
                 style="width:100px; height:140px; object-fit:cover; border-radius:6px; flex-shrink:0; background:#eee;">
        @else
            <div style="width:100px; height:140px; background:#eee; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#999; flex-shrink:0;">🖼 Нет постера</div>
        @endif
        
        <div style="flex:1; min-width:200px;">
            <div class="film-title" style="font-size:17px; margin-bottom:6px;">{{ $film->title }}</div>
            <div class="film-meta" style="margin-bottom:8px;">
                🎭 {{ $film->genre }} | 🌍 {{ $film->country }} | 📅 {{ $film->year }} | ⏱ {{ $film->duration }} мин<br>
                🎥 Режиссёр: {{ $film->director }}
            </div>
            @if($film->trailer_url)
                <a href="{{ $film->trailer_url }}" target="_blank" style="color:#8e44ad; font-size:13px; text-decoration:none; font-weight:500;">🎞 Смотреть трейлер</a>
            @endif
        </div>
    </div>
    
    <!-- Синопсис -->
    @if($film->synopsis)
    <div style="background:#fff; padding:10px; border-radius:6px; margin-bottom:12px; font-size:13px; color:#555; line-height:1.4; border-left:3px solid #8e44ad;">
        <strong>Синопсис:</strong><br>
        {{ Str::limit($film->synopsis, 200) }}
    </div>
    @endif
    
    <!-- Форма оценки -->
    <form method="POST" action="/jury/films/score">
        @csrf
        <input type="hidden" name="film_id" value="{{ $film->id }}">
        <div class="score-form">
            <div class="score-group"><label>Сценарий</label><input type="number" step="0.1" min="0" max="10" name="criterion_script" required></div>
            <div class="score-group"><label>Режиссура</label><input type="number" step="0.1" min="0" max="10" name="criterion_director" required></div>
            <div class="score-group"><label>Актёрская игра</label><input type="number" step="0.1" min="0" max="10" name="criterion_acting" required></div>
            <div class="score-group"><label>Операторская</label><input type="number" step="0.1" min="0" max="10" name="criterion_cinematography" required></div>
            <div class="score-group"><label>Звук</label><input type="number" step="0.1" min="0" max="10" name="criterion_sound" required></div>
            <button type="submit" class="btn-score">✅ Сохранить оценку</button>
        </div>
    </form>
</div>
@empty
<div class="empty-state">Нет фильмов, требующих оценки. Все одобренные работы уже оценены.</div>
@endforelse
        </div>
    </div>

    <!-- Мои оценки -->
    <div class="j-card">
        <div class="j-header">📊 Мои выставленные оценки</div>
        <div class="j-body">
            @forelse($scored as $film)
    @php $score = $film->juryScores->first(); @endphp
    <div class="film-block scored-card">
        <!-- Шапка с постером -->
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:10px;">
            @if($film->poster_path)
                <img src="{{ asset('storage/' . $film->poster_path) }}" alt="Постер" 
                     style="width:80px; height:110px; object-fit:cover; border-radius:6px; flex-shrink:0;">
            @endif
            <div style="flex:1;">
                <div class="film-title">{{ $film->title }}</div>
                <div class="film-meta">🎭 {{ $film->genre }} | 📅 {{ $film->year }} | ⏱ {{ $film->duration }} мин</div>
            </div>
        </div>
        
        <!-- Оценки -->
        <div class="score-row"><span>Сценарий</span><span>{{ number_format($score->criterion_script, 1) }}/10</span></div>
        <div class="score-row"><span>Режиссура</span><span>{{ number_format($score->criterion_director, 1) }}/10</span></div>
        <div class="score-row"><span>Актёрская игра</span><span>{{ number_format($score->criterion_acting, 1) }}/10</span></div>
        <div class="score-row"><span>Операторская работа</span><span>{{ number_format($score->criterion_cinematography, 1) }}/10</span></div>
        <div class="score-row"><span>Звук</span><span>{{ number_format($score->criterion_sound, 1) }}/10</span></div>
        <div class="score-row" style="font-size:14px;"><span>⭐ Средний балл</span><span>{{ number_format($score->total_score, 2) }}/10</span></div>
    </div>
@empty
<div class="empty-state">Вы ещё не оценили ни один фильм.</div>
@endforelse
        </div>
    </div>
</div>
@endsection