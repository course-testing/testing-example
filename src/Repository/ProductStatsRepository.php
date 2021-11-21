<?php

namespace App\Repository;

use App\Entity\VO\Stats;

interface ProductStatsRepository
{
    public function findByProductId(int $productId): Stats;
}
