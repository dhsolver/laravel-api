<?php

namespace Tests\Feature\Cms;

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

        $this->fakeActivityForTour($this->tour);
        foreach ($this->stops as $stop) {
            $this->fakeActivityForStop($stop);
        }
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

    /** @test */
    public function a_client_can_get_totals_for_the_stop_overview_report()
    {
        $this->json('GET', route('cms.analytics.overview', ['tour' => $this->tour]))
            ->assertStatus(200)
            ->assertJsonFragment([
                $this->stops->first()->id => [
                    'title' => $this->stops->first()->title,
                    'time' => 5 * 5,
                    'visits' => 5,
                    'actions' => 5,
                ],
                $this->stops->last()->id => [
                    'title' => $this->stops->last()->title,
                    'time' => 5 * 5,
                    'visits' => 5,
                    'actions' => 5,
                ],
            ]);
    }

    /** @test */
    public function a_client_can_specify_a_date_range_for_the_stop_overview_report()
    {
        $start = date('m/d/Y', strtotime('today'));
        $end = date('m/d/Y', strtotime('today'));

        $this->json('GET', route('cms.analytics.overview', ['tour' => $this->tour]) . "?start=$start&end=$end")
            ->assertStatus(200)
            ->assertJsonFragment([
                $this->stops->first()->id => [
                    'title' => $this->stops->first()->title,
                    'time' => 5,
                    'visits' => 1,
                    'actions' => 1,
                ],
                $this->stops->last()->id => [
                    'title' => $this->stops->last()->title,
                    'time' => 5,
                    'visits' => 1,
                    'actions' => 1,
                ],
            ]);
    }

    /** @test */
    public function a_client_can_get_the_totals_of_the_tour_details_report()
    {
        $this->json('GET', route('cms.analytics.details', ['tour' => $this->tour]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'downloads' => 25,
                'time' => 25 * 60,
                'actions' => 25,
            ]);
    }

    /** @test */
    public function a_client_can_specify_a_date_range_for_the_tour_details_report()
    {
        $start = date('m/d/Y', strtotime('today'));
        $end = date('m/d/Y', strtotime('today'));

        $this->json('GET', route('cms.analytics.details', ['tour' => $this->tour]) . "?start=$start&end=$end")
            ->assertStatus(200)
            ->assertJsonFragment([
                'downloads' => 1,
                'time' => 60,
                'actions' => 1,
            ]);
    }
}
