@extends('layouts.app')

@section('content')

<style>
    /* === Стили для панели администратора === */
    .admin-wrapper {
        display: flex;
        gap: 30px; /* Отступ между формой и таблицей */
        flex-wrap: wrap; /* Адаптивность для мобильных */
        align-items: flex-start; /* Выравнивание по верху */
    }

    /* Увеличиваем ширину боковой панели, чтобы полям было просторно */
.admin-sidebar {
    flex: 0 0 380px !important; /* Было 320px */
    min-width: 320px;
}

/* Оптимальная ширина поля роли */
.form-group select[name="role"] {
    width: 230px;       /* Точно помещается самое длинное слово */
    max-width: 100%;    /* Не вылезает за границы на мобильных */
    padding: 10px 28px 10px 12px; /* Отступ справа под стрелку */
}

    .admin-main {
        flex: 1; /* Таблица занимает всё остальное место */
        min-width: 0; /* Предотвращает переполнение */
    }

    /* Стили карточек */
    .admin-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08); /* Мягкая тень */
        border: 1px solid #eee;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .admin-card-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: #fff;
        padding: 15px 20px;
        font-size: 16px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .admin-card-body {
        padding: 20px;
    }

    /* Форма */
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #555; }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: #f9f9f9;
        font-size: 14px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #3498db;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .btn-submit {
        width: 100%;
        padding: 12px;
        background: linear-gradient(to right, #3498db, #2980b9);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(52, 152, 219, 0.3);
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .btn-submit:active {
        transform: scale(0.98);
        box-shadow: 0 2px 3px rgba(52, 152, 219, 0.2);
    }

    /* Таблица */
    .styled-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .styled-table thead tr {
        background-color: #f1f5f9;
        color: #4a5568;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
    }
    .styled-table th {
        padding: 14px 15px;
        font-weight: 700;
    }
    .styled-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eef2f6;
    }
    .styled-table tbody tr:hover {
        background-color: #f8fafc; /* Подсветка при наведении */
    }
    .styled-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Кнопки действий в таблице */
    .btn-action {
        padding: 6px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: 0.2s;
        margin-right: 5px;
    }
    .btn-warning {
        background-color: #f39c12;
        color: white;
        box-shadow: 0 2px 3px rgba(243, 156, 18, 0.3);
    }
    .btn-warning:hover { background-color: #e67e22; }
    
    .btn-danger {
        background-color: #e74c3c;
        color: white;
        box-shadow: 0 2px 3px rgba(231, 76, 60, 0.3);
    }
    .btn-danger:hover { background-color: #c0392b; }

    .btn-success {
        background-color: #27ae60;
        color: white;
        box-shadow: 0 2px 3px rgba(39, 174, 96, 0.3);
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }
    .status-active { background: #d4edda; color: #155724; }
    .status-inactive { background: #fadbd8; color: #721c24; }
    @media (max-width: 900px) {
    .admin-wrapper { flex-direction: column; }
    .admin-sidebar, .admin-main { flex: 1 1 100% !important; width: 100% !important; min-width: 0 !important; }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -20px; padding: 0 20px; }
    .styled-table { min-width: 650px; }
    .action-group { flex-direction: row; gap: 5px; }
    .action-btn { width: auto; padding: 6px 10px; font-size: 12px; }
}
</style>

<div class="admin-wrapper">
    
    <!-- Левая колонка: Форма -->
    <div class="admin-sidebar">
        <div class="admin-card">
            <div class="admin-card-header">Регистрация сотрудников</div>
            <div class="admin-card-body">
                <form method="POST" action="/admin/users">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Имя пользователя</label>
                        <input type="text" name="name" class="form-control" placeholder="Иван Иванов" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email адрес</label>
                        <input type="email" name="email" class="form-control" placeholder="user@kiff.ru" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" placeholder="Минимум 6 символов" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Роль в системе</label>
                        <select name="role" class="form-control" required>
                            <option value="" disabled selected>Выберите роль</option>
                            <option value="admin">Администратор</option>
                            <option value="organizer">Организатор</option>
                            <option value="jury">Член жюри</option>
                            <option value="participant">Участник</option>
                            <option value="visitor">Зритель</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-submit">Создать учетную запись</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Правая колонка: Таблица -->
    <div class="admin-main">
        <div class="admin-card">
            <div class="admin-card-header">
                Контроль пользователей
                <span style="float: right; font-size: 12px; opacity: 0.8;">Всего: {{ $users->count() }}</span>
            </div>
            <div class="admin-card-body" style="padding: 0;"> 
                <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th width="140">Роль</th>
                            <th width="100" class="text-center">Статус</th>
                            <th width="160" class="text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td style="color: #888;">{{ $user->id }}</td>
                            <td style="font-weight: bold;">{{ $user->name }}</td>
                            <td style="color: #555;">{{ $user->email }}</td>
                            
                            <!-- Смена роли -->
                            <td>
                                <form method="POST" action="/admin/users/{{ $user->id }}/role" style="display:inline;">
                                    @csrf @method('PUT')
                                    <select name="role" style="padding: 4px; border-radius: 4px; border: 1px solid #ccc; font-size: 12px;" onchange="this.form.submit()">
                                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Администратор</option>
                                        <option value="organizer" {{ $user->role == 'organizer' ? 'selected' : '' }}>Организатор</option>
                                        <option value="jury" {{ $user->role == 'jury' ? 'selected' : '' }}>Жюри</option>
                                        <option value="participant" {{ $user->role == 'participant' ? 'selected' : '' }}>Участник</option>
                                        <option value="visitor" {{ $user->role == 'visitor' ? 'selected' : '' }}>Зритель</option>
                                    </select>
                                </form>
                            </td>
                            
                            <!-- Статус -->
                            <td class="text-center">
                                <span class="status-badge {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $user->is_active ? 'Активен' : 'Блок' }}
                                </span>
                            </td>
                            
                            <!-- Кнопки -->
                            <td class="text-center">
                                <form method="POST" action="/admin/users/{{ $user->id }}/status" style="display:inline;">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn-action {{ $user->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $user->is_active ? 'Заблокировать' : 'Разблокировать' }}">
                                        {{ $user->is_active ? '🚫' : '✅' }}
                                    </button>
                                </form>
                                
                                @if($user->id !== auth()->id())
                                <form method="POST" action="/admin/users/{{ $user->id }}" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить пользователя?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-danger" title="Удалить">🗑</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #777;">
                                В системе пока нет зарегистрированных пользователей.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection