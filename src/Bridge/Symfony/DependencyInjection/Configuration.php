<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    private const CONFIGURATION_ROOT_NODE = 'pixel_federation_circuit_breaker';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder(self::CONFIGURATION_ROOT_NODE);
    }
}
