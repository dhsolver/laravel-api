<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use ReceiptValidator\iTunes\Validator as iTunesValidator;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;

class PurchaseValidatorProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ReceiptValidator\iTunes\Validator', function ($app) {
            if (config('app.env') == 'production') {
                return new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION);
            } else {
                return new iTunesValidator(iTunesValidator::ENDPOINT_SANDBOX);
            }
        });

        $this->app->singleton('ReceiptValidator\GooglePlay\Validator', function ($app) {
            $client = new \Google_Client();
            $client->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
            $client->setApplicationName(config('services.google_play.app_name'));
            $client->setAuthConfig('google-service-account.json');

            if (config('app.env') == 'production') {
                return new PlayValidator(PlayValidator::ENDPOINT_PRODUCTION);
            } else {
                return new PlayValidator(PlayValidator::ENDPOINT_SANDBOX);
            }
        });
    }
}
