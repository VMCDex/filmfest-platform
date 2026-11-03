<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Event;
use App\Models\Film;
use App\Models\JuryScore;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Демо-данные. Пароль у всех пользователей: password
     *
     * Основные аккаунты: admin@, organizer@, participant@, jury@, visitor@ (домен kiff.test).
     * Остальные нужны, чтобы отчёты, голосование и оценки выглядели живыми.
     */
    public function run(): void
    {
        $make = function (string $name, string $email, string $role): User {
            return User::create([
                'name'      => $name,
                'email'     => $email,
                'password'  => Hash::make('password'),
                'role'      => $role,
                'is_active' => true,
            ]);
        };

        $make('Администратор', 'admin@kiff.test', 'admin');
        $organizer    = $make('Организатор', 'organizer@kiff.test', 'organizer');
        $participant  = $make('Участник', 'participant@kiff.test', 'participant');
        $participant2 = $make('Участник 2', 'participant2@kiff.test', 'participant');
        $jury         = $make('Член жюри', 'jury@kiff.test', 'jury');
        $jury2        = $make('Член жюри 2', 'jury2@kiff.test', 'jury');
        $jury3        = $make('Член жюри 3', 'jury3@kiff.test', 'jury');

        $visitors = [$make('Зритель', 'visitor@kiff.test', 'visitor')];
        for ($i = 2; $i <= 7; $i++) {
            $visitors[] = $make("Зритель $i", "visitor$i@kiff.test", 'visitor');
        }

        // Мероприятия
        $opening = Event::create([
            'title'        => 'Церемония открытия',
            'description'  => 'Торжественное открытие фестиваля и показ программы.',
            'start_time'   => now()->addDays(10)->setTime(19, 0),
            'end_time'     => now()->addDays(10)->setTime(22, 0),
            'venue'        => 'Главный зал',
            'capacity'     => 300,
            'organizer_id' => $organizer->id,
            'status'       => 'published',
        ]);

        $screening = Event::create([
            'title'        => 'Показ конкурсной программы',
            'description'  => 'Конкурсные фильмы фестиваля.',
            'start_time'   => now()->addDays(14)->setTime(18, 0),
            'end_time'     => now()->addDays(14)->setTime(22, 0),
            'venue'        => 'Малый зал',
            'capacity'     => 120,
            'organizer_id' => $organizer->id,
            'status'       => 'published',
        ]);

        Event::create([
            'title'        => 'Круглый стол с режиссёрами',
            'description'  => 'Обсуждение конкурсной программы.',
            'start_time'   => now()->addDays(12)->setTime(15, 0),
            'end_time'     => now()->addDays(12)->setTime(17, 0),
            'venue'        => 'Лекторий',
            'capacity'     => 80,
            'organizer_id' => $organizer->id,
            'status'       => 'draft',
        ]);

        // Фильмы и заявки: название, синопсис, режиссёр, страна, год, минуты, жанр,
        // участник, мероприятие, статус заявки, комментарий организатора
        $catalog = [
            ['Тихая гавань', 'Драма о возвращении домой.', 'Иван Орлов', 'Россия', 2025, 98, 'Драма', $participant, $opening, 'approved', 'Фильм включён в программу.'],
            ['Последний рейс', 'Триллер на борту ночного поезда.', 'Анна Левина', 'Россия', 2026, 110, 'Триллер', $participant, $opening, 'approved', 'Фильм включён в программу.'],
            ['Дом у реки', 'Семейная история на фоне весеннего паводка.', 'Марина Ким', 'Россия', 2025, 87, 'Мелодрама', $participant2, $screening, 'approved', 'Фильм включён в программу.'],
            ['Свет в окне', 'Документальный фильм о сельском враче.', 'Олег Романов', 'Беларусь', 2024, 64, 'Документальный', $participant2, $screening, 'approved', 'Фильм включён в программу.'],
            ['Город огней', 'Документальный фильм о ночной жизни мегаполиса.', 'Пётр Смирнов', 'Казахстан', 2024, 72, 'Документальный', $participant, $opening, 'submitted', null],
            ['Шум прибоя', 'Драма о рыбацком посёлке.', 'Ева Коваль', 'Армения', 2023, 95, 'Драма', $participant2, $screening, 'under_review', null],
            ['Тёмная вода', 'Короткометражный хоррор.', 'Глеб Арсенов', 'Россия', 2026, 18, 'Хоррор', $participant, $opening, 'rejected', 'Не соответствует формату программы.'],
        ];

        $films = [];
        foreach ($catalog as [$title, $synopsis, $director, $country, $year, $duration, $genre, $owner, $event, $appStatus, $comment]) {
            $film = Film::create([
                'title'          => $title,
                'synopsis'       => $synopsis,
                'director'       => $director,
                'country'        => $country,
                'year'           => $year,
                'duration'       => $duration,
                'genre'          => $genre,
                'participant_id' => $owner->id,
                'status'         => $appStatus === 'approved' ? 'approved' : ($appStatus === 'rejected' ? 'rejected' : 'pending'),
            ]);

            Application::create([
                'film_id'     => $film->id,
                'event_id'    => $event->id,
                'status'      => $appStatus,
                'comment'     => $comment,
                'reviewed_by' => in_array($appStatus, ['approved', 'rejected'], true) ? $organizer->id : null,
            ]);

            $films[] = $film;
        }

        // Оценки жюри: сценарий, режиссура, актёры, операторская работа, звук.
        // Демо-жюри оценило три фильма, четвёртый остаётся для оценки.
        $scores = [
            [$jury,  0, [8, 9, 8, 7, 8]],
            [$jury,  1, [7, 8, 9, 9, 8]],
            [$jury,  2, [9, 8, 8, 8, 7]],
            [$jury2, 0, [9, 9, 8, 8, 9]],
            [$jury2, 1, [8, 7, 8, 9, 8]],
            [$jury2, 2, [7, 8, 9, 8, 8]],
            [$jury2, 3, [8, 8, 7, 7, 9]],
            [$jury3, 0, [8, 8, 9, 7, 8]],
            [$jury3, 1, [9, 8, 8, 9, 9]],
            [$jury3, 3, [7, 9, 8, 8, 8]],
        ];
        foreach ($scores as [$member, $filmIndex, $c]) {
            JuryScore::create([
                'jury_id'                  => $member->id,
                'film_id'                  => $films[$filmIndex]->id,
                'criterion_script'         => $c[0],
                'criterion_director'       => $c[1],
                'criterion_acting'         => $c[2],
                'criterion_cinematography' => $c[3],
                'criterion_sound'          => $c[4],
            ]);
        }

        // Билеты за последний месяц: чем ближе к открытию, тем больше продаж
        $daysAgo = [26, 24, 22, 21, 19, 17, 15, 14, 12, 10, 9, 7, 6, 5, 4, 3, 2, 2, 1, 1, 0, 0];
        foreach ($daysAgo as $i => $days) {
            $event = $i % 3 === 0 ? $screening : $opening;
            $moment = now()->subDays($days)->setTime(10 + $i % 9, ($i * 7) % 60);

            $ticket = new Ticket([
                'user_id'     => $visitors[$i % count($visitors)]->id,
                'event_id'    => $event->id,
                'seat_number' => chr(65 + intdiv($i, 7)) . '-' . ($i % 7 + 1),
                'price'       => $event->id === $opening->id ? 500 : 400,
                'status'      => 'paid',
                'paid_at'     => $moment,
            ]);
            $ticket->created_at = $moment;
            $ticket->updated_at = $moment;
            $ticket->save();
        }

        // Голоса зрителей (по одному голосу с адреса на фильм): фильм => число голосов
        foreach ([0 => 9, 1 => 6, 2 => 7, 3 => 3] as $filmIndex => $count) {
            for ($n = 1; $n <= $count; $n++) {
                Vote::create([
                    'user_id' => $visitors[$n % count($visitors)]->id,
                    'film_id' => $films[$filmIndex]->id,
                    'ip_hash' => hash('sha256', "demo-ip-{$filmIndex}-{$n}"),
                ]);
            }
        }

        // Отзывы зрителей
        $reviews = [
            [0, 0, 5, 'Сильный фильм, очень понравился.', 'approved'],
            [1, 0, 4, 'Хорошая актёрская игра.', 'approved'],
            [2, 0, 5, 'Лучший фильм программы.', 'approved'],
            [3, 1, 4, 'Напряжённо до самого финала.', 'approved'],
            [4, 1, 3, 'Хорошее начало, слабый финал.', 'pending'],
            [5, 1, 5, 'Отличный триллер.', 'approved'],
            [1, 2, 4, 'Тёплая и добрая история.', 'approved'],
            [6, 2, 3, 'Местами затянуто.', 'pending'],
            [2, 3, 5, 'Важная тема, снято честно.', 'approved'],
            [4, 3, 4, 'Достойный документальный фильм.', 'pending'],
        ];
        foreach ($reviews as [$visitorIndex, $filmIndex, $rating, $text, $status]) {
            Review::create([
                'user_id' => $visitors[$visitorIndex]->id,
                'film_id' => $films[$filmIndex]->id,
                'rating'  => $rating,
                'text'    => $text,
                'status'  => $status,
            ]);
        }
    }
}
