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
     * Тестовые данные для демонстрации. Пароль у всех пользователей: password
     *
     * @return void
     */
    public function run(): void
    {
        $make = function ($name, $email, $role) {
            return User::create([
                'name'      => $name,
                'email'     => $email,
                'password'  => Hash::make('password'),
                'role'      => $role,
                'is_active' => true,
            ]);
        };

        $admin       = $make('Администратор', 'admin@kiff.test', 'admin');
        $organizer   = $make('Организатор', 'organizer@kiff.test', 'organizer');
        $participant = $make('Участник', 'participant@kiff.test', 'participant');
        $jury        = $make('Член жюри', 'jury@kiff.test', 'jury');
        $visitor     = $make('Зритель', 'visitor@kiff.test', 'visitor');

        // Мероприятия
        $main = Event::create([
            'title'        => 'Церемония открытия',
            'description'  => 'Торжественное открытие фестиваля и показ программы.',
            'start_time'   => now()->addDays(10)->setTime(19, 0),
            'end_time'     => now()->addDays(10)->setTime(22, 0),
            'venue'        => 'Главный зал',
            'capacity'     => 300,
            'organizer_id' => $organizer->id,
            'status'       => 'published',
        ]);

        Event::create([
            'title'        => 'Круглый стол с режиссёрами',
            'description'  => 'Обсуждение конкурсной программы.',
            'start_time'   => now()->addDays(12)->setTime(15, 0),
            'end_time'     => now()->addDays(12)->setTime(17, 0),
            'venue'        => 'Малый зал',
            'capacity'     => 80,
            'organizer_id' => $organizer->id,
            'status'       => 'draft',
        ]);

        // Фильмы
        $films = [
            ['Тихая гавань', 'Драма о возвращении домой.', 'Иван Орлов', 'Россия', 2025, 98, 'Драма', 'approved'],
            ['Последний рейс', 'Триллер на борту ночного поезда.', 'Анна Левина', 'Россия', 2026, 110, 'Триллер', 'approved'],
            ['Город огней', 'Документальный фильм о ночной жизни мегаполиса.', 'Пётр Смирнов', 'Казахстан', 2024, 72, 'Документальный', 'pending'],
        ];

        $created = [];
        foreach ($films as $f) {
            $created[] = Film::create([
                'title'          => $f[0],
                'synopsis'       => $f[1],
                'director'       => $f[2],
                'country'        => $f[3],
                'year'           => $f[4],
                'duration'       => $f[5],
                'genre'          => $f[6],
                'participant_id' => $participant->id,
                'status'         => $f[7],
            ]);
        }

        // Заявки
        Application::create([
            'film_id' => $created[0]->id, 'event_id' => $main->id,
            'status' => 'approved', 'reviewed_by' => $organizer->id,
        ]);
        Application::create([
            'film_id' => $created[1]->id, 'event_id' => $main->id,
            'status' => 'approved', 'reviewed_by' => $organizer->id,
        ]);
        Application::create([
            'film_id' => $created[2]->id, 'event_id' => $main->id,
            'status' => 'submitted',
        ]);

        // Оценки жюри
        JuryScore::create([
            'jury_id' => $jury->id, 'film_id' => $created[0]->id,
            'criterion_script' => 8, 'criterion_director' => 9, 'criterion_acting' => 8,
            'criterion_cinematography' => 7, 'criterion_sound' => 8,
        ]);
        JuryScore::create([
            'jury_id' => $jury->id, 'film_id' => $created[1]->id,
            'criterion_script' => 7, 'criterion_director' => 8, 'criterion_acting' => 9,
            'criterion_cinematography' => 9, 'criterion_sound' => 8,
        ]);

        // Билет, голос и отзыв зрителя
        Ticket::create([
            'user_id' => $visitor->id, 'event_id' => $main->id,
            'seat_number' => 'A-12', 'price' => 500, 'status' => 'paid', 'paid_at' => now(),
        ]);
        Vote::create([
            'user_id' => $visitor->id, 'film_id' => $created[0]->id,
            'ip_hash' => hash('sha256', '127.0.0.1'),
        ]);
        Review::create([
            'user_id' => $visitor->id, 'film_id' => $created[0]->id,
            'rating' => 5, 'text' => 'Сильный фильм, очень понравился.', 'status' => 'pending',
        ]);
    }
}
