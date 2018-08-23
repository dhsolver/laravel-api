<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Tour;
use App\Events\TourJoined;
use App\Mobile\Resources\JoinedToursResource;

class JoinedToursController extends Controller
{
    public function index()
    {
    }

    public function store(Tour $tour)
    {
        if (auth()->user()->hasJoinedTour($tour)) {
            return response()->json(JoinedToursResource::collection(auth()->user()->joinedTours));
        }

        auth()->user()->joinTour($tour);

        event(new TourJoined($tour, auth()->user(), request()->device_id));

        return response()->json(JoinedToursResource::collection(auth()->user()->fresh()->joinedTours));
    }

    public function validateApplePurchase($tour)
    {
        $validator = $this->app->singleton('ReceiptValidator\iTunes\Validator');

        try {
            $response = $validator->setReceiptData(request()->receipt_data)->validate();
        } catch (Exception $e) {
            return false;
            // return $this->fail(402, 'Could not validate purchase receipt.');
        }

        if ($response->isValid()) {
            return true;
            // return $this->success('Valid');
        }

        return false;
        // return $this->fail(402, 'Could not validate purchase receipt.');
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
