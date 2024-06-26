<?php

namespace App\Controller\Api;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/city', name: 'api_city_')]
class CityController extends AbstractController
{
    #[Route(path: '', name: 'all')]
    public function retrieveAll(Request $request, CityRepository $cityRepository): Response
    {

        $data = json_decode($request->getContent());

        if(trim($data->zipcode) != ''){
            $cities = $cityRepository->findBy(['zipcode' => $data->zipcode]);
        }else{
            $cities = $cityRepository->findAll();
        }

        return $this->json($cities, 200, [], ["groups" => "city"]);
    }
}
