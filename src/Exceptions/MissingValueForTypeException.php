<?php

namespace Yousign\ZddMessageBundle\Exceptions;

use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

class MissingValueForTypeException extends \TypeError
{
    private function __construct(string $message, int $code, null|\Throwable $previous)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function missingValue(string $type, ZddMessageConfigInterface $class, int $code, null|\Throwable $previous): self
    {
        return new self(sprintf('Missing value for property type "%s" maybe you forgot to add it in "%s"', $type, get_class($class)), $code, $previous);
    }
}
