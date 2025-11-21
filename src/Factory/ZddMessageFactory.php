<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Serializer\SerializerInterface;

/**
 * @internal
 */
final class ZddMessageFactory
{
    private ZddPropertyExtractor $propertyExtractor;
    private MessageGenerator $messageGenerator;

    public function __construct(ZddMessageConfigInterface $config, private readonly SerializerInterface $serializer)
    {
        $this->propertyExtractor = new ZddPropertyExtractor();
        $this->messageGenerator = new MessageGenerator($config);
    }

    /**
     * @param class-string $className
     */
    public function create(string $className): ZddMessage
    {
        try {
            $propertyList = $this->propertyExtractor->extractPropertiesFromClass($className);

            $message = $this->messageGenerator->generate($className);

            $serializedMessage = $this->serializer->serialize($message);
        } catch (\Throwable $e) {
            throw new \LogicException('Unable to create ZddMessage for class "'.$className.'"', previous: $e);
        }

        return new ZddMessage($className, $serializedMessage, $propertyList, $message);
    }
}
