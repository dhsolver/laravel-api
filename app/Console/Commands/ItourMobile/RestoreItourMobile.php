<?php

namespace App\Console\Commands\ItourMobile;

use App\Console\Commands\ItourMobile\Models\TourStop as OldStop;
use App\Console\Commands\ItourMobile\Models\Tour as OldTour;
use App\Console\Commands\ItourMobile\Models\UserAccount;
use App\Console\Commands\ItourMobile\Models\RoutePoint;
use App\TourType;
use Illuminate\Database\QueryException;
use Illuminate\Console\Command;
use App\MobileUser;
use App\SuperAdmin;
use App\TourRoute;
use App\TourStop;
use App\Client;
use App\Tour;
use App\User;
use App\Admin;
use Carbon\Carbon;
use App\Console\Commands\ItourMobile\Traits\HandlesGeocoding;
use App\Console\Commands\ItourMobile\Traits\HandlesMedia;

class RestoreItourMobile extends Command
{
    use HandlesMedia, HandlesGeocoding;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'itourmobile:restore {password} {tour?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore all tour data directly from iTourMobile database.  The password parameter is used to create a password for all users in the system.';

    /**
     * Holds a list of user ids of the users that have tours.
     *
     * @var array
     */
    protected $userIdsWithTours = [];

    /**
     * Used to set all the user account passwords because we don't know the origianls.
     *
     * @var string
     */
    protected $passwordOverride = 'qweqwe';

    /**
     * Used to add digits infront of IDs in order to avoid collision.
     *
     * @var integer
     */
    protected $idPrefix = 1000;

    /**
     * Keeps count of all the errors that occur while running.
     *
     * @var integer
     */
    protected $superAdmin;
    protected $lostAndFound;
    protected $testTourId;
    protected $progress;
    protected $currentStop;
    protected $currentTour;
    protected $issues = 0;
    protected $validTourIds = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Setting up...');

        $this->passwordOverride = $this->argument('password');
        $this->testTourId = $this->argument('tour');

        $this->userIdsWithTours = OldTour::whereNotNull('tour_owner')
            ->distinct('tour_owner')
            ->get()
            ->pluck('tour_owner')
            ->toArray();

        Admin::unguard();
        Client::unguard();
        MobileUser::unguard();

        $this->superadmin = SuperAdmin::create([
            'email' => 'admin@wejunket.com',
            'name' => 'Master Account',
            'password' => bcrypt($this->passwordOverride),
        ]);

        $this->lostAndFound = Client::create([
            'email' => 'lostandfound@wejunket.com',
            'name' => 'Lost AndFound',
            'password' => bcrypt($this->passwordOverride),
        ]);

        if (empty($this->testTourId)) {
            $this->line('Converting users...');
            $this->convertUserAccounts();
            $this->progress->finish();
            $this->line('');
        }

        $this->line('Converting tours...');
        $this->convertTours();
        $this->progress->finish();
        $this->line('');

        $this->line('Converting tour routes...');
        $this->convertTourRoutes();
        $this->progress->finish();
        $this->line('');

        $this->line('Converting tour stops...');
        $this->convertStops();
        $this->progress->finish();
        $this->line('');

