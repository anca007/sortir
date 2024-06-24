<?php


namespace App\Utils\api;

use App\Entity\City;

const API_KEY = "4";
const API_URL = "";

class GeoCode
{

    public function callApi(string $address, City $city)
    {
        $address_encode = urlencode($address . " " . $city->getZipcode() . " " . $city->getName());
        $api_url = API_URL . "address=" . $address_encode . "&key=" . API_KEY;

        $coord = json_decode(file_get_contents($api_url));

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
