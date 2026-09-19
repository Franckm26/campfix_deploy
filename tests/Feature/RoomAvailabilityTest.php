<?php

namespace Tests\Feature;

use App\Http\Controllers\EventRequestController;
use App\Models\Facility;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('event_requests');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('room');
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('event_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->string('location');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status');
            $table->timestamps();
        });

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Event Owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_legacy_room_number_request_checks_the_current_location_column(): void
    {
        $this->approvedEvent('Room 301', '09:00', '11:00');

        $response = app(EventRequestController::class)->checkRoomAvailability($this->availabilityRequest([
            'room_number' => 'Room 301',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]));

        $payload = $response->getData(true);
        $this->assertFalse($payload['available']);
        $this->assertCount(1, $payload['conflicting_events']);
        $this->assertSame('Event Owner', $payload['conflicting_events'][0]['user']);
    }

    public function test_adjacent_booking_is_available(): void
    {
        $this->approvedEvent('Room 301', '09:00', '11:00');

        $response = app(EventRequestController::class)->checkRoomAvailability($this->availabilityRequest([
            'location' => 'Room 301',
            'start_time' => '11:00',
            'end_time' => '12:00',
        ]));

        $this->assertTrue($response->getData(true)['available']);
    }

    public function test_room_under_maintenance_is_available_with_a_warning(): void
    {
        Facility::create([
            'name' => 'Room 302',
            'type' => 'room',
            'status' => 'under_maintenance',
        ]);

        $response = app(EventRequestController::class)->checkRoomAvailability($this->availabilityRequest([
            'location' => 'Room 302',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]));

        $payload = $response->getData(true);
        $this->assertTrue($payload['available']);
        $this->assertSame(
            'Warning: this facility is currently under maintenance, but you may still request to use it.',
            $payload['warning']
        );
    }

    public function test_explicitly_unavailable_room_remains_blocked(): void
    {
        Facility::create([
            'name' => 'Room 303',
            'type' => 'room',
            'status' => 'unavailable',
        ]);

        $response = app(EventRequestController::class)->checkRoomAvailability($this->availabilityRequest([
            'location' => 'Room 303',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]));

        $payload = $response->getData(true);
        $this->assertFalse($payload['available']);
        $this->assertSame('This facility is currently unavailable.', $payload['reason']);
    }

    private function availabilityRequest(array $input): Request
    {
        return Request::create('/api/check-room-availability', 'POST', array_merge([
            'event_date' => '2026-10-01',
        ], $input));
    }

    private function approvedEvent(string $location, string $startTime, string $endTime): void
    {
        DB::table('event_requests')->insert([
            'user_id' => 1,
            'description' => 'Reserved event',
            'event_date' => '2026-10-01',
            'location' => $location,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'Approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
