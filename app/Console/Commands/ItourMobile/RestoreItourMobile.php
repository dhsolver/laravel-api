<?php

namespace App\Console\Commands\ItourMobile;

use App\Console\Commands\ItourMobile\Models\TourStop as OldStop;
use App\Console\Commands\ItourMobile\Models\Tour as OldTour;
use App\Console\Commands\ItourMobile\Models\UserAccount;
use Intervention\Image\Exception\NotSupportedException;
use App\Console\Commands\ItourMobile\Models\RoutePoint;
use App\Http\Controllers\Traits\UploadsMedia;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Console\Command;
use App\MobileUser;
use App\SuperAdmin;
use App\TourRoute;
use App\TourStop;
use App\Client;
use App\Tour;
use App\User;
use App\Media;
use App\Admin;
use Intervention\Image\Exception\NotReadableException;
use App\Exceptions\InvalidImageException;
use App\Exceptions\ImageTooSmallException;

class RestoreItourMobile extends Command
{
    use UploadsMedia;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'itourmobile:restore {password}';

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
    protected $errorCount = 0;

    /**
     * Holds an array of all the account emails that throw dupe key exceptions.
     *
     * @var array
     */
    protected $duplicateEmails = [];

    protected $superAdmin;
    protected $lostAndFound;
    protected $missingFiles = [];

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
        $this->passwordOverride = $this->argument('password');

        $this->loadUsersWithTours();
        Admin::unguard();
        Client::unguard();
        MobileUser::unguard();

        echo "Converting Users...\n";

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

        $this->convertUserAccounts();

        echo "Converting Tours...\n";
        $this->convertTours();

        echo "Converting Tours Routes...\n";
        $this->convertTourRoutes();

        echo "Converting Tour Stops...\n";
        $this->convertStops();

        $this->printErrors();

