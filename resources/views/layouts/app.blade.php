<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Krasnodar International Filmfest</title>
  <style>
    /* === Основные настройки === */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        background-color: #FFFFE0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        line-height: 1.6;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* === Хедер === */
    .header {
        background-color: #ECF0F1;
        padding: 12px 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        position: sticky; /* Хедер прилипает к верху при скролле */
        top: 0;
        z-index: 1000;
    }
    .header-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* === Логотип (обновленный) === */
    .logo-rect {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 140px;
        height: 46px;
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
        color: #ffffff;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: 2px;
        text-decoration: none;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .logo-rect::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.5s;
    }
    .logo-rect:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(52, 152, 219, 0.4); }
    .logo-rect:hover::before { transform: translateX(100%); }

    /* === Навигация и Кнопки === */
    .nav-buttons { display: flex; align-items: center; gap: 12px; }

    .header-btn {
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 6px;
        transition: all 0.3s ease;
        display: inline-block;
    }

    /* Кнопка "Вход" (Светлая, контурная) */
    .header-btn-login {
        background-color: #fff;
        color: #2c3e50;
        border: 1px solid #bdc3c7;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .header-btn-login:hover {
        border-color: #3498db;
        color: #3498db;
        background-color: #fdfdfd;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.08);
    }

    /* Кнопка "Регистрация" (Яркая, акцентная) */
    .header-btn-register {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: #fff;
        border: none;
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        text-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    .header-btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(52, 152, 219, 0.4);
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); /* Для перезагрузки градиента */
    }

    /* === Основной контент === */
    .main { flex: 1; max-width: 1200px; margin: 0 auto; padding: 40px 20px; width: 100%; }

    /* === Футер === */
    .footer {
        background-color: #ECF0F1;
        padding: 20px;
        text-align: center;
        margin-top: auto;
        border-top: 1px solid #dcdde1;
        font-size: 13px;
        color: #7f8c8d;
    }

    /* === Общие стили форм и кнопок (из предыдущих шагов) === */
    .form-container { max-width: 400px; margin: 0 auto; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #eee; overflow: hidden; }
    .card-header { padding: 20px; text-align: center; font-size: 20px; font-weight: bold; border-bottom: 1px solid #eee; }
    .card-body { padding: 30px; }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #555; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9; transition: all 0.2s; }
    .form-control:focus { outline: none; border-color: #3498db; background: #fff; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); }
    .btn-submit { width: 100%; padding: 12px; background: linear-gradient(to right, #3498db, #2980b9); color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 6px rgba(52, 152, 219, 0.3); transition: transform 0.1s; }
    .btn-submit:active { transform: scale(0.98); }
    .text-center { text-align: center; margin-top: 15px; }
    .invalid-feedback { color: #e74c3c; font-size: 12px; margin-top: 4px; }
    /* === Универсальные кнопки форм === */
.btn {
    display: inline-block;
    padding: 12px 20px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    box-shadow: 0 4px 6px rgba(52, 152, 219, 0.3);
    transition: all 0.2s ease;
}
.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 10px rgba(52, 152, 219, 0.4);
}
.btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
}
.btn-block {
    display: block;
    width: 100%;
}
@media (max-width: 768px) {
    .header-inner { flex-direction: column; gap: 12px; text-align: center; padding: 10px 0; }
    .nav-buttons { justify-content: center; flex-wrap: wrap; gap: 8px; }
    .header-btn { padding: 8px 14px; font-size: 13px; }
    .main { padding: 20px 12px; }
    .form-container { padding: 0 5px; }
}
</style>
</head>
<body>
            <header class="header">
        <div class="header-inner">
            <a href="/" class="logo-rect">KIFF</a>
            
            <nav class="nav-buttons">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="/admin/users" class="header-btn header-btn-login">👥 Админка</a>
                        <a href="/organizer/events" class="header-btn header-btn-login">🎬 Мероприятия</a>
                        <a href="/organizer/applications" class="header-btn header-btn-login">📋 Заявки</a>
                        <a href="/organizer/reports" class="header-btn header-btn-login">📊 Отчёты</a>
                    @elseif(auth()->user()->role === 'organizer')
                        <a href="/organizer/events" class="header-btn header-btn-login">🎬 Мероприятия</a>
                        <a href="/organizer/applications" class="header-btn header-btn-login">📋 Заявки</a>
                        <a href="/organizer/reports" class="header-btn header-btn-login">📊 Отчёты</a>
                        @elseif(auth()->user()->role === 'participant')
                        <a href="/participant/films" class="header-btn header-btn-login">🎬 Мои фильмы</a>
                        <a href="/participant/applications" class="header-btn header-btn-login">📤 Заявки</a>
                        @elseif(auth()->user()->role === 'jury')
                        <a href="/jury/films" class="header-btn header-btn-login">⚖️ Оценивание</a>
                        @elseif(auth()->user()->role === 'visitor')
                        <a href="/visitor" class="header-btn header-btn-login">🍿 Афиша и билеты</a>
                    @endif
                    
                    <span style="font-size:13px; color:#555; margin: 0 10px;">{{ auth()->user()->name }}</span>
                    <form method="POST" action="/logout" style="display:inline;">
                        @csrf
                        <button type="submit" class="header-btn header-btn-login" style="padding: 8px 15px; font-size:13px;">Выход</button>
                    </form>
                @else
                    <a href="/login" class="header-btn header-btn-login">Вход</a>
                    <a href="/register" class="header-btn header-btn-register">Регистрация</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="main">
        {{-- Глобальные уведомления --}}
        @if(session('success'))
            <div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #c3e6cb;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #f5c6cb;">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #f5c6cb;">
                <ul style="margin:0; padding-left:20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </main>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} Krasnodar International Filmfest. Все права защищены.</p>
    </footer>
</body>
</html>