<?php

namespace App\Service\PriceFormatter;

use App\Entity\VO\Money;

interface PriceFormatter
{
    public function format(Money $money): string;
}
