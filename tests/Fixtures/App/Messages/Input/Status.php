<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\Input;

enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}
