<?php

namespace Tests\Feature\Cms;

use App\DeviceType;
use App\Os;
use Tests\HasTestTour;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;

class ViewAnalyticsTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;
    use HasTestTour;

    public function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        list($this->tour, $this->stops) = $this->createTestTour();

        $this->user = $this->tour->creator;
        $this->loginAs($this->user);

        for ($i = 0; $i < 25; $i++) {
            $date = strtotime("$i days ago 01:00");
            $this->tour->deviceStats()->create([
                'yyyymmdd' => date('Ymd', $date),
                'os' => Os::IOS,
                'device_type' => DeviceType::PHONE,
                'downloads' => 1,
                'actions' => 1,
                'visitors' => 1,
                'final' => true,
            ]);
            $this->tour->deviceStats()->create([
                'yyyymmdd' => date('Ymd', $date),
                'os' => Os::IOS,
                'device_type' => DeviceType::TABLET,
                'downloads' => 1,
                'actions' => 1,
                'visitors' => 1,
                'final' => true,
            ]);
            $this->tour->deviceStats()->create([
                'yyyymmdd' => date('Ymd', $date),
                'os' => Os::ANDROID,
                'device_type' => DeviceType::PHONE,
                'downloads' => 1,
                'actions' => 1,
                'visitors' => 1,
                'final' => true,
            ]);
            $this->tour->deviceStats()->create([
                'yyyymmdd' => date('Ymd', $date),
                'os' => Os::ANDROID,
                'device_type' => DeviceType::TABLET,
                'downloads' => 1,
                'actions' => 1,
                'visitors' => 1,
                'final' => true,
            ]);
            $this->tour->stats()->create([
                'yyyymmdd' => date('Ymd', $date),
                'downloads' => 1,
                'time_spent' => 25,
                'actions' => 1,
                'final' => true,
            ]);
            foreach ($this->tour->stops as $stop) {
                $stop->stats()->create([
                    'yyyymmdd' => date('Ymd', $date),
                    'visits' => 1,
                    'time_spent' => 5,
                    'actions' => 1,
                    'final' => true,
                ]);
            }
        }
    }

    /** @test */
    public function a_client_can_get_totals_for_the_stop_overview_report()
    {
        $this->json('GET', route('cms.analytics.overview', ['tour' => $this->tour]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $this->stops->first()->id,
                'title' => $this->stops->first()->title,
                'time' => 25 * 5,
                'visits' => 25,
                'actions' => 25,
            ])
            ->assertJsonFragment([
                'id' => $this->stops->last()->id,
                'title' => $this->stops->last()->title,
                'time' => 25 * 5,
                'visits' => 25,
                'actions' => 25,
            ]);
    }

    /** @test */
    public function a_client_can_specify_a_date_range_for_the_stop_overview_report()
    {
        $start = date('m/d/Y', strtotime('yesterday'));
        $end = date('m/d/Y', strtotime('today'));

        $this->json('GET', route('cms.analytics.overview', ['tour' => $this->tour]) . "?start=$start&end=$end")
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $this->stops->first()->id,
                'title' => $this->stops->first()->title,
                'time' => 10,
                'visits' => 2,
                'actions' => 2,
            ])
            ->assertJsonFragment([
                'id' => $this->stops->last()->id,
                'title' => $this->stops->last()->title,
                'time' => 10,
                'visits' => 2,
                'actions' => 2,
                ]);
    }

    /** @test */
    public function a_client_can_get_the_totals_of_the_tour_details_report()
    {
        $this->json('GET', route('cms.analytics.details', ['tour' => $this->tour]))
            ->assertStatus(200)
            ->assertJsonCount(25, 'data')
            ->assertJsonFragment([
                'yyyymmdd' => date('Ymd', strtotime('yesterday')),
                'downloads' => 1,
                'time' => 25,
                'actions' => 1,
            ])
            ->assertJsonFragment([
                'yyyymmdd' => date('Ymd', strtotime('today')),
                'downloads' => 1,
                'time' => 25,
                'actions' => 1,
            ]);
    }

    /** @test */
    public function a_client_can_specify_a_date_range_for_the_tour_details_report()
    {
        $start = date('m/d/Y', strtotime('today'));
        $end = date('m/d/Y', strtotime('today'));

        $this->json('GET', route('cms.analytics.details', ['tour' => $this->tour]) . "?start=$start&end=$end")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'yyyymmdd' => date('Ymd', strtotime('today')),
                'downloads' => 1,
                'time' => 25,
                'actions' => 1,
            ]);
    }

    /** @test */
    public function a_client_can_get_the_totals_of_the_device_details_report()
    {
        $this->json('GET', route('cms.analytics.devices', ['tour' => $this->tour]))
            ->assertStatus(200)
            ->assertJsonCount(4)
            ->assertJsonFragment([
                'os' => Os::IOS,
                'device_type' => DeviceType::PHONE,
                'downloads' => 25,
                'actions' => 25,
                'visitors' => 25,
            ])
            ->assertJsonFragment([
                'os' => Os::IOS,
                'device_type' => DeviceType::TABLET,
                'downloads' => 25,
                'actions' => 25,
                'visitors' => 25,
            ])
            ->assertJsonFragment([
                'os' => Os::ANDROID,
                'device_type' => DeviceType::PHONE,
                'downloads' => 25,
                'actions' => 25,
                'visitors' => 25,
            ])
            ->assertJsonFragment([
                'os' => Os::ANDROID,
                'device_type' => DeviceType::TABLET,
                'downloads' => 25,
                'actions' => 25,
                'visitors' => 25,
            ]);
    }

    /** @test */
    public function a_client_can_specify_a_date_range_for_the_device_details_report()
    {
        $start = date('m/d/Y', strtotime('yesterday'));
        $end = date('m/d/Y', strtotime('today'));

        $this->json('GET', route('cms.analytics.devices', ['tour' => $this->tour]) . "?start=$start&end=$end")
            ->assertStatus(200)
            ->assertJsonCount(4)
            ->assertJsonFragment([
                'os' => Os::IOS,
                'device_type' => DeviceType::PHONE,
                'downloads' => 2,
                'actions' => 2,
                'visitors' => 2,
            ])
            ->assertJsonFragment([
                'os' => Os::IOS,
                'device_type' => DeviceType::TABLET,
                'downloads' => 2,
                'actions' => 2,
                'visitors' => 2,
            ])
            ->assertJsonFragment([
                'os' => Os::ANDROID,
                'device_type' => DeviceType::PHONE,
                'downloads' => 2,
                'actions' => 2,
                'visitors' => 2,
            ])
            ->assertJsonFragment([
                'os' => Os::ANDROID,
                'device_type' => DeviceType::TABLET,
                'downloads' => 2,
                'actions' => 2,
                'visitors' => 2,
            ]);
    }

    /** @test */
    public function only_the_tour_creator_can_see_analytics_reports()
    {
        $this->withExceptionHandling();

        $this->json('GET', route('cms.analytics.overview', ['tour' => $this->tour]))
            ->assertStatus(200);
        $this->json('GET', route('cms.analytics.details', ['tour' => $this->tour]))
            ->assertStatus(200);
        $this->json('GET', route('cms.analytics.devices', ['tour' => $this->tour]))
            ->assertStatus(200);

        $this->signIn('client');

        $this->json('GET', route('cms.analytics.overview', ['tour' => $this->tour]))
            ->assertStatus(403);
        $this->json('GET', route('cms.analytics.details', ['tour' => $this->tour]))
            ->assertStatus(403);
        $this->json('GET', route('cms.analytics.devices', ['tour' => $this->tour]))
            ->assertStatus(403);
    }
}
