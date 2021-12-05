<?php

namespace App\Event;

use App\Entity\EventLog;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManager;

class ProductSubscriber implements EventSubscriberInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();

    }

    public static function getSubscribedEvents()
    {
        return [
            ProductHit::NAME => 'onProductHit',
        ];
    }

    public function onProductHit(ProductHit $event)
    {
        $log = new EventLog();
        $log->setEventType(EventLog::TYPE_VIEWED);
        $log->setProductId($event->getId());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
