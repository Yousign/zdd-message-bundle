<?php

namespace Yousign\ZddMessageBundle\Tests\Unit;

use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer;

trait SerializerTrait
{
    public function getSerializer(): ZddMessageMessengerSerializer
    {
        return new ZddMessageMessengerSerializer(new PhpSerializer());
    }
}
