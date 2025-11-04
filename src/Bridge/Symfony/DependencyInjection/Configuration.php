<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    private const string CONFIGURATION_ROOT_NODE = 'pixel_federation_circuit_breaker';

    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder(self::CONFIGURATION_ROOT_NODE);
    }
}
