<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\EventLogRepository;
use App\Service\PriceFormatter\PriceFormatter;

class ProductDataProvider implements DenormalizedIdentifiersAwareItemDataProviderInterface, RestrictedDataProviderInterface
{
    private ItemDataProviderInterface $itemDataProvider;
    private PriceFormatter $priceFormatter;
    private EventLogRepository $eventLogRepository;

    public function __construct(
        ItemDataProviderInterface $itemDataProvider,
        PriceFormatter $priceFormatter,
        EventLogRepository $eventLogRepository
    ) {
        $this->itemDataProvider = $itemDataProvider;
        $this->priceFormatter = $priceFormatter;
        $this->eventLogRepository = $eventLogRepository;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        /** @var Product|null $product */
        $product = $this->itemDataProvider->getItem($resourceClass, $id, $operationName, $context);

        if (!$product) {
            return null;
        }

        $product->setFormattedPrice($this->priceFormatter->format($product->getPrice()));
        $product->setStats($this->eventLogRepository->findByProductId($product->getId()));

        return $product;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === Product::class;
    }
}

