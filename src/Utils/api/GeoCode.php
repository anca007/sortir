<?php


namespace App\Utils\api;

use App\Entity\City;

const API_KEY = "AIzaSyCnL0R57xn7FQg6DCT0uKehQ6prVPuzxb4";
const API_URL = "https://maps.googleapis.com/maps/api/geocode/json?";

class GeoCode
{

    public function callApi(string $address, City $city)
    {
        $address_encode = urlencode($address . " " . $city->getZipcode() . " " . $city->getName());
        $api_url = API_URL . "address=" . $address_encode . "&key=" . API_KEY;

        $coord = json_decode(file_get_contents($api_url));

       // dd($coord);

        $latitude = "";
        $longitude = "";
        $street = "";
        if ($coord->status == "OK") {
            $latitude = $coord->results[0]->geometry->location->lat;
            $longitude = $coord->results[0]->geometry->location->lng;
            $street = explode(', ', $coord->results[0]->formatted_address)[0];
        }

        return ['lat' => $latitude, 'lng' => $longitude, 'street' => $street];
    }


}