        $this->info('Restoration complete: ' . $this->issues . ' issues');
    }

    public function convertUserAccounts()
    {
        $this->progress = $this->output->createProgressBar(UserAccount::count());

        $ranOnce = false;
        foreach (UserAccount::all() as $user) {
            if ($ranOnce) {
                $this->progress->advance();
            }
            $ranOnce = true;

            if ($user->id == 1) {
                continue; // ignore master account
            }

            $attributes = [
                'id' => $this->idPrefix . $user->id,
                'name' => $user->name,
                'email' => strtolower($user->email),
                'password' => bcrypt($this->passwordOverride),
            ];

            try {
                if ($user->user_type == 3) {
                    // admin
                    $admin = Admin::make($attributes);
                    $admin->save(['create' => true]);
                } elseif (in_array($user->id, $this->userIdsWithTours) || !empty($user->company_name)) {
                    // client
                    $attributes['company_name'] = $user->company;
                    $client = Client::make($attributes);
                    $client->save(['create' => true]);
                } else {
                    // mobile user
                    $mu = MobileUser::make($attributes);
                    $mu->save(['create' => true]);
                }
            } catch (QueryException $ex) {
                if (str_contains($ex->getMessage(), 'users_email_unique')) {
                    $this->log("Duplicate email (user: {$user->id} email: {$user->email})");
                    continue;
                }

                $this->log("Unexpected error while creating user (user: {$user->id}): " . $ex->getMessage());
            }
        }
    }

    public function convertTours()
    {
        if (empty($this->testTourId)) {
            $tours = OldTour::where('tour_ready_for_sale', 1)->get();
        } else {
            $tours = OldTour::where('tour_id', $this->testTourId)->get();
        }
        $this->validTourIds = $tours->pluck('tour_id');

        $this->progress = $this->output->createProgressBar($tours->count());

        $ranOnce = false;
        foreach ($tours as $old) {
            $this->setCurrentTour($old);

            if ($ranOnce) {
                $this->progress->advance();
            }
            $ranOnce = true;

            if (empty($old->tour_title)) {
                $this->log("Tour has no title (tour: {$old->tour_id} title: '{$old->tour_title}')");
                continue;
            }
            $tour = Tour::make([
                'id' => $this->idPrefix . $old->tour_id,
                'user_id' => $this->idPrefix . $old->tour_owner,
                'title' => $old->tour_title,
                'description' => $old->tour_description,
                'type' => $old->tour_type == 5 ? TourType::INDOOR : TourType::OUTDOOR,
                'published_at' => $old->tour_ready_for_sale == 1 ? Carbon::now() : null,
            ]);

            if ($old->tour_owner == 0 || !User::where('id', $tour->user_id)->exists()) {
                // user does not exist, set it to lost and found
                // $this->log("Tour owner not found (tour: {$old->tour_id} user: {$old->tour_owner})");
                $tour->user_id = $this->lostAndFound->id;
            }

            if (Tour::where('title', $old->tour_title)->exists()) {
                $tour->title = $old->tour_title . ' 2';
            }

            if ($this->tourFileExists($old->tour_image_large)) {
                $tour->main_image_id = $this->createImage($old, 'tour_image_large', $tour->user_id);
            } else {
                $tour->main_image_id = $this->createImage($old, 'tour_image_600', $tour->user_id);
            }

            if ($tour->main_image_id === false) {
                $tour->main_image_id = null;
                $this->missingFileLog("Tour {$tour->id} is missing main_image");
                // continue;
            }

            $tour->intro_audio_id = $this->createAudio($old, 'tour_intro_music', $tour->user_id);
            if ($tour->intro_audio_id === false) {
                $tour->intro_audio_id = null;
                $this->missingFileLog("Tour {$tour->id} is missing intro_audio");
                // continue;
            }

            $tour->background_audio_id = $this->createAudio($old, 'tour_music', $tour->user_id);
            if ($tour->background_audio_id === false) {
                $tour->background_audio_id = null;
                $this->missingFileLog("Tour {$tour->id} is missing background_audio");
                // continue;
            }

            $tour->pin_image_id = $this->createIcon($old, $tour->user_id);
            if ($tour->pin_image_id === false) {
                $tour->pin_image_id = null;
                $this->missingFileLog("Tour {$tour->id} is missing pin_image");
                // continue;
            }

            $tour->video_url = $old->video_url;
            $tour->facebook_url = empty($old->facebook) ? null : $old->facebook;
            $tour->twitter_url = $old->twitter_url;

            $tour->pricing_type = 'free';

            $tour->save();

            if ($location = $this->convertLocation($old->location)) {
                $tour->location()->update($location);
            } else {
                $this->log("Failed geocoding lookup (tour: {$old->tour_id})");
                $tour->location()->update($old->location);
            }
        }
    }

    public function convertTourRoutes()
    {
        if (empty($this->testTourId)) {
            // $tours = OldTour::all()->pluck('tour_id')->toArray();
            $tours = OldTour::where('tour_ready_for_sale', 1)->get()->pluck('tour_id')->toArray();
            $oldRoutes = RoutePoint::whereIn('tour_id', $tours)->orderBy('point_order')->get();
        } else {
            $oldRoutes = RoutePoint::where('tour_id', $this->testTourId)->orderBy('point_order')->get();
        }

        $this->progress = $this->output->createProgressBar($oldRoutes->count());
        $ranOnce = false;

        foreach ($oldRoutes as $old) {
            if ($ranOnce) {
                $this->progress->advance();
            }
            $ranOnce = true;

            if (empty($old->tour_id)) {
                $this->log("Empty parent Tour for Route (route: {$old->point_id}, tour: {$old->tour_id})");
                continue;
            }

            if (!Tour::where('id', $this->idPrefix . $old->tour_id)->exists()) {
                $this->log("Missing parent Tour for Route (route: {$old->point_id}, tour: {$old->tour_id})");
                continue;
            }

            if (empty($old->point_lat) || empty($old->point_lon)) {
                $this->log("Route missing coordinates (route: {$old->point_id} tour: {$old->tour_id} lat: {$old->point_lat} lon: {$old->point_lon})");
                continue;
            }

            TourRoute::create([
                'tour_id' => $this->idPrefix . $old->tour_id,
                'order' => $old->point_order,
                'latitude' => $old->point_lat,
                'longitude' => $old->point_lon,
            ]);
        }
    }

    public function convertStops()
    {
        if (empty($this->testTourId)) {
            $stops = OldStop::whereIn('tour_id', $this->validTourIds)->orderBy('stop_order')->get();
        } else {
            $stops = OldStop::where('tour_id', $this->testTourId)->orderBy('stop_order')->get();
        }

        $this->progress = $this->output->createProgressBar($stops->count());

        $ranOnce = false;
        foreach ($stops as $old) {
            if ($ranOnce) {
                $this->progress->advance();
            }
            $ranOnce = true;

            $tour = Tour::where('id', $this->idPrefix . $old->tour_id)->first();
            if (empty($tour)) {
                $this->log("Missing parent Tour for Stop (stop: {$old->stop_id}, tour: {$old->tour_id})");
                continue;
            }

            $this->setCurrentStop($old);
            $this->setCurrentTour(OldTour::where('tour_id', $old->tour_id)->first());

            $stop = TourStop::make([
                'id' => $this->idPrefix . $old->stop_id,
                'title' => $old->stop_title,
                'description' => $old->stop_description,
                'tour_id' => $tour->id,
                'order' => $old->order,
                'video_url' => $old->video_url,
                'play_radius' => $old->play_distance,
            ]);

            $stop->main_image_id = $this->createImage($old, 'stop_photo_original', $tour->user_id);
            if ($stop->main_image_id === false) {
                $stop->main_image_id = null;
                $this->missingFileLog("Stop {$stop->id} of Tour {$tour->id} is missing main_image");
                // continue;
            }

            $stop->intro_audio_id = $this->createAudio($old, 'stop_audio_url', $tour->user_id);
            if ($stop->intro_audio_id === false) {
                $stop->intro_audio_id = null;
                $this->missingFileLog("Stop {$stop->id} of Tour {$tour->id} is missing intro_audio");
                // continue;
            }

            $i = 0;
            foreach ($old->images as $image) {
                $i++;
                if ($i > 3) {
                    $this->log("Image count exceeds 3 (tour: {$tour->id}, stop: {$old->stop_id})");
                }

                $field = "image{$i}_id";
                $stop->$field = $this->createImage($image, 'image_url', $tour->user_id);
                if ($stop->$field === false) {
                    $stop->$field = null;
                    $this->missingFileLog("Stop {$stop->id} of Tour {$tour->id} is missing image{$i}");
                    // continue;
                }
            }

            $stop->save();

            if ($location = $this->convertLocation($old->location)) {
                $stop->location()->update($location);
            } else {
                $this->log("Failed geocoding lookup (stop: {$old->stop_id})");
                $stop->location()->update($old->location);
            }
        }
    }

    public function log($text)
    {
        $this->issues++;

        \Storage::disk('local')->append('restore.log', $text);
    }

    public function missingFileLog($text)
    {
        \Storage::disk('local')->append('restore-files.log', $text);
    }

    public function setCurrentTour($tour)
    {
        $this->currentTour = $tour;
        $this->currentStop = null;
    }

    public function setCurrentStop($stop)
    {
        $this->currentTour = null;
        $this->currentStop = $stop;
    }
}
