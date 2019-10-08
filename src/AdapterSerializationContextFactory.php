<?php

declare(strict_types=1);

namespace Liip\Serializer\Adapter\JMS;

use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;

class AdapterSerializationContextFactory implements SerializationContextFactoryInterface
{
    public function createSerializationContext(): SerializationContext
    {
        return new AdapterSerializationContext();
    }
}
