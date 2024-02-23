<?php

namespace Yousign\ZddMessageBundle\Exceptions;

class InvalidTypeException extends \Exception
{
    private const NOT_SUPPORTED_TYPE = [
        \ReflectionUnionType::class,
        \ReflectionIntersectionType::class,
    ];

    private function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function typeMissing(string $propertyName, string $className): self
    {
        return new self(\sprintf('Please, add type hint on property. Property "%s" of class "%s', $propertyName, $className));
    }

    public static function typeNotSupported(): self
    {
        return new self(\sprintf('We don\'t manage types (%s) for the moment because we don\'t use it.', implode(', ', self::NOT_SUPPORTED_TYPE)));
    }
}
