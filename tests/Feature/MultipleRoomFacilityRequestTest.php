<?php

namespace Tests\Feature;

use App\Http\Controllers\EventRequestController;
use App\Models\EventRequest;
use App\Models\EventRequestType;
use App\Models\EventIntendedUser;
use App\Models\Facility;
use App\Models\User;
use App\Models\UserRateLimit;
use App\Services\FacilityRoomSelection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MultipleRoomFacilityRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropAllTables();
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid'); $table->string('name'); $table->string('email');
            $table->string('role'); $table->string('department')->nullable();
            $table->boolean('is_deleted')->default(false); $table->boolean('is_archived')->default(false); $table->timestamps();
        });
        Schema::create('facilities', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('type'); $table->string('status')->default('available'); $table->timestamps();
        });
        Schema::create('event_requests', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->date('event_date');
            foreach (['location', 'start_time', 'end_time', 'category', 'request_type', 'other_category', 'department', 'education_level', 'intended_user', 'priority', 'status', 'image_path', 'notes'] as $column) $table->string($column)->nullable();
            $table->text('description')->nullable();
            foreach (['approval_route', 'approval_history', 'materials_needed'] as $column) $table->json($column)->nullable();
            $table->unsignedInteger('approval_level')->default(1);
            $table->unsignedBigInteger('approved_by')->nullable(); $table->timestamp('approved_at')->nullable();
            $table->boolean('is_deleted')->default(false); $table->timestamps();
        });
        $migration = require database_path('migrations/2026_10_24_000001_add_requested_rooms_to_event_requests.php');
        $migration->up();
        Schema::create('event_request_types', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->json('approval_roles'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('event_intended_users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('code'); $table->json('approval_roles'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('user_rate_limits', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->string('action_type'); $table->date('date'); $table->unsignedInteger('count'); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable(); $table->unsignedBigInteger('concern_id')->nullable();
            foreach (['action', 'description', 'ip_address', 'user_agent', 'old_values', 'new_values', 'metadata'] as $column) $table->text($column)->nullable();
            $table->timestamps();
        });
        Facility::create(['name' => 'Room 301', 'type' => 'room']);
        Facility::create(['name' => 'Room 302', 'type' => 'room']);
        EventRequestType::create(['name' => 'Academic', 'approval_roles' => ['building_admin', 'school_admin']]);
        EventIntendedUser::create(['name' => 'Faculty', 'code' => 'faculty', 'approval_roles' => ['building_admin', 'school_admin']]);
        $this->actingAs(User::forceCreate(['name' => 'Requester', 'email' => 'faculty@example.com', 'role' => 'faculty']));
    }

    private function rooms(): array
    {
        return [['location' => 'Room 301', 'reason' => 'Presentations'], ['location' => 'Room 302', 'reason' => 'Breakout groups']];
    }

    private function submit(array $overrides = [])
    {
        $payload = array_merge([
            'rooms' => $this->rooms(), 'description' => 'Research workshop',
            'event_date' => now()->addDays(2)->toDateString(), 'start_time' => '10:00', 'end_time' => '12:00',
            'category' => 'Area Use', 'request_type' => 'Academic', 'intended_user' => 'faculty',
        ], $overrides);
        if ($payload['rooms'] === null) unset($payload['rooms']);
        $request = Request::create('/events', 'POST', $payload);
        $request->headers->set('Accept', 'application/json');
        return app(EventRequestController::class)->store($request);
    }

    private function availability(string $room, string $start = '10:00', string $end = '12:00'): array
    {
        return app(EventRequestController::class)->checkRoomAvailability(Request::create('/api/check-room-availability', 'GET', [
            'location' => $room, 'event_date' => now()->addDays(2)->toDateString(), 'start_time' => $start, 'end_time' => $end,
        ]))->getData(true);
    }

    public function test_two_rooms_create_one_request_and_one_submission_count_with_both_reasons_and_one_route(): void
    {
        $response = $this->submit();
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(1, EventRequest::count());
        $event = EventRequest::first();
        $this->assertSame($this->rooms(), $event->requested_rooms);
        $this->assertSame('Room 301, Room 302', $event->location);
        $this->assertSame(['building_admin', 'school_admin'], $event->approval_route);
        $this->assertSame('Pending', $event->status);
        $this->assertStringContainsString('Room 301: Presentations', $event->description);
        $this->assertStringContainsString('Room 302: Breakout groups', $event->description);
        $this->assertSame(1, (int) UserRateLimit::first()->count);
    }

    public function test_missing_room_reason_duplicate_room_and_unknown_room_are_rejected_without_creating_requests(): void
    {
        foreach ([
            [['location' => 'Room 301'], ['location' => 'Room 302', 'reason' => '   ']],
            [['location' => 'Room 301', 'reason' => 'First'], ['location' => 'Room 301', 'reason' => 'Second']],
            [['location' => 'Unknown room', 'reason' => 'Workshop']],
        ] as $rooms) {
            $this->assertSame(422, $this->submit(['rooms' => $rooms])->getStatusCode());
        }
        $this->assertSame(0, EventRequest::count());
    }

    public function test_approved_multi_room_request_reserves_every_room_but_not_adjacent_time_slots(): void
    {
        $this->submit();
        EventRequest::first()->update(['status' => 'Approved']);
        $this->assertFalse($this->availability('Room 301')['available']);
        $this->assertFalse($this->availability('Room 302')['available']);
        $this->assertTrue($this->availability('Room 302', '12:00', '13:00')['available']);
    }

    public function test_one_conflicting_room_rejects_the_entire_submission(): void
    {
        EventRequest::create(['user_id' => auth()->id(), 'location' => 'Room 302', 'status' => 'Approved',
            'event_date' => now()->addDays(2)->toDateString(), 'start_time' => '11:00', 'end_time' => '13:00']);
        $response = $this->submit();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Room 302', $response->getContent());
        $this->assertSame(1, EventRequest::count());
    }

    public function test_unavailable_additional_room_is_rejected_and_pending_requests_do_not_reserve_rooms(): void
    {
        $this->submit();
        $this->assertTrue($this->availability('Room 302')['available']);
        Facility::where('name', 'Room 302')->update(['status' => 'unavailable']);
        $this->assertSame(422, $this->submit()->getStatusCode());
        $this->assertSame(1, EventRequest::count());
    }

    public function test_rescheduling_checks_additional_rooms_and_does_not_change_a_request_on_conflict(): void
    {
        $this->submit();
        $event = EventRequest::first();
        EventRequest::create(['user_id' => auth()->id(), 'location' => 'Room 302', 'status' => 'Approved',
            'event_date' => now()->addDays(3)->toDateString(), 'start_time' => '10:00', 'end_time' => '12:00']);
        $before = $event->fresh()->getAttributes();
        try {
            app(EventRequestController::class)->reschedule(Request::create('/events/'.$event->id.'/reschedule', 'POST', [
                'event_date' => now()->addDays(3)->toDateString(), 'start_time' => '10:00', 'end_time' => '12:00',
            ]), $event->id);
            $this->fail('Expected room conflict validation.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertStringContainsString('Room 302', $exception->getMessage());
        }
        $this->assertSame($before, $event->fresh()->getAttributes());
    }

    public function test_legacy_single_room_requests_still_submit_without_rooms_array(): void
    {
        $this->assertSame(200, $this->submit(['rooms' => null, 'location' => 'Room 301', 'area_of_use' => 'Room 301'])->getStatusCode());
        $this->assertSame(['Room 301'], EventRequest::first()->roomLocations());
    }

    public function test_first_room_uses_description_without_a_separate_reason(): void
    {
        $response = $this->submit(['rooms' => [['location' => 'Room 301']]]);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Research workshop', EventRequest::first()->description);
    }

    public function test_only_additional_rooms_require_and_display_a_reason(): void
    {
        $response = $this->submit(['rooms' => [['location' => 'Room 301'], $this->rooms()[1]]]);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $event = EventRequest::first();
        $this->assertSame(['Room 301', 'Room 302'], $event->roomLocations());
        $this->assertStringContainsString('Room 302: Breakout groups', $event->description);
        $this->assertStringNotContainsString('Room 301:', $event->description);
    }

    public function test_long_location_summary_fits_legacy_column_without_losing_selected_rooms(): void
    {
        $rooms = array_fill(0, 10, ['location' => str_repeat('Room ', 30), 'reason' => 'Workshop']);
        $this->assertLessThanOrEqual(255, mb_strlen(app(FacilityRoomSelection::class)->locationSummary($rooms)));
    }

    public function test_unmigrated_database_keeps_single_room_submission_and_availability_working(): void
    {
        Schema::table('event_requests', fn (Blueprint $table) => $table->dropColumn('requested_rooms'));
        $this->assertSame(200, $this->submit(['rooms' => [$this->rooms()[0]]])->getStatusCode());
        EventRequest::first()->update(['status' => 'Approved']);
        $this->assertFalse($this->availability('Room 301')['available']);
        $this->assertSame(422, $this->submit()->getStatusCode());
        $this->assertSame(1, EventRequest::count());
    }
}
