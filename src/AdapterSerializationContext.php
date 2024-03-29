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
    private bool $hasCustomExclusionStrategy = false;

    public static function create(): SerializationContext
    {
        return new self();
    }

    public function addExclusionStrategy(ExclusionStrategyInterface $strategy): Context
    {
        if (!str_starts_with($strategy::class, 'JMS')) {
            $this->hasCustomExclusionStrategy = true;
        }

        return parent::addExclusionStrategy($strategy);
    }

    public function hasCustomExclusionStrategy(): bool
    {
        return $this->hasCustomExclusionStrategy;
    }
}
