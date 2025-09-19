<?php

use DaggerModule\Enum\DependencyVersionEnum;
use DaggerModule\Enum\MinimumStabilityEnum;

$matrix = [];

foreach (['8.1', '8.2', '8.3', '8.4'] as $phpVersion) {
    $matrix["php-{$phpVersion}"] = [
        'php-version' => $phpVersion,
        'symfony-version' => '>=5.4',
        'minimum-stability' => MinimumStabilityEnum::STABLE->value,
        'dependency-version' => DependencyVersionEnum::HIGHEST->value,
    ];
}

return [
    ...$matrix,
    // dev packages (probably not needed to have multiple such jobs)
    'dev' => [
        'minimum-stability' => 'dev',
        'php-version' => '8.4',
    ],
    // lowest deps
    'lowest' => [
        'php-version' => '8.1',
        'dependency-version' => 'lowest',
    ],
    // LTS version of Symfony
    'lts' => [
        'php-version' => '8.1',
        'symfony-version' => '6.4.*',
    ],
    // Stable
    'stable' => [
        'php-version' => '8.2',
        'symfony-version' => '7.3.*',
    ],
    // Explicit Symfony versions
    'sf-5.4' => [
        'php-version' => '8.1',
        'symfony-version' => '5.4.*',
    ],
    'sf-6.2' => [
        'php-version' => '8.1',
        'symfony-version' => '6.2.*',
    ],
    'sf-7.0' => [
        'php-version' => '8.2',
        'symfony-version' => '7.0.*',
    ],
    'sf-8.0' => [
        'php-version' => '8.4',
        'symfony-version' => '8.0.x-dev',
        'minimum-stability' => 'dev',
    ],
];
