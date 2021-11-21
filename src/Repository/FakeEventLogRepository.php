<?php

namespace App\Repository;

use App\Entity\VO\Stats;
use App\Repository\ProductStatsRepository;

// TODO move it to tests/ dir

class FakeEventLogRepository implements ProductStatsRepository
{
    public function findByProductId(int $productId): Stats
    {
        // To może być bardzo prosty Fake
        // To może być Fake działający na BD, z którą komunikacja jest szybsza (np. SQLite)
        // TO może być Stub któremu definiujemy stan

        $stats = new Stats();
        $stats->viewed = 100;
        $stats->addedToCart = 200;
        $stats->bought = 300;

        return $stats;
    }
}
