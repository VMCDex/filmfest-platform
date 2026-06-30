<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчёт KIFF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 6px; flex: 1; text-align: center; }
        .stat-box strong { display: block; font-size: 24px; color: #3498db; }
    </style>
</head>
<body>
    <h1>Отчёт по мероприятиям организатора</h1>
    <div class="stats">
        <div class="stat-box"><strong>{{ $stats['total_apps'] }}</strong>Всего заявок</div>
        <div class="stat-box"><strong>{{ $stats['approved'] }}</strong>Одобрено</div>
        <div class="stat-box"><strong>{{ $stats['rejected'] }}</strong>Отклонено</div>
    </div>
    <table>
        <thead><tr><th>ID</th><th>Фильм</th><th>Режиссёр</th><th>Мероприятие</th><th>Статус</th><th>Дата</th></tr></thead>
        <tbody>
            @foreach($applications as $app)
            <tr>
                <td>{{ $app->id }}</td>
                <td>{{ $app->film->title ?? '-' }}</td>
                <td>{{ $app->film->director ?? '-' }}</td>
                <td>{{ $app->event->title ?? '-' }}</td>
                <td>{{ $app->status }}</td>
                <td>{{ $app->created_at->format('d.m.Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>