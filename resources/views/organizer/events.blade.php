@extends('layouts.app')

@section('content')
<style>
    .organizer-wrapper { display: flex; gap: 30px; flex-wrap: wrap; align-items: flex-start; }
    .organizer-sidebar { flex: 0 0 380px; min-width: 300px; }
    .organizer-main { flex: 1; min-width: 0; }
    
    .organizer-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; overflow: hidden; margin-bottom: 20px; }
    .organizer-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #fff; padding: 15px 20px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
    .organizer-body { padding: 20px; }
    
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #555; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9; font-size: 14px; transition: 0.2s; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #3498db; background: #fff; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
    .btn-create { width: 100%; padding: 12px; background: linear-gradient(to right, #27ae60, #2ecc71); color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 6px rgba(39, 174, 96, 0.3); transition: 0.2s; }
    .btn-create:hover { transform: translateY(-2px); box-shadow: 0 6px 10px rgba(39, 174, 96, 0.4); }
    
    .styled-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .styled-table th { padding: 12px 15px; background: #f1f5f9; color: #4a5568; font-weight: 700; text-align: left; border-bottom: 2px solid #e2e8f0; }
    .styled-table td { padding: 12px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .styled-table tr:hover { background-color: #fafafa; }
    
    /* Статус (только для просмотра) */
    .status-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; color: #fff; white-space: nowrap; }
    .st-draft { background: #f39c12; }
    .st-published { background: #27ae60; }
    .st-completed { background: #7f8c8d; }
    .st-cancelled { background: #e74c3c; }
    
    /* Кнопки действий */
    .actions-cell { white-space: nowrap; }
    .action-group { display: flex; flex-direction: column; gap: 6px; min-width: 110px; }
    .action-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 8px 0; border: none; border-radius: 6px; cursor: pointer; transition: 0.2s; font-size: 13px; font-weight: 500; text-decoration: none; color: #fff; width: 100%;
    }
    .btn-edit { background: #3498db; }
    .btn-edit:hover { background: #2980b9; transform: translateY(-1px); }
    .btn-archive { background: #f39c12; }
    .btn-archive:hover { background: #e67e22; transform: translateY(-1px); }
    .btn-delete { background: #e74c3c; }
    .btn-delete:hover { background: #c0392b; transform: translateY(-1px); }
    @media (max-width: 900px) {
    .organizer-wrapper { flex-direction: column; }
    .organizer-sidebar, .organizer-main { width: 100% !important; flex: none !important; }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -20px; padding: 0 20px; }
    .styled-table { min-width: 650px; }
    .actions-cell { min-width: 130px; }
    .action-group { flex-direction: row; gap: 5px; }
    .action-btn { width: auto; padding: 6px 8px; font-size: 12px; }
}
/* === Кнопка "Опубликовать" (перекрывает все базовые стили) === */
.action-btn.btn-publish {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 5px rgba(39, 174, 96, 0.3) !important;
}
.action-btn.btn-publish:hover {
    background: linear-gradient(135deg, #219150 0%, #27ae60 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(39, 174, 96, 0.4) !important;
}
/* === Единый шрифт для всех кнопок проекта === */
.btn, .action-btn, .header-btn, .btn-submit, .btn-create, .btn-apply, 
.btn-vote, .btn-publish, .btn-archive, .btn-edit, .btn-delete, .btn-score, .btn-buy, .btn-review {
    font-family: inherit !important;          /* Наследует 'Segoe UI' из body */
    font-size: 14px !important;               /* Базовый размер */
    font-weight: 600 !important;              /* Чёткое полужирное начертание */
    letter-spacing: 0.2px;                    /* Лёгкий разряд для читаемости */
}
/* Табличные кнопки действий чуть компактнее, но в том же стиле */
.action-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 10px 14px !important; /* ✅ Добавлены равномерные отступы со всех сторон */
    border: none; border-radius: 6px; cursor: pointer; transition: 0.2s; 
    font-size: 13px; font-weight: 600; text-decoration: none; color: #fff; width: 100%;
    box-sizing: border-box; /* Гарантирует, что padding не вылезет за границы */
}
</style>

<div class="organizer-wrapper">
    <!-- Левая колонка: Создание -->
    <div class="organizer-sidebar">
        <div class="organizer-card">
            <div class="organizer-header">📅 Новое мероприятие</div>
            <div class="organizer-body">
                @if($errors->any())
                    <div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; margin-bottom:15px; font-size:13px;">Исправьте ошибки в форме.</div>
                @endif
                <form method="POST" action="/organizer/events">
                    @csrf
                    <div class="form-group"><label class="form-label">Название</label><input type="text" name="title" class="form-control" placeholder="Премьера фильма..." required></div>
                    <div class="form-group"><label class="form-label">Описание</label><textarea name="description" class="form-control" rows="3" required></textarea></div>
                    <div class="form-group"><label class="form-label">Дата начала</label><input type="datetime-local" name="start_time" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Дата окончания</label><input type="datetime-local" name="end_time" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Площадка</label><input type="text" name="venue" class="form-control" placeholder="Кинотеатр Звезда" required></div>
                    <div class="form-group"><label class="form-label">Вместимость</label><input type="number" name="capacity" class="form-control" placeholder="100" required></div>
                    <button type="submit" class="btn-create">Создать</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Правая колонка: Список -->
    <div class="organizer-main">
        <div class="organizer-card">
            <div class="organizer-header">
                <span>🎬 Управление мероприятиями</span>
                <span style="font-size: 13px; opacity: 0.9;">Всего: {{ $events->count() }}</span>
            </div>
            <div class="organizer-body" style="padding: 0;">
                <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th width="40">ID</th>
                            <th>Название</th>
                            <th>Дата и время</th>
                            <th>Площадка</th>
                            <th width="130">Статус</th>
                            <th width="130" class="text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td style="color: #999;">{{ $event->id }}</td>
                            <td style="font-weight: 600;">{{ $event->title }}</td>
                            <td style="color: #666; font-size: 13px;">{{ \Carbon\Carbon::parse($event->start_time)->format('d.m.Y H:i') }}</td>
                            <td>{{ $event->venue }}</td>
                            <td>
                                <span class="status-badge st-{{ $event->status }}">
                                    @switch($event->status)
                                        @case('draft') 📝 Черновик @break
                                        @case('published') ✅ Опубликован @break
                                        @case('completed') 📦 В архиве @break
                                        @case('cancelled') ❌ Отменён @break
                                        @default {{ $event->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="actions-cell">
                                <div class="action-group">
                                    @if($event->status == 'draft')
                                    <form method="POST" action="/organizer/events/{{ $event->id }}/publish">
                                        @csrf @method('PUT')
                                        <button type="submit" class="action-btn btn-publish" title="Опубликовать для участников">📢 Опубликовать</button>                                    </form>
                                    @endif

                                    <a href="/organizer/events/{{ $event->id }}/edit" class="action-btn btn-edit">✏️ Изменить</a>
                                    
                                    @if($event->status != 'completed')
                                    <form method="POST" action="/organizer/events/{{ $event->id }}/archive">
                                        @csrf @method('PUT')
                                        <button type="submit" class="action-btn btn-archive">📦 В архив</button>
                                    </form>
                                    @endif

                                    <form method="POST" action="/organizer/events/{{ $event->id }}" onsubmit="return confirm('Удалить навсегда?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn btn-delete">🗑 Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align: center; padding: 40px; color: #777;">Мероприятий нет. Создайте первое через форму слева.</td></tr>
                        @endforelse
                    </tbody>
                </table>
</div>
            </div>
        </div>
    </div>
</div>
@endsection