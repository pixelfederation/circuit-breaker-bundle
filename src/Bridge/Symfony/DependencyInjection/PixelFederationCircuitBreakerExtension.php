<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection;

use PixelFederation\CircuitBreakerBundle\CircuitBrokenService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class PixelFederationCircuitBreakerExtension extends ConfigurableExtension
{
    /**
     * @param array<array-key, mixed> $mergedConfig @codingStandardsIgnoreLine
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        /** @psalm-suppress ReservedWord */
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(CircuitBrokenService::class)
            ->addTag('pixel_federation_circuit_breaker.circuit_broken_service');
    }
}
