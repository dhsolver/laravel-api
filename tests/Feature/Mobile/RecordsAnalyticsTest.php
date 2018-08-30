<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use App\TourStop;
use App\DeviceType;
use App\Os;
use App\Device;
use App\Activity;

class RecordsAnalyticsTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $device;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');

        factory(Tour::class, 3)->create();
        $this->tour = factory(Tour::class)->create();

        factory(TourStop::class, 10)->create([
            'tour_id' => $this->tour->id,
        ]);
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
            'action' => 'like',
            'device_id' => $deviceId,
        ])
            ->assertStatus(200);

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
            'action' => 'like',
            'device_id' => $deviceId,
        ])
            ->assertStatus(200);

        $this->assertCount(1, Activity::all());
        $this->assertCount(1, $stop->fresh()->activity);
    }
}
