<?php

namespace App\Service;

use App\Entity\VO\Money;
use App\Service\PriceFormatter\PriceFormatter;

class BasicPriceFormatter implements PriceFormatter
{
    // do not try it at home
    // use library, eg. https://github.com/moneyphp/money | https://github.com/brick/money
    public function format(Money $money): string
    {
        $map = [
            'PLN' => fn ($base, $decimal) => sprintf('%s.%s zł', $base, $decimal),
            'USD' => fn ($base, $decimal) => sprintf('$ %s.%s', $base, $decimal),
        ];

        $valueBase = (string) $money->amount;
        $valueLength = strlen($valueBase);
        $subunit = 2;
        $baseFormatted = substr($valueBase, 0, $valueLength - $subunit);
        $decimalDigits = substr($valueBase, $valueLength - $subunit);

        return $map[$money->currency]($baseFormatted, $decimalDigits);
    }
}
