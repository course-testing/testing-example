<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ProductHit extends Event
{
    public const NAME = 'product.hit';

    /**
     * @var int
     */
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
