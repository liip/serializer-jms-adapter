<?php

declare(strict_types=1);

namespace Tests\Liip\Serializer\Adapter\JMS\Unit;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializerInterface;
use Liip\Serializer\Adapter\JMS\AdapterSerializationContext;
use Liip\Serializer\Adapter\JMS\JMSSerializerAdapter;
use Liip\Serializer\SerializerInterface as LiipSerializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Tests\Liip\Serializer\Adapter\JMS\Fixtures\TestModel;

/**
 * @small
 */
class JMSSerializerAdapterTest extends TestCase
{
    private const SERIALIZED_XML = '<it-works><yes/></it-works>';
    private const SERIALIZED_JSON = '{"it-works":"yes"}';
    private const TYPE = TestModel::class;
    private const DATA = ['it-works' => 'yes'];

    /**
     * @var SerializerInterface&ArrayTransformerInterface&MockObject
     */
    private MockObject $jms;

    /**
     * @var LiipSerializer&MockObject
     */
    private MockObject $liip;

    private TestModel $model;

    protected function setUp(): void
    {
        $this->jms = $this->createMock(SerializerArrayTransformer::class);
        $this->liip = $this->createMock(LiipSerializer::class);
        $this->model = new TestModel();
    }

    public function testIncompleteConstructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Original serializer must implement both ArrayTransformerInterface and SerializerInterface');
        new JMSSerializerAdapter(
            $this->createMock(LiipSerializer::class),
            $this->createMock(SerializerInterface::class),
            new NullLogger()
        );
    }

    public function testSerialize(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());
        $context = new AdapterSerializationContext();

        $this->liip->expects($this->once())
            ->method('serialize')
            ->with($this->model, 'json')
            ->willReturn(self::SERIALIZED_JSON)
        ;

        $this->jms->expects($this->never())
            ->method('serialize')
        ;

        $json = $transform->serialize($this->model, 'json', $context);
        $this->assertSame(self::SERIALIZED_JSON, $json);
    }

    public function testSerializeFallbackOnFormat(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());
        $context = new AdapterSerializationContext();

        $this->liip->expects($this->never())
            ->method('serialize')
        ;

        $this->jms->expects($this->once())
            ->method('serialize')
            ->with($this->model, 'xml', $context)
            ->willReturn(self::SERIALIZED_XML)
        ;

        $xml = $transform->serialize($this->model, 'xml', $context);
        $this->assertSame(self::SERIALIZED_XML, $xml);
    }

    public function testSerializeFallbackOnExclusionStrategy(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());
        $context = new AdapterSerializationContext();
        $context->addExclusionStrategy($this->createMock(ExclusionStrategyInterface::class));

        $this->liip->expects($this->never())
            ->method('serialize')
        ;

        $this->jms->expects($this->once())
            ->method('serialize')
            ->with($this->model, 'json', $context)
            ->willReturn(self::SERIALIZED_JSON)
        ;

        $xml = $transform->serialize($this->model, 'json', $context);
        $this->assertSame(self::SERIALIZED_JSON, $xml);
    }

    public function testSerializeEnabled(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger(), [TestModel::class]);
        $context = new AdapterSerializationContext();

        $this->liip->expects($this->once())
            ->method('serialize')
            ->with($this->model, 'json')
            ->willReturn(self::SERIALIZED_JSON)
        ;

        $this->jms->expects($this->never())
            ->method('serialize')
        ;

        $json = $transform->serialize($this->model, 'json', $context);
        $this->assertSame(self::SERIALIZED_JSON, $json);
    }

    public function testSerializeNotEnabled(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger(), []);
        $context = new AdapterSerializationContext();

        $this->liip->expects($this->never())
            ->method('serialize')
        ;
        $this->jms->expects($this->once())
            ->method('serialize')
            ->with($this->model, 'json', $context)
            ->willReturn(self::SERIALIZED_JSON)
        ;

        $json = $transform->serialize($this->model, 'json', $context);
        $this->assertSame(self::SERIALIZED_JSON, $json);
    }

    public function testSerializeFallbackOnError(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());

        $context = new AdapterSerializationContext();

        $this->liip->expects($this->once())
            ->method('serialize')
            ->willThrowException(new \Exception())
        ;

        $this->jms->expects($this->once())
            ->method('serialize')
            ->with($this->model, 'json', $context)
            ->willReturn(self::SERIALIZED_JSON)
        ;

        $json = $transform->serialize($this->model, 'json', $context);
        $this->assertSame(self::SERIALIZED_JSON, $json);
    }

    public function testToArray(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());
        $context = new AdapterSerializationContext();

        $this->liip->expects($this->once())
            ->method('toArray')
            ->with($this->model)
            ->willReturn(self::DATA)
        ;
        $this->jms->expects($this->never())
            ->method('toArray')
        ;

        $array = $transform->toArray($this->model, $context);
        $this->assertSame(self::DATA, $array);
    }

    public function testToArrayNotEnabled(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger(), []);
        $context = new AdapterSerializationContext();

        $this->liip->expects($this->never())
            ->method('toArray')
        ;

        $this->jms->expects($this->once())
            ->method('toArray')
            ->with($this->model, $context)
            ->willReturn(self::DATA)
        ;

        $array = $transform->toArray($this->model, $context);
        $this->assertSame(self::DATA, $array);
    }

    public function testToArrayNoContext(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());

        $this->liip->expects($this->once())
            ->method('toArray')
            ->with($this->model)
            ->willReturn(self::DATA)
        ;
        $this->jms->expects($this->never())
            ->method('toArray')
        ;

        $array = $transform->toArray($this->model);
        $this->assertSame(self::DATA, $array);
    }

    public function testDeserialize(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());

        $this->liip->expects($this->once())
            ->method('deserialize')
            ->with(self::SERIALIZED_JSON, self::TYPE, 'json', null)
            ->willReturn($this->model)
        ;

        $this->jms->expects($this->never())
            ->method('deserialize')
        ;

        $object = $transform->deserialize(self::SERIALIZED_JSON, self::TYPE, 'json');
        $this->assertSame($this->model, $object);
    }

    public function testDeserializeFallbackOnFormat(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());

        $this->liip->expects($this->never())
            ->method('deserialize')
        ;
        $this->jms->expects($this->once())
            ->method('deserialize')
            ->with(self::SERIALIZED_XML, self::TYPE, 'xml')
            ->willReturn($this->model)
        ;

        $object = $transform->deserialize(self::SERIALIZED_XML, self::TYPE, 'xml');
        $this->assertSame($this->model, $object);
    }

    public function testDeserializeNotEnabled(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger(), []);

        $this->liip->expects($this->never())
            ->method('deserialize')
        ;

        $this->jms->expects($this->once())
            ->method('deserialize')
            ->with(self::SERIALIZED_JSON, self::TYPE, 'json')
            ->willReturn($this->model)
        ;

        $object = $transform->deserialize(self::SERIALIZED_JSON, self::TYPE, 'json');
        $this->assertSame($this->model, $object);
    }

    public function testDeserializeFallbackOnError(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());
        $context = new DeserializationContext();

        $this->liip->expects($this->once())
            ->method('deserialize')
            ->willThrowException(new \Exception())
        ;

        $this->jms->expects($this->once())
            ->method('deserialize')
            ->with(self::SERIALIZED_JSON, self::TYPE, 'json', $context)
            ->willReturn($this->model)
        ;

        $object = $transform->deserialize(self::SERIALIZED_JSON, self::TYPE, 'json', $context);
        $this->assertSame($this->model, $object);
    }

    public function testFromArrayFallbackOnError(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());
        $context = new DeserializationContext();

        $this->liip->expects($this->once())
            ->method('fromArray')
            ->willThrowException(new \Exception())
        ;
        $this->jms->expects($this->once())
            ->method('fromArray')
            ->with(self::DATA, self::TYPE, $context)
            ->willReturn($this->model)
        ;

        $object = $transform->fromArray(self::DATA, self::TYPE, $context);
        $this->assertSame($this->model, $object);
    }

    public function testFromArrayNotEnabled(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger(), []);
        $context = new DeserializationContext();

        $this->liip->expects($this->never())
            ->method('fromArray')
        ;
        $this->jms->expects($this->once())
            ->method('fromArray')
            ->with(self::DATA, self::TYPE, $context)
            ->willReturn($this->model)
        ;

        $data = $transform->fromArray(self::DATA, self::TYPE, $context);
        $this->assertSame($this->model, $data);
    }

    public function testFromArrayNoContext(): void
    {
        $transform = new JMSSerializerAdapter($this->liip, $this->jms, new NullLogger());

        $this->liip->expects($this->once())
            ->method('fromArray')
            ->with(self::DATA, self::TYPE)
            ->willReturn($this->model)
        ;
        $this->jms->expects($this->never())
            ->method('fromArray')
        ;

        $transformed = $transform->fromArray(self::DATA, self::TYPE);
        $this->assertSame($this->model, $transformed);
    }
}

interface SerializerArrayTransformer extends SerializerInterface, ArrayTransformerInterface
{
}
