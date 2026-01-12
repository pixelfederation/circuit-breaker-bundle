<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection;

use Attribute;
use Override;
use PixelFederation\CircuitBreakerBundle\CircuitBrokenService;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class PixelFederationCircuitBreakerExtension extends ConfigurableExtension
{
    /**
     * @param array<array-key, mixed> $mergedConfig @codingStandardsIgnoreLine
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    #[Override]
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../../config'));
        $loader->load('services.php');
        $this->configureMetadataReader();
        $this->autoConfigureCircuitBreakers($container);
    }

    private function configureMetadataReader(): void
    {
        if (!class_exists(Attribute::class)) {
            throw new RuntimeException('No metadata reader available');
        }
    }

    private function autoConfigureCircuitBreakers(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(CircuitBrokenService::class)
            ->addTag('pixel_federation_circuit_breaker.circuit_broken_service');
    }
}
