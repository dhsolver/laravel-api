<?php

namespace Tests\Unit;

use App\DeviceStat;
use App\DeviceType;
use App\Os;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\HasTestTour;
use App\Analytics\AnalyticsSummarizer;
use App\TourStat;
use App\StopStat;
use App\Device;
use App\Action;

class AnalyticsSummaryTest extends TestCase
{
    use DatabaseMigrations;
    use HasTestTour;

    public function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        list($this->tour, $this->stops) = $this->createTestTour();
        $this->user = $this->tour->creator;

        $this->fakeActivityForTour($this->tour);
        foreach ($this->stops as $stop) {
            $this->fakeActivityForStop($stop);
        }
    }

    /** @test */
    public function it_can_create_a_summary_for_a_given_date()
    {
        $this->assertCount(0, StopStat::all());
        $this->assertCount(0, TourStat::all());

        $summarizer = new AnalyticsSummarizer();
        $summarizer->summarizeTour($this->tour, strtotime('today'));

        $this->assertCount(1, TourStat::all());

        $stat = TourStat::first();
        $this->assertEquals(1, $stat->downloads);
        $this->assertEquals(60, $stat->time_spent);
        $this->assertEquals(1, $stat->actions);
        $this->assertEquals(date('Ymd', strtotime('today')), $stat->yyyymmdd);
        $this->assertFalse($stat->final);

        $this->assertCount(5, StopStat::all());

        foreach (StopStat::all() as $stat) {
            $this->assertEquals(1, $stat->visits);
            $this->assertEquals(5, $stat->time_spent);
            $this->assertEquals(1, $stat->actions);
            $this->assertEquals(date('Ymd', strtotime('today')), $stat->yyyymmdd);
            $this->assertFalse($stat->final);
        }
    }

    /** @test */
    public function it_can_finalize_previous_days()
    {
        $summarizer = new AnalyticsSummarizer();
        $summarizer->summarizeTour($this->tour, strtotime('yesterday'));

        $stat = TourStat::first();
        $this->assertFalse($stat->final);
        $this->assertEquals(1, $stat->downloads);

        $stopStat = StopStat::first();
        $this->assertFalse($stopStat->final);
        $this->assertEquals(1, $stopStat->visits);

        $stop = $this->tour->stops()->first();

        $this->tour->activity()->create([
            'user_id' => $this->user->id,
            'action' => Action::DOWNLOAD,
            'created_at' => strtotime('yesterday'),
            'device_id' => Device::first()->id,
        ]);

        $stop->activity()->create([
            'user_id' => $this->user->id,
            'action' => Action::VISIT,
            'created_at' => strtotime('yesterday'),
            'device_id' => Device::first()->id,
        ]);

        $summarizer->finalizePreviousDays();

        $this->assertTrue($stat->fresh()->final);
        $this->assertEquals(2, $stat->fresh()->downloads);

        $this->assertTrue($stopStat->fresh()->final);
        $this->assertEquals(2, $stopStat->fresh()->visits);
    }

    /** @test */
    public function it_can_create_a_summary_for_stats_grouped_by_device_types()
    {
        $this->assertCount(0, DeviceStat::all());

        $summarizer = new AnalyticsSummarizer();
        $summarizer->summarizeTour($this->tour, strtotime('today'));

        $this->assertCount(4, DeviceStat::all());

        $stat = DeviceStat::where('tour_id', $this->tour->id)
            ->where('os', Os::IOS)
            ->where('device_type', DeviceType::PHONE)
            ->first();

        $this->assertEquals(1, $stat->downloads);
        $this->assertEquals(1, $stat->visitors);
        $this->assertEquals(1, $stat->actions);

        $stat = DeviceStat::where('tour_id', $this->tour->id)
            ->where('os', Os::ANDROID)
            ->where('device_type', DeviceType::PHONE)
            ->first();

        $this->assertEquals(0, $stat->downloads);
        $this->assertEquals(0, $stat->visitors);
        $this->assertEquals(0, $stat->actions);
    }
}
