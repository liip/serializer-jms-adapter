<?php

declare(strict_types=1);

namespace Liip\Serializer\Adapter\JMS;

use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;

class AdapterSerializationContextFactory implements SerializationContextFactoryInterface
{
    public function createSerializationContext()
    {
        return new AdapterSerializationContext();
    }
}
