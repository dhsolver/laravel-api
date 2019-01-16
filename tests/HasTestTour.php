<?php

namespace Tests;

use App\DeviceType;
use App\Os;
use App\ScoreCard;
use App\StopChoice;
use App\TourStop;
use App\TourType;
use App\Media;
use App\Tour;
use App\Device;
use App\Action;

trait HasTestTour
{
    /**
     * @var \App\Tour
     */
    protected $tour;

    /**
     * @var \App\User
     */
    protected $user;

    /**
     * @var Collection
     */
    protected $stops;

    /**
     * User score for the last tour started from the startTour method
     *
     * @var \App\TourStop
     */
    protected $score;

    /**
     * Helper to start the current tour.
     *
     * @param $timestamp
     * @param Tour $tour
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function startTour($timestamp = null, $tour = null)
    {
        if (empty($timestamp)) {
            $timestamp = strtotime('now');
        }

        if (empty($tour)) {
            $tour = $this->tour;
        }

        $response = $this->postJson(route('mobile.scores.start'), ['tour_id' => $tour->id, 'timestamp' => $timestamp]);
        $this->score = ScoreCard::find($response->decodeResponseJson()['id']);

        return $response;
    }

    /**
     * Visit a stop along the current tour.
     *
     * @param $stop
     * @param int $timestamp
     * @param ScoreCard $scoreCard
     * @return mixed
     */
    public function visitStop($stop, $timestamp = null, $scoreCard = null, $skippedQuestion = false)
    {
        if (empty($timestamp)) {
            $timestamp = strtotime('now');
        }

        if (empty($scoreCard)) {
            $scoreCard = $this->score;
        }

        return $this->postJson(
            route('mobile.scores.progress', ['score' => $scoreCard]),
            [
                'stop_id' => modelid($stop),
                'timestamp' => $timestamp,
                'skipped_question' => $skippedQuestion,
            ]
        );
    }

    /**
     * Insert test tour data with or without route data.
     *
     * @return array
     */
    public function createTestTour()
    {
        $tour = factory(Tour::class)->states('published')->create([
            'pricing_type' => 'free',
            'type' => TourType::OUTDOOR,
            'prize_details' => 'free stuff',
            'prize_instructions' => 'redeem at x location',
            'prize_time_limit' => 36,
            'has_prize' => true,
        ]);

        factory(TourStop::class, 5)->create([
            'tour_id' => $tour->id,
        ]);

        $stops = $tour->stops()->ordered()->get();

        return [$tour, $stops];
    }

    /**
     * Insert test tour data with or without route data.
     *
     * @param bool $withRoutes
     * @return array
     */
    public function createTestAdventure($withRoutes = false, $user = null)
    {
        if (empty($user)) {
            $user = $this->signInUser;
        }

        $audio = Media::create([
            'file' => str_random(10),
            'length' => 155,
            'user_id' => $user->id,
        ]);

        $tour = factory(Tour::class)->states('published')->create([
            'pricing_type' => 'free',
            'background_audio_id' => $audio->id,
            'type' => TourType::ADVENTURE,
            'prize_details' => 'free stuff',
            'prize_instructions' => 'redeem at x location',
            'prize_time_limit' => 36,
            'has_prize' => true,
            'user_id' => $user->id,
        ]);

        $stop1 = factory(TourStop::class)->create(['tour_id' => $tour, 'intro_audio_id' => $audio->id]);
        $stop1->location->update([
            'address1' => '77 River St',    // Hoboken Cigars
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.73611847,
            'longitude' => -74.0290305,
        ]);

        $stop2 = factory(TourStop::class)->create(['tour_id' => $tour, 'intro_audio_id' => $audio->id]);
        $stop2->location->update([
            'address1' => '500 Grand St',       // Grand Vin
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74331877,
            'longitude' => -74.03518617,
        ]);

        $stop3 = factory(TourStop::class)->create(['tour_id' => $tour, 'intro_audio_id' => $audio->id]);
        $stop3->location->update([
            'address1' => '163 14th St',        // Dino's
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.75336903,
            'longitude' => -74.02768135,
        ]);

        $stop4 = factory(TourStop::class)->create(['tour_id' => $tour, 'intro_audio_id' => $audio->id]);
        $stop4->location->update([
            'address1' => '11th St',        // Baseball Monument
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74993106,
            'longitude' => -74.02735949,
        ]);

        $stop5 = factory(TourStop::class)->create(['tour_id' => $tour, 'intro_audio_id' => $audio->id]);
        $stop5->location->update([
            'address1' => '622 Washington St',      // Benny Tunido's
            'address2' => null,
            'city' => 'Hoboken',
            'state' => 'NJ',
            'country' => 'US',
            'zipcode' => '07030',
            'latitude' => 40.74423323,
            'longitude' => -74.02915657,
        ]);

        $stop1->update(['next_stop_id' => $stop2->id]);

        factory(StopChoice::class)->create(['tour_stop_id' => $stop2->id, 'next_stop_id' => $stop3->id]);
        factory(StopChoice::class)->create(['tour_stop_id' => $stop2->id, 'next_stop_id' => $stop4->id]);
        $stop2->update(['is_multiple_choice' => true]);

        factory(StopChoice::class)->create(['tour_stop_id' => $stop3->id, 'next_stop_id' => $stop4->id]);
        factory(StopChoice::class)->create(['tour_stop_id' => $stop3->id, 'next_stop_id' => $stop5->id]);
        $stop3->update(['is_multiple_choice' => true]);

        $stop4->update(['next_stop_id' => $stop5->id]);

        // set main image for publishable tour
        $media = factory('App\Media')->create(['user_id' => $user->id]);

        $tour->update([
            'start_point_id' => $stop1->id,
            'end_point_id' => $stop5->id,
            'main_image_id' => $media->id,
        ]);

        // create random location for tour
        // this should not affect the distances
        $tour->location()->delete();
        factory('App\Location')->create([
            'locationable_type' => 'App\Tour',
            'locationable_id' => $tour->id,
        ]);

        if ($withRoutes) {
            $this->insertStopRouteData($tour);
        }

        return [$tour, $tour->stops];
    }

