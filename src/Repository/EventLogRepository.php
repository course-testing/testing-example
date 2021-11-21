<?php

namespace App\Repository;

use App\Entity\EventLog;
use App\Entity\VO\Stats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventLog[]    findAll()
 * @method EventLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventLogRepository extends ServiceEntityRepository implements ProductStatsRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventLog::class);
    }

    public function findByProductId(int $productId): Stats
    {
        /**
         * Złożone, skomplikowane, zasobożerne, długo trwające, sięgające do odrębnej BD zapytanie...
         */
        $result = $this->createQueryBuilder('e')
            ->select('e.eventType', 'count(e.productId) as eventsNumber')
            ->andWhere('e.productId = :productId')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->groupBy('e.eventType', 'e.productId')
            ->getQuery()
            ->getResult();

        return $this->mapResultsToStats($result);
    }

    private function mapResultsToStats(array $result): Stats
    {
        $reducedResults = array_reduce($result, function(array $stats, $item) {
            $stats[$item['eventType']] = $item['eventsNumber'];

            return $stats;
        }, []);

        $stats = new Stats();
        $stats->viewed = $reducedResults[EventLog::TYPE_VIEWED] ?? 0;
        $stats->addedToCart = $reducedResults[EventLog::TYPE_ADDED_TO_CART]  ?? 0;
        $stats->bought = $reducedResults[EventLog::TYPE_BOUGHT] ?? 0;

        return $stats;
    }
}
