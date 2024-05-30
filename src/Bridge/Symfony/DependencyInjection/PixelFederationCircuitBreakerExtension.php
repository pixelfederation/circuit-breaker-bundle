<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection;

use Attribute;
use Doctrine\Common\Annotations\AnnotationReader;
use PixelFederation\CircuitBreakerBundle\AnnotationMetadataReader;
use PixelFederation\CircuitBreakerBundle\AttributeMetadataReader;
use PixelFederation\CircuitBreakerBundle\CircuitBrokenService;
use PixelFederation\CircuitBreakerBundle\FallbackableMetadataReader;
use PixelFederation\CircuitBreakerBundle\MetadataReader;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class PixelFederationCircuitBreakerExtension extends ConfigurableExtension
{
    /**
     * @param array<array-key, mixed> $mergedConfig @codingStandardsIgnoreLine
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../../config'));
        $loader->load('services.php');
        $this->configureMetadataReader($container);
        $this->autoConfigureCircuitBreakers($container);
    }

    //phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh,SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
    private function configureMetadataReader(ContainerBuilder $container): void
    {
        $supportedReaders = [
            AnnotationMetadataReader::class => AnnotationMetadataReader::class,
        ];

        if (!class_exists(AnnotationReader::class)) {
            $container->removeDefinition(AnnotationMetadataReader::class);
            unset($supportedReaders[AnnotationMetadataReader::class]);
        }

        if (class_exists(Attribute::class)) {
            $supportedReaders[AttributeMetadataReader::class] = AttributeMetadataReader::class;
        }

        if (count($supportedReaders) === 0) {
            throw new RuntimeException('No metadata reader available');
        }

        if (count($supportedReaders) === 1) {
            $readerClass = array_pop($supportedReaders);

            if ($readerClass === AnnotationMetadataReader::class) {
                return;
            }

            $attributeReaderDef = new Definition(AttributeMetadataReader::class);
            $container->setDefinition(AttributeMetadataReader::class, $attributeReaderDef);
            $container->setAlias(MetadataReader::class, AttributeMetadataReader::class);

            return;
        }

        $attributeReaderDef = new Definition(AttributeMetadataReader::class);
        $container->setDefinition(AttributeMetadataReader::class, $attributeReaderDef);
        $fallbackableReaderDef = new Definition(FallbackableMetadataReader::class);
        $fallbackableReaderDef->setArgument('$attributeReader', new Reference(AttributeMetadataReader::class));
        $fallbackableReaderDef->setArgument('$annotationReader', new Reference(AnnotationMetadataReader::class));
        $container->setDefinition(FallbackableMetadataReader::class, $fallbackableReaderDef);
        $container->setAlias(MetadataReader::class, FallbackableMetadataReader::class);
    }

    private function autoConfigureCircuitBreakers(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(CircuitBrokenService::class)
            ->addTag('pixel_federation_circuit_breaker.circuit_broken_service');
    }
}