    /**
     * Insert stop route data.
     *
     * @param Tour $tour
     * @return void
     */
    public function insertStopRouteData($tour)
    {
        $query = <<<qur
INSERT INTO `stop_routes` (`tour_id`, `stop_id`, `next_stop_id`, `order`, `latitude`, `longitude`, `created_at`, `updated_at`)
VALUES
(1000533, 100022075, 100022076, 1, 40.74331877, -74.03518617, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 2, 40.74311233, -74.03526875, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 3, 40.74319768, -74.03496164, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 4, 40.74441805, -74.03458436, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 5, 40.74569275, -74.03420176, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 6, 40.74695205, -74.03381672, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 7, 40.74823832, -74.03344121, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 8, 40.74807373, -74.03250244, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 9, 40.74935136, -74.03211684, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 10, 40.75060924, -74.03173132, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 11, 40.75188466, -74.03134053, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 12, 40.75408110, -74.03066730, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 13, 40.75388198, -74.02969365, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 14, 40.75379868, -74.02946567, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 15, 40.75367067, -74.02881925, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 16, 40.75351380, -74.02790620, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 17, 40.75337665, -74.02794107, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022076, 18, 40.75336903, -74.02768135, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022077, 1, 40.74331877, -74.03518617, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022077, 2, 40.74321513, -74.03523445, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022077, 3, 40.74316382, -74.03496690, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022077, 4, 40.74441104, -74.03460145, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022077, 5, 40.74344172, -74.02912706, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022077, 6, 40.74989347, -74.02715296, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022075, 100022077, 7, 40.74993106, -74.02735949, '2018-10-10 16:51:41', '2018-10-10 16:51:41'),
(1000533, 100022077, 100022078, 1, 40.74993106, -74.02735949, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 2, 40.74989144, -74.02713284, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 3, 40.74982235, -74.02715698, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 4, 40.74857879, -74.02752713, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 5, 40.74732081, -74.02792543, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 6, 40.74603453, -74.02830198, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 7, 40.74475514, -74.02869500, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 8, 40.74418731, -74.02886801, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022077, 100022078, 9, 40.74423323, -74.02915657, '2018-10-10 16:51:58', '2018-10-10 16:51:58'),
(1000533, 100022076, 100022077, 1, 40.75336903, -74.02768135, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 2, 40.75340038, -74.02788636, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 3, 40.75351670, -74.02784211, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 4, 40.75336381, -74.02693619, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 5, 40.75323427, -74.02613421, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 6, 40.75301382, -74.02620429, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 7, 40.75239308, -74.02637226, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 8, 40.75113026, -74.02677057, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 9, 40.75050493, -74.02695430, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 10, 40.74989078, -74.02715546, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022077, 11, 40.74993106, -74.02735949, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 1, 40.75336903, -74.02768135, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 2, 40.75336903, -74.02768135, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 3, 40.75339037, -74.02793750, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 4, 40.75352142, -74.02789727, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 5, 40.75323595, -74.02613372, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 6, 40.75240898, -74.02638048, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 7, 40.75114921, -74.02676672, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 8, 40.74998288, -74.02711943, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 9, 40.74979289, -74.02718783, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 10, 40.74860943, -74.02756333, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 11, 40.74734146, -74.02795762, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 12, 40.74606533, -74.02832508, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 13, 40.74477496, -74.02872741, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 14, 40.74417955, -74.02891249, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022076, 100022078, 15, 40.74423323, -74.02915657, '2018-10-10 16:52:09', '2018-10-10 16:52:09'),
(1000533, 100022074, 100022075, 1, 40.73611847, -74.02903050, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 2, 40.73613244, -74.02910644, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 3, 40.73652443, -74.02898524, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 4, 40.73707926, -74.02881022, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 5, 40.73724846, -74.02979459, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 6, 40.73851459, -74.02940299, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 7, 40.73980204, -74.02902681, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 8, 40.74104831, -74.02862984, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 9, 40.74115094, -74.02921389, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 10, 40.74125255, -74.02979526, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 11, 40.74139633, -74.03058115, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 12, 40.74155128, -74.03146762, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 13, 40.74188863, -74.03332371, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 14, 40.74204511, -74.03423700, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 15, 40.74220870, -74.03519723, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 16, 40.74315976, -74.03493169, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 17, 40.74321056, -74.03522539, '2018-10-10 17:44:28', '2018-10-10 17:44:28'),
(1000533, 100022074, 100022075, 18, 40.74322732, -74.03532564, '2018-10-10 17:44:28', '2018-10-10 17:44:28');
qur;

        $query = str_replace('1000533', $tour->id, $query);
        $query = str_replace('100022074', $tour->stops[0]->id, $query);
        $query = str_replace('100022075', $tour->stops[1]->id, $query);
        $query = str_replace('100022076', $tour->stops[2]->id, $query);
        $query = str_replace('100022077', $tour->stops[3]->id, $query);
        $query = str_replace('100022078', $tour->stops[4]->id, $query);
        \DB::insert($query);
    }

