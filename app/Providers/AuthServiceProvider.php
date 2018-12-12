<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\MobileUser' => 'App\Policies\MobileUserPolicy',
        'App\Client' => 'App\Policies\ClientPolicy',
        'App\Admin' => 'App\Policies\AdminPolicy',
        'App\Tour' => 'App\Policies\TourPolicy',
        'App\Media' => 'App\Policies\MediaPolicy',
        'App\User' => 'App\Policies\UserPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
