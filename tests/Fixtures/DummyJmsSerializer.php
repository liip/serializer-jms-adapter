<?php

declare(strict_types=1);

namespace Tests\Liip\Serializer\Adapter\JMS\Fixtures;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;

interface DummyJmsSerializer extends SerializerInterface, ArrayTransformerInterface
{
}
