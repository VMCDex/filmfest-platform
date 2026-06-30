@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
    .reports-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-bottom: 25px; }
    .chart-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #eee; }
    .chart-title { font-size: 16px; font-weight: 600; color: #2c3e50; margin-bottom: 15px; text-align: center; }
    .btn-export { background: #27ae60; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 500; transition: 0.2s; }
    .btn-export:hover { background: #219150; transform: translateY(-1px); }
    .btn-export.pdf { background: #8e44ad; }
    .btn-export.pdf:hover { background: #7d3c98; }
    
    .section-title { font-size: 20px; font-weight: bold; color: #2c3e50; margin: 30px 0 15px; padding-bottom: 10px; border-bottom: 2px solid #3498db; }
    
    @media (max-width: 768px) {
        .charts-grid { grid-template-columns: 1fr !important; }
        .reports-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        .reports-header > div { width: 100%; flex-wrap: wrap; }
        .btn-export { flex: 1; text-align: center; }
        .chart-card { padding: 15px; }
    }
</style>

<div class="organizer-main">
    <div class="reports-header">
        <h2 style="margin: 0; font-size: 22px;">📊 Отчёты и аналитика</h2>
        <div style="display: flex; gap: 10px;">
            <a href="/organizer/reports/export/xlsx" class="btn-export">📥 Скачать XLSX</a>
            <a href="/organizer/reports/export/pdf" target="_blank" class="btn-export pdf">🖨 Печать / PDF</a>
        </div>
    </div>

    <!-- Базовая статистика -->
    <div class="section-title">📈 Базовая статистика</div>
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">Распределение заявок по статусам</div>
            <canvas id="appsChart"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title">Динамика продаж билетов (30 дней)</div>
            <canvas id="ticketsChart"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title">Распределение оценок зрителей</div>
            <canvas id="reviewsChart"></canvas>
        </div>
    </div>

    <!-- Результаты голосования -->
    <div class="section-title">🏆 Результаты зрительского голосования</div>
    <div class="charts-grid">
        <div class="chart-card" style="grid-column: 1 / -1;">
            <div class="chart-title">Топ-10 фильмов по голосам зрителей</div>
            <canvas id="votesChart"></canvas>
        </div>
    </div>

    <!-- Оценки жюри -->
    <div class="section-title">⚖️ Оценки жюри</div>
    <div class="charts-grid">
        <div class="chart-card" style="grid-column: 1 / -1;">
            <div class="chart-title">Средние оценки жюри по фильмам (топ-10)</div>
            <canvas id="juryChart"></canvas>
        </div>
    </div>
</div>

<script>
    const appsData = @json($appsData);
    const ticketsData = @json($ticketDynamics);
    const reviewsData = @json($reviewsData);
    const votesData = @json($votesData);
    const juryData = @json($juryData);

    // 1. Заявки (Pie)
    if (appsData.data.some(v => v > 0)) {
        new Chart(document.getElementById('appsChart'), {
            type: 'pie',
            data: { labels: appsData.labels, datasets: [{ data: appsData.data, backgroundColor: ['#f39c12', '#3498db', '#27ae60', '#e74c3c'] }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    } else {
        document.getElementById('appsChart').parentElement.innerHTML = '<div style="text-align:center;color:#777;padding:40px;">📊 Нет данных</div>';
    }

    // 2. Билеты (Line)
    if (ticketsData.some(d => d.count > 0)) {
        new Chart(document.getElementById('ticketsChart'), {
            type: 'line',
            data: { labels: ticketsData.map(d => d.date), datasets: [{ label: 'Продано билетов', data: ticketsData.map(d => d.count), borderColor: '#3498db', backgroundColor: 'rgba(52, 152, 219, 0.1)', fill: true, tension: 0.3 }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    } else {
        document.getElementById('ticketsChart').parentElement.innerHTML = '<div style="text-align:center;color:#777;padding:40px;">🎫 Нет данных</div>';
    }

    // 3. Отзывы (Bar)
    if (reviewsData.data.some(v => v > 0)) {
        new Chart(document.getElementById('reviewsChart'), {
            type: 'bar',
            data: { labels: reviewsData.labels, datasets: [{ label: 'Количество отзывов', data: reviewsData.data, backgroundColor: ['#e74c3c', '#f39c12', '#f1c40f', '#2ecc71', '#27ae60'] }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    } else {
        document.getElementById('reviewsChart').parentElement.innerHTML = '<div style="text-align:center;color:#777;padding:40px;">💬 Нет данных</div>';
    }

    // 4. Голоса зрителей (Bar - горизонтальный)
    if (votesData.data.some(v => v > 0)) {
        new Chart(document.getElementById('votesChart'), {
            type: 'bar',
            data: {
                labels: votesData.labels,
                datasets: [{
                    label: 'Количество голосов',
                    data: votesData.data,
                    backgroundColor: 'rgba(52, 152, 219, 0.8)',
                    borderColor: '#3498db',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Горизонтальный график
                responsive: true,
                scales: { x: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    } else {
        document.getElementById('votesChart').parentElement.innerHTML = '<div style="text-align:center;color:#777;padding:40px;">🗳 Голосов пока нет</div>';
    }

    // 5. Оценки жюри (Bar - горизонтальный)
    if (juryData.data.some(v => v > 0)) {
        new Chart(document.getElementById('juryChart'), {
            type: 'bar',
            data: {
                labels: juryData.labels,
                datasets: [{
                    label: 'Средняя оценка жюри (из 10)',
                    data: juryData.data,
                    backgroundColor: 'rgba(142, 68, 173, 0.8)',
                    borderColor: '#8e44ad',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Горизонтальный график
                responsive: true,
                scales: { 
                    x: { 
                        beginAtZero: true,
                        max: 10,
                        title: { display: true, text: 'Баллы (0-10)' }
                    } 
                },
                plugins: { legend: { display: false } }
            }
        });
    } else {
        document.getElementById('juryChart').parentElement.innerHTML = '<div style="text-align:center;color:#777;padding:40px;">⚖️ Оценок жюри пока нет</div>';
    }
</script>
@endsection