<?php

declare(strict_types=1);

namespace Liip\Serializer\Adapter\JMS;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Liip\Serializer\Context;
use Liip\Serializer\SerializerInterface as LiipSerializer;
use Psr\Log\LoggerInterface;

/**
 * Bridge to use the LiipSerializer as a drop-in replacement for JMS
 * Serializer, with fallback to JMS on error.
 *
 * If the liip serializer can not handle the operation, we fall back to the
 * regular JMS serializer.
 */
class JMSSerializerAdapter implements SerializerInterface, ArrayTransformerInterface
{
    /**
     * A hashmap with FQN classnames that should be handled with the Liip
     * Serializer or null to always try to use the Liip Serializer.
     *
     * @var bool[]|null
     */
    private $enabledClasses;

    /**
     * The original serializer *MUST* implement both SerializerInterface and ArrayTransformerInterface interfaces
     *
     * @var SerializerInterface&ArrayTransformerInterface Fallback for when the type or format is not supported
     */
    private $originalSerializer;

    /**
     * @var LiipSerializer
     */
    private $liipSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SerializerInterface&ArrayTransformerInterface $originalSerializer must implement both SerializerInterface and ArrayTransformerInterface interfaces
     * @param string[]|null                                 $enabledClasses     list of fully qualified class names for which to use the Liip Serializer.
     *                                                                          Null tells to attempt to use the Liip Serializer with all classes.
     */
    public function __construct(
        LiipSerializer $liipSerializer,
        object $originalSerializer,
        LoggerInterface $logger,
        ?array $enabledClasses = null
    ) {
        $this->liipSerializer = $liipSerializer;
        if (!$originalSerializer instanceof SerializerInterface
            || !$originalSerializer instanceof ArrayTransformerInterface
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Original serializer must implement both ArrayTransformerInterface and SerializerInterface, but is %s',
                \get_class($originalSerializer)
            ));
        }
        $this->originalSerializer = $originalSerializer;
        $this->logger = $logger;
        if (null === $enabledClasses) {
            $this->enabledClasses = null;
        } else {
            $map = array_combine($enabledClasses, array_fill(0, \count($enabledClasses), true));
            \assert(\is_array($map));
            $this->enabledClasses = $map;
        }
    }

    public function serialize($data, string $format, ?SerializationContext $context = null, ?string $type = null): string
    {
        if ('json' === $format && $this->useLiipSerializer($data, $context)) {
            try {
                return $this->liipSerializer->serialize($data, $format, $this->createLiipContext($context));
            } catch (\Throwable $t) {
                $this->logger->warning('Liip Serializer failed to serialize {type}, falling back to JMS', [
                    'class' => \is_object($data) ? \get_class($data) : \gettype($data),
                    'exception' => $t,
                ]);
            }
        }

        return $this->originalSerializer->serialize($data, $format, $context, $type);
    }

    public function deserialize(string $data, string $type, string $format, ?DeserializationContext $context = null)
    {
        if ('json' === $format && $this->useLiipDeserializer($type, $context)) {
            try {
                return $this->liipSerializer->deserialize($data, $type, $format, $this->createLiipContext($context));
            } catch (\Throwable $t) {
                $this->logger->warning('Liip Serializer failed to deserialize to type {type}, falling back to JMS', [
                    'type' => $type,
                    'exception' => $t,
                ]);
            }
        }

        return $this->originalSerializer->deserialize($data, $type, $format, $context);
    }

    public function toArray($data, ?SerializationContext $context = null, ?string $type = null): array
    {
        if ($this->useLiipSerializer($data, $context)) {
            try {
                return $this->liipSerializer->toArray($data, $this->createLiipContext($context));
            } catch (\Throwable $t) {
                $this->logger->warning('Liip Serializer failed to convert {type} to array, falling back to JMS', [
                    'type' => \is_object($data) ? \get_class($data) : \gettype($data),
                    'exception' => $t,
                ]);
            }
        }

        return $this->originalSerializer->toArray($data, $context, $type);
    }

    public function fromArray(array $data, string $type, ?DeserializationContext $context = null)
    {
        if ($this->useLiipDeserializer($type, $context)) {
            try {
                return $this->liipSerializer->fromArray($data, $type, $this->createLiipContext($context));
            } catch (\Throwable $t) {
                $this->logger->warning('Liip Serializer failed to create {type} from array, falling back to JMS', [
                    'type' => $type,
                    'exception' => $t,
                ]);
            }
        }

        return $this->originalSerializer->fromArray($data, $type, $context);
    }

    private function createLiipContext(?JMSContext $context): ?Context
    {
        if (null === $context) {
            return null;
        }

        $liipContext = new Context();
        if ($context->hasAttribute('groups')) {
            $liipContext->setGroups($context->getAttribute('groups'));
        }
        if ($context->hasAttribute('version')) {
            $liipContext->setVersion($context->getAttribute('version'));
        }

        return $liipContext;
    }

    private function useLiipSerializer($data, ?SerializationContext $context): bool
    {
        if (!\is_object($data)) {
            // $data can be anything, not only an object. a feature complete serializer should also handle string (trivial) and arrays (loop over the elements)
            // we can ignore this for now because product lists are ProductCollection objects, never plain PHP arrays

            return false;
        }
        if (null !== $this->enabledClasses && !\array_key_exists(\get_class($data), $this->enabledClasses)) {
            return false;
        }
        if (null !== $context) {
            if (!$context instanceof AdapterSerializationContext) {
                throw new \InvalidArgumentException(sprintf(
                    'Serialization context for %s needs to be an instance of %s, %s given',
                    __CLASS__,
                    AdapterSerializationContext::class,
                    \get_class($context)
                ));
            }

            if ($context->hasCustomExclusionStrategy()) {
                // Custom exclusion strategies are not supported
                return false;
            }
        }

        return true;
    }

    private function useLiipDeserializer(string $type, ?DeserializationContext $context): bool
    {
        if (null !== $this->enabledClasses && !\array_key_exists($type, $this->enabledClasses)) {
            return false;
        }
        if (!$context) {
            return true;
        }
        if ($context->hasAttribute('version')) {
            return false;
        }
        if ($context->hasAttribute('groups') && \count($context->getAttribute('groups'))) {
            return false;
        }

        return true;
    }
}
