<?php

namespace App\Console\Commands\ItourMobile\Traits;

use GuzzleHttp\Client as GuzzleClient;

trait HandlesGeocoding
{
    /**
     * Lookup the given coodinates with the Google Geocoding API
     * and return a location array.
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function reverseGeocode($lat, $lon)
    {
        $apiKey = config('services.google-maps.api_key');

        if (empty($apiKey)) {
            $this->error('Google Maps API key is not set!');
            exit();
        }

        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lon}&sensor=true&key={$apiKey}";

        $client = new GuzzleClient();
        $result = $client->get($url);

        $data = json_decode($result->getBody());

        if ($data->status == 'ZERO_RESULTS') {
            return null;
        } elseif ($data->status != 'OK') {
            // error
            $this->error('Google Maps API error: ' . $data->status);
            exit();
        }

        $first = $data->results[0];
        $components = $first->address_components;

        $streetNo = $this->getAddressComponent('street_number', $components);
        $route = $this->getAddressComponent('route', $components);

        return [
            'address1' => $streetNo ? "{$streetNo} {$route}" : $route,
            'city' => $this->getAddressComponent('locality', $components),
            'state' => $this->getAddressComponent('administrative_area_level_1', $components),
            'zipcode' => $this->getAddressComponent('postal_code', $components),
            'country' => $this->getAddressComponent('country', $components),
        ];
    }

    /**
     * Get the result entry for the given component type.
     *
     * @param string $type
     * @param array $components
     * @return string
     */
    public function getAddressComponent($type, $components)
    {
        foreach ($components as $c) {
            if (in_array($type, $c->types)) {
                if (in_array($type, ['administrative_area_level_1', 'postal_code', 'country'])) {
                    return $c->short_name;
                } else {
                    return $c->long_name;
                }
            }
        }
    }

    /**
     * Checks if the given location array is missing data
     * and looks up the valid address using Google Geocoding API.
     *
     * @param array $location
     * @return array
     */
    public function convertLocation($location)
    {
        if (
            empty($location['address1'])
            || empty($location['city'])
            || empty($location['state'])
            || empty($location['country'])
            || empty($location['zipcode'])
        ) {
            if (! empty($location['latitude']) && ! empty($location['longitude'])) {
                // $this->info('Looking up address from coordinates..');

                return array_merge(
                    $location,
                    $this->reverseGeocode($location['latitude'], $location['longitude'])
                );
            } else {
                // $this->error('Incomplete address!');
                return false;
            }
        }

        return $location;
    }
}
