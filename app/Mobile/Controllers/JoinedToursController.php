<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Tour;
use App\Events\TourJoined;
use App\Http\Resources\JoinedTourCollection;

class JoinedToursController extends Controller
{
    /**
     * Get al list of Tour IDs that the User has joined.
     *
     * @return Illuminate/Http/Response
     */
    public function index()
    {
        return new JoinedTourCollection(auth()->user()->joinedTours);
    }

    /**
     * Add the current User to a specific Tour.
     *
     * @param Tour $tour
     * @return Illuminate/Http/Response
     */
    public function store(Tour $tour)
    {
        if (auth()->user()->hasJoinedTour($tour)) {
            return new JoinedTourCollection(auth()->user()->joinedTours);
        }

        if (!$tour->isFree()) {
            if (request()->has('receipt_data')) {
                // validate apple purchase
                if (!$this->validateApplePurchase()) {
                    return $this->fail(402, 'Could not validate purchase.');
                }
            } else {
                // validate google play purchase
                if (!$this->validateGooglePurchase()) {
                    return $this->fail(402, 'Could not validate purchase.');
                }
            }
        }

        auth()->user()->joinTour($tour);

        event(new TourJoined($tour, auth()->user(), request()->device_id));

        return new JoinedTourCollection(auth()->user()->fresh()->joinedTours);
    }

    public function validateApplePurchase($tour)
    {
        $validator = $this->app->singleton('ReceiptValidator\iTunes\Validator');

        try {
            $response = $validator->setReceiptData(request()->receipt_data)->validate();
        } catch (Exception $e) {
            return false;
        }

        if ($response->isValid()) {
            return true;
        }

        return false;
    }

    public function validateGooglePurchase()
    {
        $validator = $this->app->singleton('ReceiptValidator\GooglePlay\Validator');

        $googleAndroidPublisher = new \Google_Service_AndroidPublisher($googleClient);
        $validator = new \ReceiptValidator\GooglePlay\Validator($googleAndroidPublisher);

        try {
            $response = $validator->setPackageName('PACKAGE_NAME')
              ->setProductId('PRODUCT_ID')
              ->setPurchaseToken('PURCHASE_TOKEN')
              ->validateSubscription();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            // example message: Error calling GET ....: (404) Product not found for this application.
        }
    }
}