    public function fakeActivityForStop($stop)
    {
        $devices = [
            factory(Device::class)->create(['os' => Os::IOS, 'type' => DeviceType::PHONE]),
            factory(Device::class)->create(['os' => Os::IOS, 'type' => DeviceType::PHONE]),
            factory(Device::class)->create(['os' => Os::IOS, 'type' => DeviceType::TABLET]),
            factory(Device::class)->create(['os' => Os::ANDROID, 'type' => DeviceType::PHONE]),
            factory(Device::class)->create(['os' => Os::ANDROID, 'type' => DeviceType::TABLET]),
        ];

        for ($i = 0; $i < 5; $i++) {
            $start = strtotime("$i days ago 13:00");
            $end = strtotime("$i days ago 13:05");

            $stop->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::START,
                'created_at' => $start,
                'device_id' => $devices[$i]->id,
            ]);

            $stop->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::STOP,
                'created_at' => $end,
                'device_id' => $devices[$i]->id,
            ]);

            $stop->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::VISIT,
                'created_at' => $end,
                'device_id' => $devices[$i]->id,
            ]);

            $stop->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::LIKE,
                'created_at' => $end,
                'device_id' => $devices[$i]->id,
            ]);
        }
    }

    public function fakeActivityForTour($tour)
    {
        $devices = [
            factory(Device::class)->create(['os' => Os::IOS, 'type' => DeviceType::PHONE]),
            factory(Device::class)->create(['os' => Os::IOS, 'type' => DeviceType::PHONE]),
            factory(Device::class)->create(['os' => Os::IOS, 'type' => DeviceType::TABLET]),
            factory(Device::class)->create(['os' => Os::ANDROID, 'type' => DeviceType::PHONE]),
            factory(Device::class)->create(['os' => Os::ANDROID, 'type' => DeviceType::TABLET]),
        ];

        $deviceIndex = 0;

        for ($i = 0; $i < 25; $i++) {
            $device = $devices[$deviceIndex];

            $start = strtotime("$i days ago 13:00");
            $end = strtotime("$i days ago 14:00");

            $tour->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::START,
                'created_at' => $start,
                'device_id' => $device->id,
            ]);

            $tour->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::STOP,
                'created_at' => $end,
                'device_id' => $device->id,
            ]);

            $tour->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::DOWNLOAD,
                'created_at' => $end,
                'device_id' => $device->id,
            ]);

            $tour->activity()->create([
                'user_id' => $this->user->id,
                'action' => Action::LIKE,
                'created_at' => $end,
                'device_id' => $device->id,
            ]);

            $deviceIndex++;
            if ($deviceIndex > count($devices) - 1) {
                $deviceIndex = 0;
            }
        }
    }
}
