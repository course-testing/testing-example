<?php

namespace App\Tests\integration\Repository;

use App\Entity\EventLog;
use App\Event\ProductHit;
use Doctrine\DBAL\ParameterType;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductSubscriberTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const PRODUCT_ID = 1;

    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->eventDispatcher = self::getContainer()->get('event_dispatcher');
    }

    public function test_that_hit_event_is_stored()
    {
        // Given There are collected events for products
        // /api/fixtures/event-log.yaml
        $event = new ProductHit(self::PRODUCT_ID);
        $viewedCounter = $this->getViewedCounterByProductId(self::PRODUCT_ID);

        // When the event is dispatched
        $this->eventDispatcher->dispatch($event, ProductHit::NAME);

        // Then new VIEWED event log is stored
        $viewedCounterAfterCall = $this->getViewedCounterByProductId(self::PRODUCT_ID);
        $this->assertEquals($viewedCounter + 1, $viewedCounterAfterCall);
    }

    private function getViewedCounterByProductId(int $productId): int
    {
        $viewedStats = $this->entityManager->createQueryBuilder()
            ->select('count(e.productId) as eventsNumber')
            ->from(EventLog::class, 'e')
            ->andWhere('e.productId = :productId')
            ->andWhere('e.eventType = :eventType')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('eventType', EventLog::TYPE_VIEWED, ParameterType::INTEGER)
            ->getQuery()
            ->getResult();

        return $viewedStats[0]['eventsNumber'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
