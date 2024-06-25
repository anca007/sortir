<?php

namespace App\Repository;

use App\Controller\ActivityController;
use App\Entity\Activity;
use App\Entity\State;
use App\Form\Model\SearchActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{

    private $user;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Activity::class);
        $this->user = $security->getUser();
    }

    public function getActivitiesWithFilters(SearchActivity $searchActivity, $page){

        $qb = $this->createQueryBuilder('s');
        $qb->innerJoin('s.campus', 'c');
        $qb->innerJoin('s.organiser', 'o');
        $qb->innerJoin('s.state', 'state');
        $qb->orderBy('s.startDate', 'DESC');

        $qb->andWhere("state.stateCode != 'HIS'");

        if($searchActivity->getName()){
            $qb->andWhere('s.name LIKE :name')->setParameter('name', '%'.$searchActivity->getName().'%');
        }

        if($searchActivity->getCampus()){
            $qb->andWhere('s.campus = :campus')->setParameter('campus', $searchActivity->getCampus());
        }

        if($searchActivity->getStartDate()){
            $qb->andWhere('s.startDate >= :startDate')->setParameter('startDate', $searchActivity->getStartDate());
        }

        if($searchActivity->getEndDate()){
            $qb->andWhere('s.startDate <= :endDate')->setParameter('endDate', $searchActivity->getEndDate());
        }

        if($searchActivity->getOrganiser()){
            $qb->andWhere('s.organiser = :organiser')->setParameter('organiser', $this->user);
        }

        if($searchActivity->getIsParticipant()){
            $qb->andWhere(':participant MEMBER OF s.participants ')->setParameter('participant', $this->user);
        }

        if($searchActivity->getIsNotParticipant()){
            $qb->andWhere(':notParticipant NOT MEMBER OF s.participants ')->setParameter('notParticipant', $this->user);
        }

        if($searchActivity->getOldActivity()){
            $qb->andWhere("state.stateCode = :stateCode")->setParameter('stateCode', State::IS_FINISHED);
        }

        //récupération des sorties en création si organisateur
        //soit je suis l'orga et l'état est en création
        //soit l'état doit être différent de création
        $qb->andWhere("(s.organiser = :user AND state.stateCode = :creStateCode) OR (state.stateCode != :creStateCode)")
            ->setParameter('user', $this->user)
            ->setParameter('creStateCode', State::CREATION);

        //compte tous les résultats avec les critères de recherches
        $maxActivities = $qb->select('COUNT(s)')->getQuery()->getSingleScalarResult();

        //les selects ici pour récupérer les résultats
        $qb->select('s')->addSelect('c')->addSelect('p')->addSelect('o')->addSelect('state');
        // + le leftJoin des participants
        $qb->leftJoin('s.participants', 'p');

        $query = $qb->getQuery();

        //ajout offset et limite pour la pagination
        $offset = ($page - 1) * ActivityController::ACTIVITY_LIMIT;
        $query->setMaxResults(ActivityController::ACTIVITY_LIMIT);
        $query->setFirstResult($offset);

        $activities = new Paginator($query);

        return [
            'maxActivitities' => $maxActivities,
            'activities' => $activities
        ];
    }

    public function findActivitiesToUpdate(){

        $stateCodes = [State::OPEN, State::CLOSED, State::IN_PROGRESS, State::IS_FINISHED];

        $qb = $this->createQueryBuilder('a');
        $qb->innerJoin('a.state', 'state')
            ->addSelect('state')
            ->andWhere("state.stateCode IN (:states)")
            ->setParameter("states", $stateCodes);


        return $qb->getQuery()->getResult();
    }
}
