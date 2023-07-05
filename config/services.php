<?php

declare(strict_types=1);

use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Storage\Adapter\Apcu;
use Ackintosh\Ganesha\Storage\Adapter\ApcuStore;
use Ackintosh\Ganesha\Strategy\Rate\Builder;
use Doctrine\Common\Annotations\Reader;
use PixelFederation\CircuitBreakerBundle\Bridge\Ganesha\GaneshaCircuitBreaker;
use PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command\GenerateCircuitBrokenProxiesCacheClearer;
use PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command\GenerateCircuitBrokenProxiesCacheWarmer;
use PixelFederation\CircuitBreakerBundle\CachedMethodExtractor;
use PixelFederation\CircuitBreakerBundle\CircuitBreaker;
use PixelFederation\CircuitBreakerBundle\Generator;
use PixelFederation\CircuitBreakerBundle\Instantiator;
use PixelFederation\CircuitBreakerBundle\MethodExtractor;
use PixelFederation\CircuitBreakerBundle\ReflectionMethodExtractor;
use ProxyManager\Configuration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        'pixel_federation_circuit_breaker.proxy_cache_dir',
        '%kernel.cache_dir%/pixelfederation/cicruit_breaker'
    );

    $parameters->set('pixel_federation_circuit_breaker.cache_namespace', 'pf_cicruit_breaker');

    $services = $containerConfigurator->services();

    $services->set('pixel_federation_circuit_breaker.array_cache_item_pool', ArrayAdapter::class);

    $services->set('pixel_federation_circuit_breaker.circuit_breaker.cache_item_pool', FilesystemAdapter::class)
        ->arg('$namespace', '%pixel_federation_circuit_breaker.cache_namespace%')
        ->arg('$defaultLifetime', 0)
        ->arg('$directory', '%pixel_federation_circuit_breaker.proxy_cache_dir%');

    $services->set('pixel_federation_circuit_breaker.circuit_breaker.cache', ChainAdapter::class)
        ->arg('$adapters', [
        service('pixel_federation_circuit_breaker.array_cache_item_pool'),
        service('pixel_federation_circuit_breaker.circuit_breaker.cache_item_pool'),
    ]);

    $services->alias(MethodExtractor::class, ReflectionMethodExtractor::class);

    $services->set(CachedMethodExtractor::class)
        ->decorate(MethodExtractor::class)
        ->arg('$decorated', service('PixelFederation\CircuitBreakerBundle\CachedMethodExtractor.inner'))
        ->arg('$cache', service('pixel_federation_circuit_breaker.circuit_breaker.cache'));

    $services->set(ReflectionMethodExtractor::class)
        ->arg('$annotationsReader', service(Reader::class))
        ->arg('$serviceClasses', [
    ]);

    $services->set(GenerateCircuitBrokenProxiesCacheClearer::class)
        ->arg('$cache', service('pixel_federation_circuit_breaker.circuit_breaker.cache'))
        ->arg('$cacheDirectory', '%pixel_federation_circuit_breaker.proxy_cache_dir%')
        ->arg('$filesystem', inline_service(Filesystem::class))
        ->tag('kernel.cache_clearer');

    $services->set(GenerateCircuitBrokenProxiesCacheWarmer::class)
        ->arg('$proxyGenerator', service(Generator::class))
        ->tag('kernel.cache_warmer');

    $services->set('pixel_federation_circuit_breaker.circuit_breaker.service_proxy_configuration', Configuration::class)
        ->call('setGeneratorStrategy', [
        service('pixel_federation_circuit_breaker.circuit_breaker.proxy_file_writer_generator'),
    ])
        ->call('setProxiesTargetDir', [
        '%pixel_federation_circuit_breaker.proxy_cache_dir%',
    ]);

    $services->set(
        'pixel_federation_circuit_breaker.circuit_breaker.proxy_file_writer_generator',
        FileWriterGeneratorStrategy::class
    )
        ->arg('$fileLocator', service('pixel_federation_circuit_breaker.circuit_breaker.proxy_file_locator'));

    $services->set('pixel_federation_circuit_breaker.circuit_breaker.proxy_file_locator', FileLocator::class)
        ->lazy(true)
        ->arg('$proxiesDirectory', '%pixel_federation_circuit_breaker.proxy_cache_dir%');

    $services->set(Generator::class)
        ->arg('$configuration', service('pixel_federation_circuit_breaker.circuit_breaker.service_proxy_configuration'))
        ->arg('$methodExtractor', service(MethodExtractor::class));

    $services->set(Instantiator::class)
        ->arg('$methodExtractor', service(MethodExtractor::class))
        ->arg('$proxyGenerator', service(Generator::class))
        ->arg('$circuitBreaker', service(CircuitBreaker::class));

    $services->alias(CircuitBreaker::class, GaneshaCircuitBreaker::class);

    $services->set(GaneshaCircuitBreaker::class)
        ->arg('$ganesha', service('pixel_federation_circuit_breaker.ganesha'));

    $services->set('pixel_federation_circuit_breaker.ganesha_adapter', Apcu::class)
        ->arg('$apcuStore', service('pixel_federation_circuit_breaker.ganesha_adapter_store'));

    $services->set('pixel_federation_circuit_breaker.ganesha_adapter_store', ApcuStore::class);

    $services->set('pixel_federation_circuit_breaker.ganesha', Ganesha::class)
        ->lazy(true)
        ->factory([
        service('pixel_federation_circuit_breaker.ganesha_builder'),
        'build',
    ]);

    $services->set('pixel_federation_circuit_breaker.ganesha_builder', Builder::class)
        ->factory([Ganesha\Builder::class, 'withRateStrategy'])
        ->call('adapter', [service('pixel_federation_circuit_breaker.ganesha_adapter'),])
        ->call('failureRateThreshold', [25])
        ->call('intervalToHalfOpen', [5])
        ->call('minimumRequests', [5])
        ->call('timeWindow', [10]);
};
