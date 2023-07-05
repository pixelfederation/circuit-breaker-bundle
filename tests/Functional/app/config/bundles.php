<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use PixelFederation\CircuitBreakerBundle\Bridge\Symfony\PixelFederationCircuitBreakerBundle;

return [
    new FrameworkBundle(),
    new MonologBundle(),
    new PixelFederationCircuitBreakerBundle(),
];
