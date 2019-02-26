<?php

declare(strict_types=1);

namespace Liip\Serializer\Adapter\JMS;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext as JMSSerializationContext;

/**
 * Decorated JMS Serialization Context so that we can track if there is a custom exclusion strategy.
 */
class AdapterSerializationContext extends JMSSerializationContext
{
    /**
     * @var bool
     */
    private $hasCustomExclusionStrategy = false;

    public static function create()
    {
        return new self();
    }

    public function addExclusionStrategy(ExclusionStrategyInterface $strategy)
    {
        if (0 !== \strpos(\get_class($strategy), 'JMS')) {
            $this->hasCustomExclusionStrategy = true;
        }

        return parent::addExclusionStrategy($strategy);
    }

    public function hasCustomExclusionStrategy(): bool
    {
        return $this->hasCustomExclusionStrategy;
    }
}
