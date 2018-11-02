<?php

namespace Tests\Feature\Mobile;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Exceptions\UntraceableTourException;
use App\Points\AdventureCalculator;
use Tests\Concerns\AttachJwtToken;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\StopChoice;
use App\ScoreCard;
use App\TourStop;
use App\TourType;
use App\Device;
use App\Media;
use App\Tour;

class AdventurePointsTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $user;
    protected $device;
    protected $stop1;
    protected $stop2;
    protected $stop3;
    protected $stop4;
    protected $stop5;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser->user;
        $this->device = $this->user->devices()->create(factory(Device::class)->make()->toArray());

        $audio = Media::create([
            'file' => str_random(10),
            'length' => 155,
            'user_id' => $this->signInUser->id,
        ]);

        $this->tour = factory(Tour::class)->states('published')->create([
            'pricing_type' => 'free',
            'background_audio_id' => $audio->id,
            'type' => TourType::ADVENTURE
        ]);

        $this->stop1 = factory(TourStop::class)->create(['tour_id' => $this->tour, 'intro_audio_id' => $audio->id]);
        $this->stop1->location->update([
            'address1' => '77 River St',    // Hoboken Cigars
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.73611847,
            'longitude' => -74.0290305,
        ]);

        $this->stop2 = factory(TourStop::class)->create(['tour_id' => $this->tour, 'intro_audio_id' => $audio->id]);
        $this->stop2->location->update([
            'id' => 2610,
            'address1' => '500 Grand St',       // Grand Vin
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74331877,
            'longitude' => -74.03518617,
        ]);

        $this->stop3 = factory(TourStop::class)->create(['tour_id' => $this->tour, 'intro_audio_id' => $audio->id]);
        $this->stop3->location->update([
            'id' => 2611,
            'address1' => '163 14th St',        // Dino's
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.75336903,
            'longitude' => -74.02768135,
        ]);

        $this->stop4 = factory(TourStop::class)->create(['tour_id' => $this->tour, 'intro_audio_id' => $audio->id]);
        $this->stop4->location->update([
            'id' => 2612,
            'address1' => '11th St',        // Baseball Monument
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74993106,
            'longitude' => -74.02735949,
        ]);

        $this->stop5 = factory(TourStop::class)->create(['tour_id' => $this->tour, 'intro_audio_id' => $audio->id]);
        $this->stop5->location->update([
            'address1' => '622 Washington St',      // Benny Tunido's
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74423323,
            'longitude' => -74.02915657,
        ]);

        $this->stop1->update(['next_stop_id' => $this->stop2->id]);

        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop2->id, 'next_stop_id' => $this->stop3->id]);
        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop2->id, 'next_stop_id' => $this->stop4->id]);
        $this->stop2->update(['is_multiple_choice' => true]);

        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop3->id, 'next_stop_id' => $this->stop4->id]);
        factory(StopChoice::class)->create(['tour_stop_id' => $this->stop3->id, 'next_stop_id' => $this->stop5->id]);
        $this->stop3->update(['is_multiple_choice' => true]);

        $this->stop4->update(['next_stop_id' => $this->stop5->id]);

        $this->tour->update([
            'start_point_id' => $this->stop1->id,
            'end_point_id' => $this->stop5->id,
        ]);
    }

    public function insertStopRouteData()
    {
        $query = <<<qur
INSERT INTO `stop_routes` (`id`, `tour_id`, `stop_id`, `next_stop_id`, `order`, `latitude`, `longitude`, `created_at`, `updated_at`)
VALUES
(187, 1000533, 100022075, 100022076, 1, 40.74331877, -74.03518617, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(188, 1000533, 100022075, 100022076, 2, 40.74311233, -74.03526875, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(189, 1000533, 100022075, 100022076, 3, 40.74319768, -74.03496164, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(190, 1000533, 100022075, 100022076, 4, 40.74441805, -74.03458436, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(191, 1000533, 100022075, 100022076, 5, 40.74569275, -74.03420176, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(192, 1000533, 100022075, 100022076, 6, 40.74695205, -74.03381672, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(193, 1000533, 100022075, 100022076, 7, 40.74823832, -74.03344121, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(194, 1000533, 100022075, 100022076, 8, 40.74807373, -74.03250244, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(195, 1000533, 100022075, 100022076, 9, 40.74935136, -74.03211684, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(196, 1000533, 100022075, 100022076, 10, 40.75060924, -74.03173132, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(197, 1000533, 100022075, 100022076, 11, 40.75188466, -74.03134053, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(198, 1000533, 100022075, 100022076, 12, 40.75408110, -74.03066730, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(199, 1000533, 100022075, 100022076, 13, 40.75388198, -74.02969365, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(200, 1000533, 100022075, 100022076, 14, 40.75379868, -74.02946567, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(201, 1000533, 100022075, 100022076, 15, 40.75367067, -74.02881925, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(202, 1000533, 100022075, 100022076, 16, 40.75351380, -74.02790620, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(203, 1000533, 100022075, 100022076, 17, 40.75337665, -74.02794107, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(204, 1000533, 100022075, 100022076, 18, 40.75336903, -74.02768135, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(205, 1000533, 100022075, 100022077, 1, 40.74331877, -74.03518617, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(206, 1000533, 100022075, 100022077, 2, 40.74321513, -74.03523445, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(207, 1000533, 100022075, 100022077, 3, 40.74316382, -74.03496690, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(208, 1000533, 100022075, 100022077, 4, 40.74441104, -74.03460145, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(209, 1000533, 100022075, 100022077, 5, 40.74344172, -74.02912706, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(210, 1000533, 100022075, 100022077, 6, 40.74989347, -74.02715296, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(211, 1000533, 100022075, 100022077, 7, 40.74993106, -74.02735949, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(212, 1000533, 100022077, 100022078, 1, 40.74993106, -74.02735949, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(213, 1000533, 100022077, 100022078, 2, 40.74989144, -74.02713284, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(214, 1000533, 100022077, 100022078, 3, 40.74982235, -74.02715698, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(215, 1000533, 100022077, 100022078, 4, 40.74857879, -74.02752713, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(216, 1000533, 100022077, 100022078, 5, 40.74732081, -74.02792543, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(217, 1000533, 100022077, 100022078, 6, 40.74603453, -74.02830198, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(218, 1000533, 100022077, 100022078, 7, 40.74475514, -74.02869500, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(219, 1000533, 100022077, 100022078, 8, 40.74418731, -74.02886801, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(220, 1000533, 100022077, 100022078, 9, 40.74423323, -74.02915657, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(221, 1000533, 100022076, 100022077, 1, 40.75336903, -74.02768135, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(222, 1000533, 100022076, 100022077, 2, 40.75340038, -74.02788636, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(223, 1000533, 100022076, 100022077, 3, 40.75351670, -74.02784211, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(224, 1000533, 100022076, 100022077, 4, 40.75336381, -74.02693619, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(225, 1000533, 100022076, 100022077, 5, 40.75323427, -74.02613421, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(226, 1000533, 100022076, 100022077, 6, 40.75301382, -74.02620429, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(227, 1000533, 100022076, 100022077, 7, 40.75239308, -74.02637226, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(228, 1000533, 100022076, 100022077, 8, 40.75113026, -74.02677057, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(229, 1000533, 100022076, 100022077, 9, 40.75050493, -74.02695430, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(230, 1000533, 100022076, 100022077, 10, 40.74989078, -74.02715546, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(231, 1000533, 100022076, 100022077, 11, 40.74993106, -74.02735949, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(232, 1000533, 100022076, 100022078, 1, 40.75336903, -74.02768135, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(233, 1000533, 100022076, 100022078, 2, 40.75336903, -74.02768135, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(234, 1000533, 100022076, 100022078, 3, 40.75339037, -74.02793750, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(235, 1000533, 100022076, 100022078, 4, 40.75352142, -74.02789727, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(236, 1000533, 100022076, 100022078, 5, 40.75323595, -74.02613372, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(237, 1000533, 100022076, 100022078, 6, 40.75240898, -74.02638048, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(238, 1000533, 100022076, 100022078, 7, 40.75114921, -74.02676672, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(239, 1000533, 100022076, 100022078, 8, 40.74998288, -74.02711943, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(240, 1000533, 100022076, 100022078, 9, 40.74979289, -74.02718783, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(241, 1000533, 100022076, 100022078, 10, 40.74860943, -74.02756333, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(242, 1000533, 100022076, 100022078, 11, 40.74734146, -74.02795762, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(243, 1000533, 100022076, 100022078, 12, 40.74606533, -74.02832508, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(244, 1000533, 100022076, 100022078, 13, 40.74477496, -74.02872741, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(245, 1000533, 100022076, 100022078, 14, 40.74417955, -74.02891249, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(246, 1000533, 100022076, 100022078, 15, 40.74423323, -74.02915657, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(265, 1000533, 100022074, 100022075, 1, 40.73611847, -74.02903050, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(266, 1000533, 100022074, 100022075, 2, 40.73613244, -74.02910644, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(267, 1000533, 100022074, 100022075, 3, 40.73652443, -74.02898524, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(268, 1000533, 100022074, 100022075, 4, 40.73707926, -74.02881022, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(269, 1000533, 100022074, 100022075, 5, 40.73724846, -74.02979459, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(270, 1000533, 100022074, 100022075, 6, 40.73851459, -74.02940299, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(271, 1000533, 100022074, 100022075, 7, 40.73980204, -74.02902681, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(272, 1000533, 100022074, 100022075, 8, 40.74104831, -74.02862984, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(273, 1000533, 100022074, 100022075, 9, 40.74115094, -74.02921389, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(274, 1000533, 100022074, 100022075, 10, 40.74125255, -74.02979526, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(275, 1000533, 100022074, 100022075, 11, 40.74139633, -74.03058115, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(276, 1000533, 100022074, 100022075, 12, 40.74155128, -74.03146762, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(277, 1000533, 100022074, 100022075, 13, 40.74188863, -74.03332371, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(278, 1000533, 100022074, 100022075, 14, 40.74204511, -74.03423700, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(279, 1000533, 100022074, 100022075, 15, 40.74220870, -74.03519723, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(280, 1000533, 100022074, 100022075, 16, 40.74315976, -74.03493169, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(281, 1000533, 100022074, 100022075, 17, 40.74321056, -74.03522539, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(282, 1000533, 100022074, 100022075, 18, 40.74322732, -74.03532564, '2018-10-10 17:44:28', '2018-10-10 17:44:28');
qur;

        $query = str_replace('1000533', $this->tour->id, $query);
        $query = str_replace('100022074', $this->stop1->id, $query);
        $query = str_replace('100022075', $this->stop2->id, $query);
        $query = str_replace('100022076', $this->stop3->id, $query);
        $query = str_replace('100022077', $this->stop4->id, $query);
        $query = str_replace('100022078', $this->stop5->id, $query);
        \DB::insert($query);
    }

    public function sendAnalytics($model, $action = 'start', $time = null)
    {
        if ($model instanceof \App\Tour) {
            return $this->postJson("/mobile/tours/{$model->id}/track", [
                'activity' => [
                    [
                        'action' => $action,
                        'device_id' => $this->device->id,
                        'timestamp' => $time ?: strtotime('now'),
                    ],
                ],
            ])->assertStatus(200);
        } elseif ($model instanceof \App\TourStop) {
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
    public function it_can_calculate_the_distance_between_two_stops_if_there_is_no_route_data()
    {
        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stop1, $this->stop2);

        $this->assertEquals(0.5927164968112091, $distance);
    }

    /** @test */
    public function it_can_calculate_the_distance_between_two_stops_when_there_is_route_data()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);
        $distance = $ac->getDistanceBetweenStops($this->stop1, $this->stop2);

        $this->assertEquals(0.8447459269516513, $distance);
    }

    /** @test */
    public function it_can_determine_the_first_stop_of_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($this->stop1->id, $ac->getFirstStop()->id);
    }

    /** @test */
    public function it_can_determine_the_last_stop_of_a_tour()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($this->stop5->id, $ac->getLastStop()->id);
    }

    /** @test */
    public function if_the_first_stop_of_the_tour_is_missing_it_will_throw_an_exception()
    {
        $this->tour->update(['start_point_id' => null]);
        $ac = new AdventureCalculator($this->tour);

        $this->expectException(UntraceableTourException::class);
        $ac->getFirstStop();
    }

    /** @test */
    public function if_the_last_stop_of_the_tour_is_missing_it_will_throw_an_exception()
    {
        $this->tour->update(['end_point_id' => null]);
        $ac = new AdventureCalculator($this->tour);

        $this->expectException(UntraceableTourException::class);
        $ac->getLastStop();
    }

    /** @test */
    public function it_can_get_the_next_stops_of_a_stop()
    {
        $ac = new AdventureCalculator($this->tour);

        $next = $ac->getNextStops($this->stop1);
        $this->assertCount(1, $next);
        $this->assertEquals($next[0]->id, $this->stop2->id);

        $next = $ac->getNextStops($this->stop2);
        $this->assertCount(2, $next);
        $this->assertEquals($next[0]->id, $this->stop3->id);
        $this->assertEquals($next[1]->id, $this->stop4->id);
    }

    /** @test */
    public function it_throws_an_error_getting_next_stops_if_the_next_stop_is_empty()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->stop1->update(['next_stop_id' => null]);

        $this->expectException(UntraceableTourException::class);
        $next = $ac->getNextStops($this->stop1);
    }

    /** @test */
    public function it_throws_an_error_getting_next_stops_if_a_choice_next_stop_is_empty()
    {
        $ac = new AdventureCalculator($this->tour);

        $this->stop2->choices()->first()->update(['next_stop_id' => null]);

        $this->expectException(UntraceableTourException::class);
        $next = $ac->getNextStops($this->stop2);
    }

    /** @test */
    public function it_can_get_all_stop_permutations()
    {
        $ac = new AdventureCalculator($this->tour);

        $paths = $ac->getPossiblePaths();

        // stop1 -> stop2 -> stop3 -> stop5
        // stop1 -> stop2 -> stop4 -> stop5
        // stop1 -> stop2 -> stop3 -> stop4 -> stop 5
        $this->assertEquals($paths->toArray(), [
            [$this->stop1->id, $this->stop2->id, $this->stop3->id, $this->stop5->id],
            [$this->stop1->id, $this->stop2->id, $this->stop4->id, $this->stop5->id],
            [$this->stop1->id, $this->stop2->id, $this->stop3->id, $this->stop4->id, $this->stop5->id],
        ]);
    }

    /** @test */
    public function it_can_determine_the_shortest_route_for_a_tour_when_the_stops_have_routes()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);

        list($route, $distance) = $ac->getShortestRoute();

        $this->assertEquals(2.1500689713536545, $distance);
        $this->assertEquals([1, 2, 4, 5], $route);
    }

    /** @test */
    public function it_can_determine_the_shortest_route_for_a_tour_when_there_are_no_stop_routes()
    {
        $ac = new AdventureCalculator($this->tour);

        list($route, $distance) = $ac->getShortestRoute();

        $this->assertEquals(1.6110865039252085, $distance);
        $this->assertEquals([1, 2, 4, 5], $route);
    }

    /** @test */
    public function it_can_calculate_the_clock_par_for_a_tour()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);

        $par = $ac->getPar();

        $this->assertEquals(51, $par);
    }

    /** @test */
    public function it_can_calculate_the_points_for_a_users_time()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);

        $points = $ac->calculatePoints(55);

        $this->assertEquals(192, $points);

        $points = $ac->calculatePoints(55.3);

        $this->assertEquals(192, $points);

        $points = $ac->calculatePoints(55.6);

        $this->assertEquals(191, $points);
    }

    /** @test */
    public function it_can_calculate_if_a_users_score_qualifies_for_a_trophy()
    {
        $this->insertStopRouteData();

        $ac = new AdventureCalculator($this->tour);

        $score = $ac->calculatePoints(55);

        $this->assertEquals(192, $score);

        $this->assertTrue($ac->scoreQualifiesForTrophy($score));

        $score = $ac->calculatePoints(90);

        $this->assertEquals(122, $score);

        $this->assertFalse($ac->scoreQualifiesForTrophy($score));
    }

    /** @test */
    public function when_a_tour_is_started_it_should_create_a_score_card()
    {
        $this->withoutExceptionHandling();

        $this->insertStopRouteData();

        $this->sendAnalytics($this->tour, 'start');

        $this->assertCount(1, $this->user->scoreCards()->get());

        $score = ScoreCard::for($this->tour, $this->user);

        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($ac->getPar(), $score->par);
    }

    /** @test */
    public function when_a_tour_is_finished_it_should_calculate_and_return_the_score_card()
    {
        $this->withoutExceptionHandling();

        $this->insertStopRouteData();

        $startTime = strtotime('30 minutes ago');

        $this->sendAnalytics($this->tour, 'start', $startTime);

        $score = ScoreCard::for($this->tour, $this->user);

        $stopTime = strtotime('now');

        $response = $this->sendAnalytics($this->tour, 'stop', $stopTime)
            ->assertJsonFragment(['won_trophy' => true])
            ->assertJsonFragment(['points' => 200]);

        $this->assertEquals(Carbon::createFromTimestampUTC($stopTime), $score->fresh()->finished_at);

        $this->assertEquals(30, $score->fresh()->duration);

        $ac = new AdventureCalculator($this->tour);

        $this->assertEquals($ac->calculatePoints(30), $score->fresh()->points);
    }

    /** @test */
    public function if_a_tour_par_changes_since_the_user_started_it_wouldnt_affect_their_score()
    {
        $this->withoutExceptionHandling();

        $this->insertStopRouteData();

        $startTime = strtotime('30 minutes ago');

        $this->sendAnalytics($this->tour, 'start', $startTime);

        $score = ScoreCard::for($this->tour, $this->user);
        $score->update(['par' => 15]);
        $score = $score->fresh();

        $stopTime = strtotime('now');

        $this->sendAnalytics($this->tour, 'stop', $stopTime);

        $this->assertEquals(Carbon::createFromTimestampUTC($stopTime), $score->fresh()->finished_at);

        $this->assertEquals(30, $score->fresh()->duration);

        $ac = new AdventureCalculator($this->tour);
        $this->assertEquals($ac->calculatePoints(30, $score->par), $score->fresh()->points);
        $this->assertEquals(170, $score->fresh()->points);
    }

    /** @test */
    public function when_a_user_gets_a_high_enough_score_they_are_awarded_a_trophy()
    {
        $this->withoutExceptionHandling();

        $this->insertStopRouteData();

        $startTime = strtotime('30 minutes ago');
        $stopTime = strtotime('now');

        $this->sendAnalytics($this->tour, 'start', $startTime);
        $this->sendAnalytics($this->tour, 'stop', $stopTime);

        $score = $this->signInUser->user->scoreCards()->forTour($this->tour)->first();

        $this->assertTrue($score->won_trophy);
    }

    /** @test */
    public function when_a_user_does_not_complete_the_tour_in_time_they_do_not_get_a_trophy()
    {
        $this->withoutExceptionHandling();

        $this->insertStopRouteData();

        $startTime = strtotime('200 minutes ago');
        $stopTime = strtotime('now');

        $this->sendAnalytics($this->tour, 'start', $startTime);
        $this->sendAnalytics($this->tour, 'stop', $stopTime);

        $score = $this->signInUser->user->scoreCards()->forTour($this->tour)->first();

        $this->assertFalse($score->won_trophy);
    }

    /** @test */
    public function when_a_regular_tour_is_started_it_should_set_the_total_number_of_stops()
    {
        $this->withoutExceptionHandling();

        $this->tour->update(['type' => TourType::OUTDOOR]);

        $this->sendAnalytics($this->tour, 'start');

        $this->assertCount(1, $this->signInUser->user->scoreCards()->get());

        $score = ScoreCard::for($this->tour, $this->user);

        $this->assertEquals($this->tour->stops()->count(), $score->total_stops);
    }

    /** @test */
    public function an_adventure_can_be_started_again_after_finishing()
    {
        $this->withoutExceptionHandling();

        $score = ScoreCard::create([
            'is_adventure' => true,
            'par' => 60,
            'total_stops' => 5,
            'stops_visited' => 5,
            'started_at' => \Carbon\Carbon::now()->subMinutes(120),
            'finished_at' => \Carbon\Carbon::now(),
            'tour_id' => $this->tour->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertCount(1, $this->user->scoreCards()->get());

        $startTime = strtotime('30 minutes ago');
        $this->sendAnalytics($this->tour, 'start', $startTime)
            ->assertJsonFragment(['points' => 0]);

        $this->assertCount(2, $this->user->scoreCards()->get());

        $score2 = ScoreCard::for($this->tour, $this->user);

        $this->assertNotNull($score->finished_at);
        $this->assertNull($score2->finished_at);
        $this->assertNotEquals($score->id, $score2->id);
    }

    /** @test */
    public function an_adventure_can_be_started_again_even_when_not_finished()
    {
        $this->withoutExceptionHandling();

        $score = ScoreCard::create([
            'is_adventure' => true,
            'par' => 60,
            'total_stops' => 5,
            'stops_visited' => 5,
            'started_at' => \Carbon\Carbon::now()->subMinutes(120),
            'finished_at' => null,
            'tour_id' => $this->tour->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertCount(1, $this->user->scoreCards()->get());

        $startTime = strtotime('30 minutes ago');
        $this->sendAnalytics($this->tour, 'start', $startTime)
            ->assertJsonFragment(['points' => 0]);

        $this->assertCount(2, $this->user->scoreCards()->get());

        $score2 = ScoreCard::for($this->tour, $this->user);

        $this->assertNull($score->finished_at);
        $this->assertNull($score2->finished_at);
        $this->assertNotEquals($score->id, $score2->id);
    }

    /** @test */
    public function only_completed_adventuress_count_towards_a_users_score()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start', strtotime('50 minutes ago'))
            ->assertJsonFragment(['points' => 0]);

        $this->sendAnalytics($this->tour, 'stop', strtotime('now'))
            ->assertJsonFragment(['points' => 186]);

        $this->sendAnalytics($this->tour, 'start', strtotime('25 minutes ago'))
            ->assertJsonFragment(['points' => 0]);

        $score = ScoreCard::for($this->tour, $this->user);

        $this->assertEquals(0, $score->points);

        $this->assertEquals(186, $this->user->fresh()->stats->points);
    }

    /** @test */
    public function a_users_total_score_only_includes_their_best_for_an_adventure()
    {
        $this->withoutExceptionHandling();

        $this->sendAnalytics($this->tour, 'start', strtotime('50 minutes ago'))
            ->assertJsonFragment(['points' => 0]);

        $this->sendAnalytics($this->tour, 'stop', strtotime('now'))
            ->assertJsonFragment(['points' => 186]);

        $score = ScoreCard::for($this->tour, $this->user);

        $this->assertEquals(186, $score->points);

        $this->sendAnalytics($this->tour, 'start', strtotime('30 minutes ago'))
            ->assertJsonFragment(['points' => 0]);

        $this->sendAnalytics($this->tour, 'stop', strtotime('now'))
            ->assertJsonFragment(['points' => 200]);

        $score = ScoreCard::for($this->tour, $this->user);

        $this->assertEquals(200, $score->points);

        $this->assertEquals(200, $this->user->fresh()->stats->points);
    }

    /** @test */
    public function when_a_user_unlocks_an_adventure_trophy_their_stats_automatically_update()
    {
        $this->withoutExceptionHandling();

        $this->assertEquals(0, $this->user->fresh()->stats->trophies);

        $this->sendAnalytics($this->tour, 'start', strtotime('30 minutes ago'));

        $this->sendAnalytics($this->tour, 'stop')
            ->assertJsonFragment(['won_trophy' => true]);

        $this->assertEquals(1, $this->user->fresh()->stats->trophies);
    }
}
