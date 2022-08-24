<?php

declare(strict_types=1);

namespace Liip\Serializer\Adapter\JMS;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext;

/**
 * Decorated JMS Serialization Context so that we can track if there is a custom exclusion strategy.
 */
class AdapterSerializationContext extends SerializationContext
{
    /**
     * @var bool
     */
    private $hasCustomExclusionStrategy = false;

    public static function create(): SerializationContext
    {
        return new self();
    }

    public function addExclusionStrategy(ExclusionStrategyInterface $strategy): Context
    {
        if (0 !== strpos(\get_class($strategy), 'JMS')) {
            $this->hasCustomExclusionStrategy = true;
        }

        return parent::addExclusionStrategy($strategy);
    }

    public function hasCustomExclusionStrategy(): bool
    {
        return $this->hasCustomExclusionStrategy;
    }
}
