<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\VO\Stats;
use App\Service\PriceFormatter\PriceFormatter;

class ProductDataProvider implements DenormalizedIdentifiersAwareItemDataProviderInterface, RestrictedDataProviderInterface
{
    private ItemDataProviderInterface $itemDataProvider;
    private PriceFormatter $priceFormatter;

    public function __construct(ItemDataProviderInterface $itemDataProvider, PriceFormatter $priceFormatter)
    {
        $this->itemDataProvider = $itemDataProvider;
        $this->priceFormatter = $priceFormatter;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        /** @var Product|null $item */
        $item = $this->itemDataProvider->getItem($resourceClass, $id, $operationName, $context);

        if (!$item) {
            return null;
        }

        $item->setFormattedPrice($this->priceFormatter->format($item->getPrice()));

        $stats = new Stats();
        $stats->viewed = 1001;
        $stats->addedToCart = 89;
        $stats->bought = 43;
        $item->setStats($stats);

        return $item;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === Product::class;
    }
}

