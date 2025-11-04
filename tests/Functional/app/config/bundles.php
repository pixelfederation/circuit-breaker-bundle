<?php

declare(strict_types=1);

use PixelFederation\CircuitBreakerBundle\Bridge\Symfony\PixelFederationCircuitBreakerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;

return [
    new FrameworkBundle(),
    new MonologBundle(),
    new PixelFederationCircuitBreakerBundle(),
];
