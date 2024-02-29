<?php

namespace Yousign\ZddMessageBundle\Factory;

use Yousign\ZddMessageBundle\Exceptions\InvalidTypeException;

/**
 * @internal
 */
final class ZddPropertyExtractor
{
    /**
     * @return Property[]
     *
     * @throws InvalidTypeException
     */
    public function extractProperties(object $object): array
    {
        $reflectionClass = new \ReflectionClass($object);

        $properties = [];

        foreach ($reflectionClass->getProperties() as $property) {
            if (null === $property->getType()) {
                continue;
            }

            if (!$property->getType() instanceof \ReflectionNamedType) {
                throw InvalidTypeException::typeNotSupported();
            }

            if (!$property->getType()->isBuiltin()) {
                $value = $property->getValue($object);
                if (!is_object($value)) {
                    continue;
                }

                $children = $this->extractProperties($value);
            }

            $properties[] = new Property($property->getName(), $property->getType()->getName(), $children ?? []);
        }

        return $properties;
    }
}
