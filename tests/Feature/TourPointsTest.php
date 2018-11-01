<?php

namespace Tests\Feature\Mobile;

use App\TourType;
use App\UserScore;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use Tests\TestCase;
use App\Tour;
use App\TourStop;
use App\Device;

class TourPointsTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $user;
    protected $device;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;
        $this->device = $this->user->devices()->create(factory(Device::class)->make()->toArray());

        $this->tour = factory(Tour::class)->states('published')->create([
            'pricing_type' => 'free',
            'type' => TourType::OUTDOOR
        ]);

        factory(TourStop::class, 10)->create([
            'tour_id' => $this->tour->id,
        ]);
    }

    public function sendAnalytics($model, $action = 'start', $time = null)
    {
        if ($model instanceof Tour) {
            return $this->postJson("/mobile/tours/{$model->id}/track", [
                'activity' => [
                    [
                        'action' => $action,
                        'device_id' => $this->device->id,
                        'timestamp' => $time ?: strtotime('now'),
                    ],
                ],
            ])->assertStatus(200);
        } elseif ($model instanceof TourStop) {
            return $this->postJson("/mobile/stops/{$model->id}/track", [
                'activity' => [
                    [
                        'action' => $action,
                        'device_id' => $this->device->id,
                        'timestamp' => $time ?: strtotime('now'),
                    ],
                ],
            ])->assertStatus(200);
        }
    }

    /** @test */
    public function when_a_regular_tour_is_started_it_should_set_the_total_number_of_stops()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start');

        $this->assertCount(1, $this->signInUser->user->scores()->get());

        $score = UserScore::current($this->tour, $this->user);

        $this->assertEquals($this->tour->stops()->count(), $score->total_stops);
    }
}
