<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Serializer;

class UnableToDeserializeException extends \Exception
{
    public function __construct(\Throwable $previous)
    {
        parent::__construct('Unable to deserialize message', 0, $previous);
    }
}
