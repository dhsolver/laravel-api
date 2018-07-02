<?php

namespace App\Console\Commands\ItourMobile;

use Illuminate\Console\Command;
use App\Console\Commands\ItourMobile\Models\Tour;
use App\Console\Commands\ItourMobile\Models\UserAccount;
use App\Admin;
use App\MobileUser;
use App\Client;

class RestoreItourMobile extends Command
{
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
    protected $passwordOverride = 'password';

    /**
     * Used to add digits infront of IDs in order to avoid collision.
     *
     * @var integer
     */
    protected $idPrefix = 1000;

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
        echo "Converting users...\n";
        $this->convertUserAccounts();

        echo "Restoration complete.\n";
    }

    public function loadUsersWithTours()
    {
        $this->userIdsWithTours = Tour::whereNotNull('tour_owner')
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

            if ($user->user_type == 3) {
                // admin
                $admin = Admin::create([
                    'id' => $this->idPrefix . $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => bcrypt($this->passwordOverride),
                ], true);

                $admin->id = $this->idPrefix . $user->id;
                $admin->save();
            } elseif (in_array($user->id, $this->userIdsWithTours) || !empty($user->company_name)) {
                // client
            } else {
                // mobile user
            }
        }
    }
}
