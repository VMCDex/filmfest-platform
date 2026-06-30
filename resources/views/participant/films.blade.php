@extends('layouts.app')

@section('content')
<style>
    .participant-wrapper { display: flex; gap: 30px; flex-wrap: wrap; align-items: flex-start; }
    .p-sidebar { flex: 0 0 380px; min-width: 300px; }
    .p-main { flex: 1; min-width: 0; }
    .p-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; overflow: hidden; margin-bottom: 20px; }
    .p-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #fff; padding: 15px 20px; font-weight: bold; }
    .p-body { padding: 20px; }
    .form-group { margin-bottom: 14px; }
    .form-label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #555; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9; font-size: 14px; transition: 0.2s; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #3498db; background: #fff; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
    .btn-add { width: 100%; padding: 12px; background: linear-gradient(to right, #3498db, #2980b9); color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 10px; }
    .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 10px rgba(52, 152, 219, 0.4); }
    .film-card { background: #fafafa; border-left: 4px solid #f39c12; padding: 15px; border-radius: 8px; margin-bottom: 12px; }
    .film-card.approved { border-left-color: #27ae60; background: #f0fdf4; }
    .film-card.rejected { border-left-color: #e74c3c; background: #fef2f2; }
    .film-title { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
    .film-meta { font-size: 13px; color: #666; line-height: 1.4; }
    .status-pill { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; color: #fff; margin-top: 6px; }
    .st-pending { background: #f39c12; } .st-approved { background: #27ae60; } .st-rejected { background: #e74c3c; }
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

<div class="participant-wrapper">
    <!-- Форма -->
    <div class="p-sidebar">
        <div class="p-card">
            <div class="p-header">🎬 Добавить фильм</div>
            <div class="p-body">
                <form method="POST" action="/participant/films" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group"><label class="form-label">Название</label><input type="text" name="title" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Синопсис</label><textarea name="synopsis" class="form-control" rows="3" required></textarea></div>
                    <div class="form-group"><label class="form-label">Режиссёр</label><input type="text" name="director" class="form-control" required></div>
                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1;"><label class="form-label">Страна</label><input type="text" name="country" class="form-control" required></div>
                        <div style="flex:1;"><label class="form-label">Год</label><input type="number" name="year" class="form-control" min="1900" max="2099" required></div>
                    </div>
                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1;"><label class="form-label">Жанр</label><input type="text" name="genre" class="form-control" required></div>
                        <div style="flex:1;"><label class="form-label">Хронометраж (мин)</label><input type="number" name="duration" class="form-control" min="1" required></div>
                    </div>
                    <div class="form-group"><label class="form-label">Постер (jpg/png)</label><input type="file" name="poster" class="form-control" accept="image/*"></div>
                    <div class="form-group">
                        <label class="form-label">Ссылка на трейлер</label>
                        <input type="url" name="trailer_url" class="form-control" placeholder="https://youtube.com/... или прямая ссылка на .mp4">
                    </div>                  
                    <button type="submit" class="btn-add">Отправить на проверку</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Список -->
    <div class="p-main">
        <div class="p-card">
            <div class="p-header">Мои фильмы</div>
            <div class="p-body">
                @forelse($films as $film)
                <div class="film-card {{ $film->status }}">
                    <div class="film-title">{{ $film->title }}</div>
                    <div class="film-meta">
                        🎭 {{ $film->genre }} | 🌍 {{ $film->country }} | 📅 {{ $film->year }} | ⏱ {{ $film->duration }} мин<br>
                        🎥 Режиссёр: {{ $film->director }}
                    </div>
                    <span class="status-pill st-{{ $film->status }}">
                        @switch($film->status)
                            @case('pending') ⏳ На проверке @break
                            @case('approved') ✅ Допущен @break
                            @case('rejected') ❌ Отклонён @break
                        @endswitch
                    </span>
                    @if($film->poster_path) <div class="film-meta" style="margin-top:5px;">🖼 Постер загружен</div> @endif
                    @if($film->trailer_url) 
                        <div class="film-meta" style="margin-top:5px;">🔗 <a href="{{ $film->trailer_url }}" target="_blank" style="color:#3498db;">Смотреть трейлер</a></div> 
                    @endif                    
                    @if($film->status == 'rejected' && $film->applications->isNotEmpty())
                        <div class="film-meta" style="color:#c0392b; margin-top:5px;">💬 Причина: {{ $film->applications->last()->comment ?? 'Не указана' }}</div>
                    @endif
                </div>
                @empty
                <p style="color:#777; text-align:center; padding:30px;">У вас пока нет загруженных фильмов.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection