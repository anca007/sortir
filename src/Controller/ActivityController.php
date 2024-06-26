<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Location;
use App\Entity\State;
use App\Form\ActivityType;
use App\Form\LocationType;
use App\Form\Model\SearchActivity;
use App\Form\SearchActivityType;
use App\Repository\ActivityRepository;
use App\Repository\StateRepository;
use App\Security\Voter\ActivityVoter;
use App\Utils\ActivityState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController
{
    const ACTIVITY_LIMIT = 20;

    #[Route(path: '/{page}', name: 'activity_home', requirements: ['page' => '\d+'])]
    public function home(Request            $request,
                         ActivityRepository $activityRepository,
                         ActivityState      $activityState,
                         int                $page = 1): Response
    {

        $activityState->update();

        $search = new SearchActivity();
        $search->setCampus($this->getUser()->getCampus());

        //vérification de la page
        $page = ($page <= 0 ? 1 : $page);
        //si on a cliqué sur le bouton rechercher je remets la page 1
        if ($request->query->get('searchButton') == 'submit') {
            $page = 1;
        }

        //passage en méthode GET, et ajout de de l'action sur le form
        $searchForm = $this->createForm(SearchActivityType::class, $search, [
            'method' => 'GET',
            'action' => $this->generateUrl("activity_home", ["page" => $page])
        ]);
        $searchForm->handleRequest($request);

        //récupération des sorties en fonction de la page
        $results = $activityRepository->getActivitiesWithFilters($search, $page);

        $maxPage = ceil($results['maxActivitities'] / self::ACTIVITY_LIMIT);

        //si demande d'accès à numéro de page trop éléve , renvoie une 404
        if ($page > $maxPage && $maxPage > 0) {
            throw $this->createNotFoundException("La page que vous demandez n'existe pas !");
        }

        return $this->render('activity/home.html.twig', [
                'activities' => $results['activities'],
                'maxActivities' => $results['maxActivitities'],
                'searchForm' => $searchForm->createView(),
                'currentPage' => $page,
                'maxPage' => $maxPage
            ]
        );
    }


    #[Route(path: '/sortie/créer', name: 'activity_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activity = new Activity();
        $activityForm = $this->createForm(ActivityType::class, $activity);

        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location, [
            'method' => 'POST',
            //permet d'avoir l'attribut action avec l'url
            'action' => $this->generateUrl('api_location_create')
        ]);


        $activityForm->handleRequest($request);
        if ($activityForm->isSubmitted() && $activityForm->isValid()) {

            if ($request->request->get('save') === 'validate') {
                $activity->setState($entityManager->getRepository(State::class)->findOneBy(['stateCode' => State::CREATION]));
                $this->addFlash('success', 'Sortie créée !');
            }

            if ($request->request->get('save') === 'publish') {
                $activity->setState($entityManager->getRepository(State::class)->findOneBy(['stateCode' => State::OPEN]));
                $this->addFlash('success', 'Sortie publiée !');
                $this->addFlash('success', 'Vous êtes inscrit !');
            }

            $activity->setOrganiser($this->getUser());
            //j'inscris automatiquement l'organisateur
            $activity->addParticipant($this->getUser());

            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('activity_detail', ['id' => $activity->getId()]);

        }

        return $this->render('activity/create.html.twig', [
            'activityForm' => $activityForm->createView(),
            'locationForm' => $locationForm->createView()
        ]);
    }

    #[Route(path: '/sortie/detail/{id}', name: 'activity_detail')]
    public function detail($id, ActivityRepository $activityRepository): Response
    {
        //TODO s'assurer de bien renvoyer une sortie ou une page 404
        return $this->render('activity/detail.html.twig', [
            'activity' => $activityRepository->find($id),
        ]);
    }

    //utilisation du param_converter implicite, permet de récupérer directement l'instance de l'objet désiré
    //https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
    #[Route(path: '/sortie/modifier/{activity}', name: 'activity_edit')]
    public function edit(Activity $activity, Request $request, EntityManagerInterface $entityManager): Response
    {

        if ($this->isGranted(ActivityVoter::EDIT, $activity)) {

            $activityForm = $this->createForm(ActivityType::class, $activity);

            $location = new Location();
            $locationForm = $this->createForm(LocationType::class, $location, [
                'method' => 'POST',
                //permet d'avoir l'attribut action avec l'url
                'action' => $this->generateUrl('api_location_create')
            ]);

            $activityForm->handleRequest($request);

            if ($activityForm->isSubmitted() && $activityForm->isValid()) {

                if ($request->request->get('save') === 'validate') {
                    $activity->setState($entityManager->getRepository(State::class)->findOneBy(['stateCode' => State::CREATION]));
                    $this->addFlash('success', 'Sortie modifiée !');
                }

                if ($request->request->get('save') === 'publish') {
                    $activity->setState($entityManager->getRepository(State::class)->findOneBy(['stateCode' => State::OPEN]));
                    $this->addFlash('success', 'Sortie publiée !');
                    $this->addFlash('success', 'Vous êtes inscrit !');
                }

                $entityManager->persist($activity);
                $entityManager->flush();

                return $this->redirectToRoute('activity_detail', ['id' => $activity->getId()]);

            }

            return $this->render('activity/edit.html.twig', [
                'activityForm' => $activityForm->createView(),
                'locationForm' => $locationForm->createView(),
                'activity' => $activity
            ]);


        } else {

            $this->addFlash('error', "Vous ne pouvez pas modifier cette sortie");
            return $this->redirectToRoute('activity_home', ['page' => 1]);
        }


    }

//    /**
    //     * @Route ("/sortie/ajax/modifier", name="activity_ajax_edit")
    //     */
    //    public function editAjax(Request $request, EntityManagerInterface $entityManager, ActivityRepository $activityRepository): Response
    //    {
    //
    //        $data = json_decode($request->getContent());
    //
    //        $location = $activityRepository->find($data->activity_id);
    //
    //        $activityForm = $this->createForm(ActivityType::class, $location);
    //
    //        return $this->render('views/forms/activity_form.html.twig', [
    //            'activityForm' => $activityForm->createView()
    //        ]);
    //    }
    #[Route(path: '/sortie/inscrire/{id}', name: 'activity_register')]
    public function register(
        int                    $id,
        ActivityRepository     $activityRepository,
        EntityManagerInterface $manager,
        Request                $request,
        StateRepository        $stateRepository
    ): Response
    {
        $activity = $activityRepository->find($id);

        if (!$activity) {
            throw $this->createNotFoundException("Action impossible !");
        }

        if ($this->isGranted(ActivityVoter::REGISTER, $activity)) {

            $activity->addParticipant($this->getUser());

            if (count($activity->getParticipants()) == $activity->getMaxRegistrationNumber()) {
                $closedState = $stateRepository->findOneBy(['stateCode' => State::CLOSED]);
                $activity->setState($closedState);
            }

            $manager->persist($activity);
            $manager->flush();

            $this->addFlash('success', 'Vous êtes inscrit !');
        } else {
            $this->addFlash('warning', 'Vous ne pouvez pas vous inscrire à cette sortie !');
        }

        return $this->redirect($request->headers->get('referer'));

    }

    #[Route(path: '/sortie/desinscrire/{id}', name: 'activity_unsubscribe')]
    public function unsubscribe(int                    $id,
                                ActivityRepository     $activityRepository,
                                EntityManagerInterface $manager,
                                StateRepository        $stateRepository,
                                Request                $request): Response
    {
        $activity = $activityRepository->find($id);

        if (!$activity) {
            throw $this->createNotFoundException("Action impossible !");
        }

        if ($this->isGranted(ActivityVoter::QUIT, $activity)) {

            $activity->removeParticipant($this->getUser());

            $openState = $stateRepository->findOneBy(['stateCode' => State::OPEN]);
            $activity->setState($openState);

            $manager->persist($activity);
            $manager->flush();

            $this->addFlash('success', 'Vous êtes désinscrit !');

        } else {
            $this->addFlash('warning', 'Vous ne pouvez pas vous désinscrire !');
        }

        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirectToRoute('activity_home');
        }

    }

    #[Route(path: '/sortie/annuler/{id}', name: 'activity_cancel')]
    public function cancel($id, ActivityRepository $activityRepository, EntityManagerInterface $manager, Request $request, StateRepository $stateRepository): Response
    {
        $activity = $activityRepository->find($id);

        if (!$activity) {
            throw $this->createNotFoundException("Action impossible !");
        }

        if ($activity->getOrganiser() === $this->getUser() && $activity->getState()->getStateCode() == State::OPEN) {

            if ($request->request->get('cancel_reason')) {

                $state = $stateRepository->findOneBy(['stateCode' => State::CANCELED]);

                $activity->setDescription($activity->getDescription() . '***** ANNULEE *****' . $request->request->get('cancel_reason'));
                $activity->setState($state);
                $manager->persist($activity);
                $manager->flush();

                $this->addFlash('warning', 'La sortie a été annulée');
                return $this->redirectToRoute('activity_home', ['page' => 1]);

            }

        } else {

            $this->addFlash("error", "Vous ne pouvez pas annuler cette sortie !");
            return $this->redirectToRoute('activity_home', ['page' => 1]);

        }

        return $this->render('activity/cancel.html.twig', [
            'location' => $activity
        ]);
    }

    #[Route(path: '/sortie/supprimer/{id}', name: 'activity_delete')]
    public function delete(
        int                    $id,
        ActivityRepository     $activityRepository,
        EntityManagerInterface $entityManager
    )
    {


        $activity = $activityRepository->find($id);

        if (!$activity) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        if ($this->isGranted(ActivityVoter::DELETE, $activity)) {

            $entityManager->remove($activity);
            $entityManager->flush();

            $this->addFlash('success', 'La sortie a été supprimée !');
        }
        return $this->redirectToRoute('activity_home');
    }


}
