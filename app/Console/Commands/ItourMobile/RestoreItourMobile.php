<?php

namespace App\Console\Commands\ItourMobile;

use Illuminate\Console\Command;
use App\Console\Commands\ItourMobile\Models\Tour as OldTour;
use App\Console\Commands\ItourMobile\Models\UserAccount;
use App\Admin;
use App\MobileUser;
use App\Client;
use Illuminate\Database\QueryException;
use App\Tour;
use App\SuperAdmin;
use App\User;
use App\Http\Controllers\Traits\UploadsMedia;
use Illuminate\Http\UploadedFile;
use App\Media;

class RestoreItourMobile extends Command
{
    use UploadsMedia;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'itourmobile:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore all tour data directly from iTourMobile database.';

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
        $this->loadUsersWithTours();
        Admin::unguard();
        Client::unguard();
        MobileUser::unguard();

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
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
            $old->tour_id = $this->idPrefix . $old->tour_id;

            $tour = Tour::make([
                'id' => $old->tour_id,
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

            // $tour->main_image_id = $this->createImage($old->tour_image_large, $tour->user_id);
            // $tour->intro_audio_id = $this->createAudio($old->tour_intro_music, $tour->user_id);
            // $tour->background_audio_id = $this->createAudio($old->tour_music, $tour->user_id);

            $tour->video_url = $old->video_url;
            $tour->facebook_url = empty($old->facebook) ? null : $old->facebook;
            $tour->twitter_url = $old->twitter_url;

            // TODO:
            // - handle routes
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
            $filename = $this->storeImage($f, 'images', 'jpg');
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
}
