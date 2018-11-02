<?php

namespace Tests\Feature;

use App\TourType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use App\TourStop;
use App\DeviceType;
use App\Os;
use App\Device;
use App\Activity;
use Carbon\Carbon;

class RecordsAnalyticsTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $device;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');

        $this->tour = factory(Tour::class)->states('published')->create([
            'pricing_type' => 'free',
            'type' => TourType::OUTDOOR
        ]);

        factory(TourStop::class, 5)->create([
            'tour_id' => $this->tour->id,
        ]);

        $this->stops = $this->tour->stops()->ordered()->get();
//        factory(Tour::class, 3)->create();
//        $this->tour = factory(Tour::class)->create();
//
//        factory(TourStop::class, 10)->create([
//            'tour_id' => $this->tour->id,
//        ]);
//        $this->tour->update([
//            'start_point_id' => $this->tour->stops[0],
//        ]);
    }

    public function createDevice()
    {
        $this->device = \App\Device::create([
            'device_udid' => '12345',
            'os' => Os::ANDROID,
            'type' => DeviceType::PHONE,
        ]);

        $this->signInUser->user->devices()->attach($this->device);

        return $this->device->id;
    }

    /** @test */
    public function a_mobile_user_can_have_multiple_devices()
    {
        $this->assertCount(0, \App\Device::all());

        $this->postJson('/mobile/device', [
            'device_udid' => '12345',
            'os' => Os::ANDROID,
            'type' => DeviceType::PHONE,
        ])
            ->assertJsonStructure(['device_id'])
            ->assertStatus(200);

        $this->assertCount(1, \App\Device::all());

        $this->postJson('/mobile/device', [
            'device_udid' => '67890',
            'os' => Os::IOS,
            'type' => DeviceType::TABLET,
        ])
            ->assertStatus(200);

        $this->assertCount(2, $this->signInUser->devices);
    }

    /** @test */
    public function a_mobile_device_can_belong_to_multiple_users()
    {
        $otherUser = create(\App\User::class);

        $device = \App\Device::create([
            'device_udid' => '12345',
            'os' => Os::ANDROID,
            'type' => DeviceType::PHONE,
        ]);

        $otherUser->devices()->attach($device);
        $this->assertCount(1, $otherUser->fresh()->devices);

        $this->postJson('/mobile/device', $device->toArray())
            ->assertStatus(200);

        $this->assertCount(1, $this->signInUser->user->fresh()->devices);

        $this->assertCount(1, Device::all());
    }

    /** @test */
    public function a_mobile_device_can_record_tour_activity()
    {
        $this->withoutExceptionHandling();

        $deviceId = $this->createDevice();

        $this->postJson("/mobile/tours/{$this->tour->id}/track", [
            'activity' => [
                [
                    'action' => 'like',
                    'device_id' => $deviceId,
                    'timestamp' => strtotime('now'),
                ],
            ],
        ])->assertStatus(200);

        $this->assertCount(1, Activity::all());
        $this->assertCount(1, $this->tour->fresh()->activity);
    }

    /** @test */
    public function a_mobile_device_can_record_stop_activity()
    {
        $this->withoutExceptionHandling();

        $deviceId = $this->createDevice();

        $stop = $this->tour->stops()->first();

        $this->postJson("/mobile/stops/{$stop->id}/track", [
            'activity' => [
                [
                    'action' => 'like',
                    'device_id' => $deviceId,
                    'timestamp' => strtotime('now'),
                ],
            ],
        ])->assertStatus(200);

        $this->assertCount(1, Activity::all());
        $this->assertCount(1, $stop->fresh()->activity);
    }

    /** @test */
    public function tracking_updates_must_have_a_timestamp()
    {
        $this->withoutExceptionHandling();

        $deviceId = $this->createDevice();

        $time = strtotime('yesterday');

        $this->postJson("/mobile/tours/{$this->tour->id}/track", [
            'activity' => [
                [
                    'action' => 'like',
                    'device_id' => $deviceId,
                    'timestamp' => $time
                ],
            ],
        ])->assertStatus(200);

        $item = Activity::first();
        $this->assertEquals(Carbon::createFromTimestampUTC($time)->toDateTimeString(), $item->created_at);
    }

    /** @test */
    public function tracking_endpoints_can_submit_multiple_entries_at_once()
    {
        $this->withoutExceptionHandling();

        $deviceId = $this->createDevice();

        $stop = $this->tour->stops()->first();
        $this->assertCount(0, $stop->activity);

        $this->postJson("/mobile/stops/{$stop->id}/track", [
            'activity' => [
                [
                    'action' => 'start',
                    'device_id' => $deviceId,
                    'timestamp' => strtotime('yesterday'),
                ],
                [
                    'action' => 'stop',
                    'device_id' => $deviceId,
                    'timestamp' => strtotime('now'),
                ],
            ],
        ])->assertStatus(200);

        $this->assertCount(2, Activity::all());
        $this->assertCount(2, $stop->fresh()->activity);
    }
}
