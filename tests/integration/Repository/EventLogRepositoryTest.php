<?php

namespace App\Tests\Repository;

use App\Entity\VO\Stats;
use App\Repository\EventLogRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventLogRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private EventLogRepository $repository;

    protected function setUp(): void
    {
        $this->repository = self::getContainer()->get(EventLogRepository::class);
    }

    public function test_that_returns_stats_for_product()
    {
        // Given There are collected events for products
        // /api/fixtures/event-log.yaml

        // When I request for stats by product ID
        $stats = $this->repository->findByProductId(1);

        // Then I get calculated stats
        $expectedStats = new Stats();
        $expectedStats->viewed = 1;
        $expectedStats->addedToCart = 2;
        $expectedStats->bought = 3;

        $this->assertEquals($expectedStats, $stats);
    }

    public function test_that_returns_stats_for_product_without_any_events()
    {
        // Given There are collected events for products
        // /api/fixtures/event-log.yaml

        // When I request for stats by product ID for product without any events
        $stats = $this->repository->findByProductId(3);

        // Then I get stats with 0 hits
        $expectedStats = new Stats();
        $expectedStats->viewed = 0;
        $expectedStats->addedToCart = 0;
        $expectedStats->bought = 0;

        $this->assertEquals($expectedStats, $stats);
    }
}
