<?php

namespace App\Controller\Api;

use App\Entity\City;
use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Utils\api\GeoCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api/location', name: 'api_location_')]
class LocationController extends AbstractController
{
    #[Route(path: '/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response   {

        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location,[
        'method' => 'POST',
            //permet d'avoir l'attribut action avec l'url
            'action' => $this->generateUrl('api_location_create')
        ]);

        $locationForm->handleRequest($request);

        if($locationForm->isSubmitted() && $locationForm->isValid()){

            $entityManager->persist($location);
            $entityManager->flush();

        }else{
            return $this->render('views/forms/location_form.html.twig', [
                'locationForm' => $locationForm->createView()
            ]);
        }

        return $this->json($location, 200, [], ["groups" => "location"]);
    }

    #[Route(path: '/find-with-geocode', name: 'find_geo_code')]
    public function getLocationWithGeoCode(Request $request, CityRepository $cityRepository, GeoCode $geoCode): Response
    {

        $data = json_decode($request->getContent());
        /**
         * @var City $city
         */
        $city = $cityRepository->find($data->city);

        $result = $geoCode->callApi($data->address, $city);

        return $this->json($result);
    }

    #[Route(path: '/find-by-city', name: 'retrieve_by_city')]
    public function retrieveByCity(Request $request, LocationRepository $locationRepository){

        $data = json_decode($request->getContent());

        $locations = $locationRepository->findBy(['city' => $data->city]);

        return $this->json($locations, Response::HTTP_OK, [], ['groups' => 'location']);

    }

    #[Route(path: '/find-by-id', name: 'retrieve_by_id')]
    public function retrieveOne(LocationRepository $locationRepository, Request $request){

        $id = $request->get('id');

        $location = $locationRepository->find($id);

        return $this->json($location, Response::HTTP_OK, [], ['groups' => 'location']);

    }

}
