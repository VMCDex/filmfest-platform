@extends('layouts.app')

@section('content')
<style>
    .edit-wrapper { max-width: 750px; margin: 0 auto; }
    .edit-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden; }
    .edit-header { background: linear-gradient(135deg, #3498db, #2980b9); color: #fff; padding: 20px; font-size: 18px; font-weight: bold; }
    .edit-body { padding: 30px; }
    
    .form-row { display: flex; gap: 20px; margin-bottom: 0; }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 600; color: #555; font-size: 14px; }
    .form-control {
        width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; font-size: 14px; transition: 0.2s; box-sizing: border-box;
    }
    .form-control:focus { border-color: #3498db; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
    
    .btn-row { 
        display: flex; gap: 15px; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; 
    }
    .btn {
        flex: 1; padding: 14px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.2s; text-align: center; text-decoration: none;
    }
    .btn-primary { background: linear-gradient(to right, #3498db, #2980b9); color: #fff; box-shadow: 0 4px 6px rgba(52, 152, 219, 0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 10px rgba(52, 152, 219, 0.4); }
    .btn-secondary { background: #ecf0f1; color: #7f8c8d; }
    .btn-secondary:hover { background: #d5dbdb; color: #555; }
    @media (max-width: 600px) {
    .form-row { flex-direction: column; gap: 0; }
    .edit-body { padding: 20px 15px; }
    .btn-row { flex-direction: column; gap: 10px; }
    .edit-header { font-size: 16px; padding: 15px; }
}
</style>

<div class="edit-wrapper">
    <div class="edit-card">
        <div class="edit-header">✏️ Редактирование: {{ $event->title }}</div>
        <div class="edit-body">
            <form method="POST" action="/organizer/events/{{ $event->id }}">
                @csrf @method('PUT')
                
                <div class="form-group">
                    <label class="form-label">Название мероприятия</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ old('description', $event->description) }}</textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Дата и время начала</label>
                        <input type="datetime-local" name="start_time" class="form-control" value="{{ old('start_time', date('Y-m-d\TH:i', strtotime($event->start_time))) }}" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Дата и время окончания</label>
                        <input type="datetime-local" name="end_time" class="form-control" value="{{ old('end_time', date('Y-m-d\TH:i', strtotime($event->end_time))) }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Площадка</label>
                        <input type="text" name="venue" class="form-control" value="{{ old('venue', $event->venue) }}" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Вместимость (чел.)</label>
                        <input type="number" name="capacity" class="form-control" min="1" value="{{ old('capacity', $event->capacity) }}" required>
                    </div>
                </div>
                
                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">💾 Сохранить изменения</button>
                    <a href="/organizer/events" class="btn btn-secondary">↩️ Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection