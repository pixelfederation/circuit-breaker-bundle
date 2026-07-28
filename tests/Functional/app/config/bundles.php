<?php

declare(strict_types=1);

return [
    \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    \Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    \PixelFederation\CircuitBreakerBundle\Bridge\Symfony\PixelFederationCircuitBreakerBundle::class => ['all' => true],
];
