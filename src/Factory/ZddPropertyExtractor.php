<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Exceptions\InvalidTypeException;

/**
 * @internal
 */
final class ZddPropertyExtractor
{
    /**
     * @return Property[]
     */
    public function extractProperties(object $object): array
    {
        $reflectionClass = new \ReflectionClass($object);

        $properties = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (null === $reflectionProperty->getType()) {
                continue;
            }

            if ($reflectionProperty->getType() instanceof \ReflectionIntersectionType) {
                throw InvalidTypeException::typeNotSupported();
            }

            $value = $reflectionProperty->getValue($object);
            $properties[] = new Property(
                $reflectionProperty->getName(),
                is_object($value) ? $value::class : gettype($value),
                is_object($value) ? $this->extractProperties($value) : [],
            );
        }

        return $properties;
    }
}
