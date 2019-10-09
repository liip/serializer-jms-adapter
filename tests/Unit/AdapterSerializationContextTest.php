<?php

declare(strict_types=1);

namespace Tests\Liip\Serializer\Adapter\JMS\Unit;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use Liip\Serializer\Adapter\JMS\AdapterSerializationContext;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class AdapterSerializationContextTest extends TestCase
{
    public function testCustomExclusionStrategy(): void
    {
        $context = AdapterSerializationContext::create();
        $this->assertInstanceOf(AdapterSerializationContext::class, $context);

        $context->addExclusionStrategy($this->createMock(ExclusionStrategyInterface::class));

        $this->assertTrue($context->hasCustomExclusionStrategy());
    }

    public function testNoCustomExclusionStrategy(): void
    {
        $context = AdapterSerializationContext::create();
        $this->assertInstanceOf(AdapterSerializationContext::class, $context);

        $context->setVersion('3');

        $this->assertFalse($context->hasCustomExclusionStrategy());
    }

    public function testNoExclusionStrategy(): void
    {
        $context = AdapterSerializationContext::create();
        $this->assertInstanceOf(AdapterSerializationContext::class, $context);

        $this->assertFalse($context->hasCustomExclusionStrategy());
    }
}