        echo "Restoration complete.\n";
    }

    public function printErrors()
    {
        echo "{$this->errorCount} errors occurred.\n";

        if (count($this->duplicateEmails) > 0) {
            echo 'Duplicate Emails: ' . count($this->duplicateEmails) . "\n";
            foreach ($this->duplicateEmails as $email) {
                echo ' - ' . $email . "\n";
            }
        }
    }

    public function loadUsersWithTours()
    {
        $this->userIdsWithTours = OldTour::whereNotNull('tour_owner')
            ->distinct('tour_owner')
            ->get()
            ->pluck('tour_owner')
            ->toArray();
    }

    public function convertUserAccounts()
    {
        foreach (UserAccount::all() as $user) {
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
                    // dupe email
                    $this->errorCount++;
                    array_push($this->duplicateEmails, $attributes['email']);
                    continue;
                }
                dd($ex);
            }
        }
    }

    public function convertTours()
    {
        foreach (OldTour::all() as $old) {
            if (empty($old->tour_title)) {
                $this->info("Tour has no title: {$old->tour_id}, skipping...\n");
                continue;
            }
            $tour = Tour::make([
                'id' => $this->idPrefix . $old->tour_id,
                'user_id' => $this->idPrefix . $old->tour_owner,
                'title' => $old->tour_title,
                'description' => $old->tour_description,
                'type' => $old->tour_type == 5 ? 'indoor' : 'outdoor',
            ]);

            if ($old->tour_owner == 0 || !User::where('id', $tour->user_id)->exists()) {
                // user does not exist, set it to lost and found
                echo 'Tour owner not found: ' . $tour->id . "\n";
                $tour->user_id = $this->lostAndFound->id;
            }

            if (Tour::where('title', $old->tour_title)->exists()) {
                $tour->title = $old->tour_title . ' 2';
            }

            try {
                $tour->main_image_id = $this->createImage($old->tour_image_large, $tour->user_id);
            } catch (NotSupportedException $ex) {
                echo 'Bad image format: ' . $old->tour_image_large . "\n";
                continue;
            } catch (NotReadableException $ex) {
                echo 'Bad image format: ' . $old->tour_image_large . "\n";
                continue;
            } catch (InvalidImageException $ex) {
                echo 'Bad image format: ' . $old->stop_photo_original . "\n";
                continue;
            } catch (ImageTooSmallException $ex) {
                echo 'Image too small: ' . $old->stop_photo_original . "\n";
                continue;
            }

            try {
                $tour->intro_audio_id = $this->createAudio($old->tour_intro_music, $tour->user_id);
            } catch (NotSupportedException $ex) {
                echo 'Bad audio format: ' . $old->tour_intro_music . "\n";
                continue;
            } catch (NotReadableException $ex) {
                echo 'Bad audio format: ' . $old->tour_intro_music . "\n";
                continue;
            } catch (InvalidImageException $ex) {
                echo 'Bad image format: ' . $old->stop_photo_original . "\n";
                continue;
            } catch (ImageTooSmallException $ex) {
                echo 'Image too small: ' . $old->stop_photo_original . "\n";
                continue;
            }

            try {
                $tour->background_audio_id = $this->createAudio($old->tour_music, $tour->user_id);
            } catch (NotSupportedException $ex) {
                echo 'Bad audio format: ' . $old->tour_music . "\n";
                continue;
            } catch (NotReadableException $ex) {
                echo 'Bad audio format: ' . $old->tour_music . "\n";
                continue;
            } catch (InvalidImageException $ex) {
                echo 'Bad audio format: ' . $old->stop_photo_original . "\n";
                continue;
            }

            if (!empty($old->icon)) {
                try {
                    $tour->pin_image_id = $this->createIcon($old->icon->url, $tour->user_id);
                } catch (NotSupportedException $ex) {
                    echo 'Bad image format: ' . $old->icon->url . "\n";
                    continue;
                } catch (NotReadableException $ex) {
                    echo 'Bad image format: ' . $old->icon->url . "\n";
                    continue;
                }
            }

            $tour->video_url = $old->video_url;
            $tour->facebook_url = empty($old->facebook) ? null : $old->facebook;
            $tour->twitter_url = $old->twitter_url;

            // TODO:
            // - handle inapp ids ?
            // - handle tour_ready_for_sale = published
            // - pricing?
            // - subscriptions?
            // - tour_webapp?
            // - use google api to ping lat/long and perfect addresses?
            $tour->pricing_type = 'free';

            $tour->save();

            $tour->location()->update($old->location);
        }
    }

    public function iTourPath($filename)
    {
        return config('junket.itourfiles') . '/' . $filename;
    }

    public function createMedia($type, $oldFilename, $user_id)
    {
        if (empty($oldFilename)) {
            return null;
        }

        $file = $this->iTourPath($oldFilename);
        if (!file_exists($file)) {
            // image file not found
            $this->addMissingFile($file);
            return;
        }

        $f = new UploadedFile($file, basename($file), mime_content_type($file));
        if ($type == 'image') {
            $filename = $this->storeImage($f, 'images', 'jpg', true);
        } elseif ($type == 'icon') {
            $filename = $this->storeIcon($f, 'images', 'png');
        } elseif ($type == 'audio') {
            $filename = $this->storeFile($f, 'audio', 'mp3');
        }

        $media = Media::create([
            'file' => $filename,
            'user_id' => $user_id,
        ]);

        return $media->id;
    }

    public function createImage($oldFilename, $user_id)
    {
        return $this->createMedia('image', $oldFilename, $user_id);
    }

    public function createAudio($oldFilename, $user_id)
    {
        return $this->createMedia('audio', $oldFilename, $user_id);
    }

    public function createIcon($oldFilename, $user_id)
    {
        return $this->createMedia('icon', $oldFilename, $user_id);
    }

    /**
     * Returns the user account that belongs to Lance.
     *
     * @return \App\Admin
     */
    public function getLance()
    {
        return Admin::where('email', 'lance.zaal@iworksllc.com')->first();
    }

    public function addMissingFile($file)
    {
        $this->errorCount++;
        array_push($this->missingFiles, $file);
        echo "File not found: $file\n";
    }

    public function convertTourRoutes()
    {
        $bad = [];

        foreach (RoutePoint::orderBy('point_order')->get() as $old) {
            if (empty($old->tour_id)) {
                echo "No tour associated with route point\n";
                array_push($bad, $old);
                continue;
            }

            if (!Tour::where('id', $this->idPrefix . $old->tour_id)->exists()) {
                echo "Tour does not exist for route point\n";
                array_push($bad, $old);
                continue;
            }

            if (empty($old->point_lat) || empty($old->point_lon)) {
                echo "Route is missing coordinates\n";
                array_push($bad, $old);
                continue;
            }

            TourRoute::create([
                'tour_id' => $this->idPrefix . $old->tour_id,
                'order' => $old->point_order,
                'latitude' => $old->point_lat,
                'longitude' => $old->point_lon,
            ]);
        }

        echo 'Total invalid routes: ' . count($bad) . "\n";
    }

    public function convertStops()
    {
        foreach (OldStop::orderBy('stop_order')->get() as $old) {
            $old->tour_id = $this->idPrefix . $old->tour_id;

            $tour = Tour::where('id', $old->tour_id)->first();
            if (empty($tour)) {
                echo 'Tour does not exist for stop ' . $old->stop_id . "\n";
                continue;
            }

            $stop = TourStop::make([
                'id' => $this->idPrefix . $old->stop_id,
                'title' => $old->stop_title,
                'description' => $old->stop_description,
                'tour_id' => $tour->id,
                'order' => $old->order,
                'video_url' => $old->video_url,
                'play_radius' => $old->play_distance,
            ]);

            try {
                $stop->main_image_id = $this->createImage($old->stop_photo_original, $tour->user_id);
            } catch (NotSupportedException $ex) {
                echo 'Bad image format: ' . $old->stop_photo_original . "\n";
                continue;
            } catch (NotReadableException $ex) {
                echo 'Bad image format: ' . $old->stop_photo_original . "\n";
                continue;
            } catch (InvalidImageException $ex) {
                echo 'Bad image format: ' . $old->stop_photo_original . "\n";
                continue;
            } catch (ImageTooSmallException $ex) {
                echo 'Image too small: ' . $old->stop_photo_original . "\n";
                continue;
            }

            try {
                $stop->intro_audio_id = $this->createAudio($old->stop_audio_url, $tour->user_id);
            } catch (NotSupportedException $ex) {
                echo 'Bad audio format: ' . $old->stop_audio_url . "\n";
                continue;
            } catch (NotReadableException $ex) {
                echo 'Bad audio format: ' . $old->stop_audio_url . "\n";
                continue;
            } catch (InvalidImageException $ex) {
                echo 'Bad image format: ' . $old->stop_photo_original . "\n";
                continue;
            }

            $i = 0;
            foreach ($old->images as $image) {
                $i++;
                try {
                    switch ($i) {
                        case 1:
                            $stop->image1_id = $this->createImage($image->image_url, $tour->user_id);
                            break;
                        case 2:
                            $stop->image2_id = $this->createImage($image->image_url, $tour->user_id);
                            break;
                        case 3:
                            $stop->image3_id = $this->createImage($image->image_url, $tour->user_id);
                            break;
                        default:
                            echo 'Image count exceeded 3 for stop: ' . $old->stop_id . "\n";
                            break;
                    }
                } catch (NotSupportedException $ex) {
                    echo 'Bad image format: ' . $image->image_url . "\n";
                    continue;
                } catch (NotReadableException $ex) {
                    echo 'Bad image format: ' . $image->image_url . "\n";
                    continue;
                } catch (NotReadableException $ex) {
                    echo 'Bad audio format: ' . $old->stop_audio_url . "\n";
                    continue;
                } catch (InvalidImageException $ex) {
                    echo 'Bad image format: ' . $old->stop_photo_original . "\n";
                    continue;
                }
            }

            $stop->save();

            $stop->location()->update($old->location);
        }
    }
}
