<?php

declare(strict_types=1);

namespace DaggerModule\Enum;

enum DependencyVersionEnum: string
{
    case LOCKED = 'locked';
    case HIGHEST = 'highest';
    case LOWEST = 'lowest';
}
