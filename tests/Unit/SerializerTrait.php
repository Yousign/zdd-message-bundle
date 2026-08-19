<?php

namespace Youtrust\ZddMessageBundle\Tests\Unit;

use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Youtrust\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;

trait SerializerTrait
{
    public function getSerializer(): ZddMessageMessengerSerializer
    {
        return new ZddMessageMessengerSerializer(new PhpSerializer());
    }
}
