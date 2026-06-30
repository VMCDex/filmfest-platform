@extends('layouts.app')

@section('content')
<style>
    .visitor-grid { display: grid; gap: 25px; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
    .v-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; overflow: hidden; }
    .v-header { background: linear-gradient(135deg, #16a085 0%, #1abc9c 100%); color: #fff; padding: 15px 20px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
    .v-body { padding: 20px; }
    
    .event-item { border-bottom: 1px solid #f0f0f0; padding: 15px 0; }
    .event-item:last-child { border-bottom: none; }
    .event-title { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
    .event-meta { font-size: 13px; color: #666; margin-bottom: 10px; }
    .ticket-form { display: flex; gap: 8px; align-items: center; margin-top: 8px; }
    .ticket-form input { flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
    .btn-buy { background: #16a085; color: #fff; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: 0.2s; white-space: nowrap; }
    .btn-buy:hover { background: #12876f; transform: translateY(-1px); }

    .film-grid { display: grid; gap: 15px; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
    .film-mini { background: #fafafa; padding: 12px; border-radius: 8px; border-left: 4px solid #16a085; }
    .film-mini h4 { margin: 0 0 4px; font-size: 14px; }
    .film-mini p { margin: 0 0 8px; font-size: 12px; color: #777; }
    .btn-vote { width: 100%; background: #8e44ad; color: #fff; border: none; padding: 8px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; }
    .btn-vote:hover { background: #7d3c98; }

    .review-form textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; resize: vertical; min-height: 60px; margin-bottom: 8px; }
    .rating-stars { display: flex; gap: 5px; margin-bottom: 10px; }
    .star { cursor: pointer; font-size: 22px; color: #ddd; transition: 0.2s; }
    .star.active { color: #f1c40f; }
    .btn-review { background: #e67e22; color: #fff; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-weight: 600; }
    
    .ticket-badge { background: #e8f8f5; padding: 10px; border-radius: 8px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .ticket-badge strong { color: #16a085; }

    @media (max-width: 768px) {
        .visitor-grid { grid-template-columns: 1fr; }
        .film-grid { grid-template-columns: 1fr; }
        .ticket-form { flex-direction: column; align-items: stretch; }
        .v-header { font-size: 15px; }
    }
</style>

<div class="visitor-grid">
    <!-- Афиша и билеты -->
    <div class="v-card">
        <div class="v-header">🎟️ Афиша мероприятий</div>
        <div class="v-body">
            @forelse($events as $event)
            <div class="event-item">
                <div class="event-title">{{ $event->title }}</div>
                <div class="event-meta">📅 {{ \Carbon\Carbon::parse($event->start_time)->format('d.m.Y H:i') }} | 📍 {{ $event->venue }}</div>
                <div class="event-meta">💡 Цена: <strong>{{ number_format($event->capacity > 0 ? 500 : 0, 0, '.', ' ') }} ₽</strong></div>
                
                <form method="POST" action="/visitor/tickets/{{ $event->id }}" class="ticket-form">
                    @csrf
                    <input type="text" name="seat_number" placeholder="Место (напр. A-12)" required>
                    <input type="hidden" name="price" value="500">
                    <button type="submit" class="btn-buy">Купить билет</button>
                </form>
            </div>
            @empty
            <p style="color:#777; text-align:center; padding:20px;">Нет открытых мероприятий.</p>
            @endforelse
        </div>
    </div>

    <!-- Голосование -->
<div class="v-card">
    <div class="v-header">🗳️ Голосование за фильмы</div>
    <div class="v-body">
        <div class="film-grid">
            @forelse($films as $film)
            <div class="film-mini" style="border-left-color:#16a085; background:#fff; padding:15px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                <!-- Постер -->
                @if($film->poster_path)
                    <img src="{{ asset('storage/' . $film->poster_path) }}" alt="Постер" 
                         style="width:100%; height:180px; object-fit:cover; border-radius:6px; margin-bottom:10px; background:#eee;">
                @else
                    <div style="width:100%; height:180px; background:#eee; border-radius:6px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:#999;">🖼 Нет постера</div>
                @endif
                
                <!-- Инфо -->
                <h4 style="margin:0 0 6px; font-size:15px; font-weight:700;">{{ $film->title }}</h4>
                <p style="margin:0 0 8px; font-size:12px; color:#666;">
                    🎭 {{ $film->genre }} • 🌍 {{ $film->country }} • 📅 {{ $film->year }} • ⏱ {{ $film->duration }} мин<br>
                    🎥 {{ $film->director }}
                </p>
                
                <!-- Синопсис -->
                @if($film->synopsis)
                <p style="margin:0 0 8px; font-size:12px; color:#777; line-height:1.3;">
                    {{ Str::limit($film->synopsis, 90) }}
                </p>
                @endif
                
                <!-- Трейлер -->
                @if($film->trailer_url)
                    <a href="{{ $film->trailer_url }}" target="_blank" 
                       style="display:inline-block; margin-bottom:10px; color:#16a085; font-size:12px; text-decoration:none; font-weight:500;">
                        🎞 Смотреть трейлер
                    </a>
                @endif
                
                <!-- Кнопка голосования -->
                <form method="POST" action="/visitor/votes/{{ $film->id }}">
                    @csrf
                    <button type="submit" class="btn-vote" style="width:100%; background:#8e44ad; color:#fff; border:none; padding:8px; border-radius:6px; cursor:pointer; font-weight:600; transition:0.2s;">
                        🗳 Голосовать
                    </button>
                </form>
            </div>
            @empty
            <p style="color:#777; grid-column: 1/-1; text-align:center; padding:20px;">Нет фильмов, доступных для голосования.</p>
            @endforelse
        </div>
    </div>
</div>

   <!-- Отзывы -->
<div class="v-card">
    <div class="v-header">💬 Написать отзыв</div>
    <div class="v-body">
        <form method="POST" action="/visitor/reviews/{{ $films->first()->id ?? 0 }}">
            @csrf
            <label class="form-label">Выберите фильм</label>
            <select name="film_id_for_review" class="form-control" style="margin-bottom:10px; width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;" onchange="updateFilmInfo(this.value)">
                <option value="">-- Выберите фильм --</option>
                @foreach($films as $film)
                    <option value="{{ $film->id }}" 
                            data-title="{{ $film->title }}"
                            data-poster="{{ $film->poster_path ? asset('storage/' . $film->poster_path) : '' }}"
                            data-synopsis="{{ Str::limit($film->synopsis, 120) }}">
                        {{ $film->title }} ({{ $film->year }})
                    </option>
                @endforeach
            </select>
            
            <!-- Превью фильма (появляется при выборе) -->
            <div id="filmPreview" style="display:none; background:#fafafa; padding:10px; border-radius:6px; margin-bottom:10px;">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <img id="previewPoster" src="" alt="Постер" style="width:60px; height:80px; object-fit:cover; border-radius:4px; background:#eee;">
                    <div style="flex:1; min-width:150px;">
                        <strong id="previewTitle" style="font-size:13px;"></strong>
                        <p id="previewSynopsis" style="font-size:11px; color:#666; margin:4px 0 0;"></p>
                    </div>
                </div>
            </div>
            
            <label class="form-label">Ваша оценка</label>
            <div class="rating-stars" id="starRating">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <input type="hidden" name="rating" id="ratingValue" value="5" required>
            
            <label class="form-label">Текст отзыва</label>
            <textarea name="text" class="form-control" placeholder="Поделитесь впечатлениями..." required></textarea>
            
            <button type="submit" class="btn-review" style="margin-top:10px;">Отправить отзыв</button>
        </form>
    </div>
</div>

    <!-- Мои билеты -->
    <div class="v-card">
        <div class="v-header">🎫 Мои билеты</div>
        <div class="v-body">
            @forelse($myTickets as $ticket)
            <div class="ticket-badge">
                <div>
                    <strong>{{ $ticket->event->title ?? 'Удалено' }}</strong><br>
                    <span style="font-size:12px; color:#555;">Место: {{ $ticket->seat_number }} | {{ $ticket->created_at->format('d.m.Y') }}</span>
                </div>
                <span style="background:#27ae60; color:#fff; padding:4px 8px; border-radius:10px; font-size:11px;">Оплачен</span>
            </div>
            @empty
            <p style="color:#777; text-align:center; padding:20px;">У вас пока нет билетов.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    // Простой скрипт для звёзд рейтинга
    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function() {
            let val = this.getAttribute('data-value');
            document.getElementById('ratingValue').value = val;
            document.querySelectorAll('.star').forEach(s => s.classList.remove('active'));
            for(let i=1; i<=val; i++) document.querySelector(`.star[data-value="${i}"]`).classList.add('active');
        });
    });
    // Инициализация 5 звёзд по умолчанию
    document.querySelectorAll('.star').forEach(s => s.classList.add('active'));

    // Превью фильма при выборе в отзыве
function updateFilmInfo(filmId) {
    const select = document.querySelector(`select[name="film_id_for_review"]`);
    const option = select.querySelector(`option[value="${filmId}"]`);
    const preview = document.getElementById('filmPreview');
    
    if (option && filmId) {
        document.getElementById('previewTitle').textContent = option.dataset.title;
        document.getElementById('previewSynopsis').textContent = option.dataset.synopsis;
        document.getElementById('previewPoster').src = option.dataset.poster || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="80" viewBox="0 0 60 80"%3E%3Crect fill="%23eee" width="60" height="80"/%3E%3Ctext fill="%23999" font-size="12" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3E🖼%3C/text%3E%3C/svg%3E';
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endsection