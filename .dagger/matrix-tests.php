<?php

declare(strict_types=1);

use DaggerModule\Enum\DependencyVersionEnum;
use DaggerModule\Enum\MinimumStabilityEnum;

$matrix = [];

foreach (['8.1', '8.2', '8.3', '8.4'] as $phpVersion) {
    $matrix[] = [
        'php-version' => $phpVersion,
        'symfony-version' => '>=5.4',
        'minimum-stability' => MinimumStabilityEnum::STABLE->value,
        'dependency-version' => DependencyVersionEnum::HIGHEST->value,
    ];
}

$matrix = [
    ...$matrix,
    // dev packages (probably not needed to have multiple such jobs)
    [
        'minimum-stability' => 'dev',
        'php-version' => '8.4',
    ],
    // lowest deps
    [
        'php-version' => '8.1',
        'dependency-version' => 'lowest',
    ],
    // LTS version of Symfony
    [
        'php-version' => '8.1',
        'symfony-version' => '6.4.*',
    ],
    // Stable
    [
        'php-version' => '8.2',
        'symfony-version' => '7.3.*',
    ],
    // Explicit Symfony versions
    [
        'php-version' => '8.1',
        'symfony-version' => '5.4.*',
    ],
    [
        'php-version' => '8.1',
        'symfony-version' => '6.2.*',
    ],
    [
        'php-version' => '8.2',
        'symfony-version' => '7.0.*',
    ],
    [
        'php-version' => '8.4',
        'symfony-version' => '8.0.x-dev',
        'minimum-stability' => 'dev',
    ],
];

$default = [
    'php-version' => '8.1',
    'symfony-version' => '>=5.4',
    'minimum-stability' => MinimumStabilityEnum::STABLE->value,
    'dependency-version' => DependencyVersionEnum::LOCKED->value,
];

return array_map(fn (array $job) => array_merge($default, $job), $matrix);
