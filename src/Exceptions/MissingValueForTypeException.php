<?php

namespace Youtrust\ZddMessageBundle\Exceptions;

use Youtrust\ZddMessageBundle\Config\ZddMessageConfigInterface;

class MissingValueForTypeException extends \Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function missingValue(string $type, ZddMessageConfigInterface $class): self
    {
        return new self(sprintf('Missing value for property type "%s" maybe you forgot to add it in "%s"', $type, get_class($class)));
    }
}
