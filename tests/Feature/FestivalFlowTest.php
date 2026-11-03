<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Event;
use App\Models\Film;
use App\Models\JuryScore;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FestivalFlowTest extends TestCase
{
    use RefreshDatabase;

    private function event(User $organizer): Event
    {
        return Event::create([
            'title' => 'Открытие',
            'start_time' => now()->addDays(5),
            'end_time' => now()->addDays(5)->addHours(3),
            'venue' => 'Главный зал',
            'capacity' => 100,
            'organizer_id' => $organizer->id,
            'status' => 'published',
        ]);
    }

    private function film(User $participant, string $status = 'pending'): Film
    {
        return Film::create([
            'title' => 'Тестовый фильм',
            'synopsis' => 'Описание',
            'director' => 'Режиссёр',
            'country' => 'Россия',
            'year' => 2026,
            'duration' => 90,
            'genre' => 'Драма',
            'participant_id' => $participant->id,
            'status' => $status,
        ]);
    }

    public function test_approving_application_approves_film(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $participant = User::factory()->create(['role' => 'participant']);
        $film = $this->film($participant);
        $application = Application::create([
            'film_id' => $film->id,
            'event_id' => $this->event($organizer)->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($organizer)
            ->put("/organizer/applications/{$application->id}/review", ['status' => 'approved'])
            ->assertRedirect();

        $this->assertSame('approved', $application->fresh()->status);
        $this->assertSame('approved', $film->fresh()->status);
        $this->assertSame($organizer->id, $application->fresh()->reviewed_by);
    }

    public function test_jury_score_total_is_average_and_duplicate_is_rejected(): void
    {
        $jury = User::factory()->create(['role' => 'jury']);
        $film = $this->film(User::factory()->create(['role' => 'participant']), 'approved');
        $scores = [
            'film_id' => $film->id,
            'criterion_script' => 9,
            'criterion_director' => 8,
            'criterion_acting' => 7,
            'criterion_cinematography' => 10,
            'criterion_sound' => 6,
        ];

        $this->actingAs($jury)->post('/jury/films/score', $scores);
        $this->actingAs($jury)->post('/jury/films/score', $scores)->assertSessionHas('error');

        $this->assertSame(1, JuryScore::count());
        $this->assertEquals(8.0, JuryScore::first()->total_score);
    }

    public function test_jury_score_is_validated(): void
    {
        $jury = User::factory()->create(['role' => 'jury']);
        $film = $this->film(User::factory()->create(['role' => 'participant']), 'approved');

        $this->actingAs($jury)
            ->post('/jury/films/score', ['film_id' => $film->id, 'criterion_script' => 11])
            ->assertSessionHasErrors('criterion_script');
    }

    public function test_visitor_cannot_buy_same_seat_twice(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);
        $event = $this->event(User::factory()->create(['role' => 'organizer']));

        $this->actingAs($visitor)->post("/visitor/tickets/{$event->id}", ['seat_number' => 'A-1', 'price' => 500]);
        $this->actingAs($visitor)->post("/visitor/tickets/{$event->id}", ['seat_number' => 'A-1', 'price' => 500])
            ->assertSessionHas('error');

        $this->assertSame(1, Ticket::count());
    }

    public function test_visitor_votes_for_film_only_once(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);
        $film = $this->film(User::factory()->create(['role' => 'participant']), 'approved');

        $this->actingAs($visitor)->post("/visitor/votes/{$film->id}");
        $this->actingAs($visitor)->post("/visitor/votes/{$film->id}")->assertSessionHas('error');

        $this->assertSame(1, Vote::count());
    }

    public function test_organizer_reports_and_exports_open(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);

        $this->actingAs($organizer)->get('/organizer/reports')->assertOk();
        $this->actingAs($organizer)->get('/organizer/reports/export/xlsx')->assertOk();
        $this->actingAs($organizer)->get('/organizer/reports/export/pdf')->assertOk();
    }

    public function test_seeder_creates_demo_data_for_all_roles(): void
    {
        $this->seed();

        foreach (['admin', 'organizer', 'participant', 'jury', 'visitor'] as $role) {
            $this->assertDatabaseHas('users', ['role' => $role]);
        }
        $this->assertGreaterThan(0, Film::count());
    }
}
