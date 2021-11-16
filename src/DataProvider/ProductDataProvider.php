<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\VO\Money;

class ProductDataProvider implements DenormalizedIdentifiersAwareItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $itemDataProvider;

    public function __construct(ItemDataProviderInterface $itemDataProvider)
    {
        $this->itemDataProvider = $itemDataProvider;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        /** @var Product|null $item */
        $item = $this->itemDataProvider->getItem($resourceClass, $id, $operationName, $context);

        if (!$item) {
            return null;
        }

        $item->setFormattedPrice($this->formatPrice($item->getPrice()));
        return $item;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === Product::class;
    }

    // do not try it at home
    // use library, eg. https://github.com/moneyphp/money | https://github.com/brick/money
    private function formatPrice(Money $money): string
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

    // SRP !
}

