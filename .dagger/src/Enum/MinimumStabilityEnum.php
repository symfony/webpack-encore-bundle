<?php

declare(strict_types=1);

namespace DaggerModule\Enum;

enum MinimumStabilityEnum: string
{
    case DEV = 'dev';
    case ALPHA = 'alpha';
    case BETA = 'beta';
    case RC = 'RC';
    case STABLE = 'stable';
}
