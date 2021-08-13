<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

return [
    new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
    new Symfony\Bundle\MonologBundle\MonologBundle(),
    new PixelFederation\CircuitBreakerBundle\Bridge\Symfony\PixelFederationCircuitBreakerBundle(),
];
