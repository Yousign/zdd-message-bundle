<?php

namespace Yousign\ZddMessageBundle\Config;

/**
 * @experimental
 */
interface CustomMessageGeneratorInterface
{
    /**
     * If you need full control over how a specific message instance is created,
     * use this method to return a fully instantiated message object.
     * This is useful when the default instantiation (using reflection and property injection)
     * is not sufficient or when your message requires specific constructor logic.
     *
     * WARNING: The object must be instantiated with minimum requirements (i.e., nullable properties
     * must be set as null) in order to ensure a good ZDD test.
     *
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return ?T The fully instantiated message object, or null if the default instantiation should be used
     */
    public function generateCustomMessage(string $className): ?object;
}
