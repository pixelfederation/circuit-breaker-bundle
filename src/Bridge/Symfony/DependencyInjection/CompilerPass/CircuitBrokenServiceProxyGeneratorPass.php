<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 * @author Juraj Surman <jsurman@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\DependencyInjection\CompilerPass;

use PixelFederation\CircuitBreakerBundle\Instantiator;
use PixelFederation\CircuitBreakerBundle\ReflectionMethodExtractor;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use UnexpectedValueException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class CircuitBrokenServiceProxyGeneratorPass implements CompilerPassInterface
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws ServiceNotFoundException
     * @throws BadMethodCallException
     */
    public function process(ContainerBuilder $container): void
    {
        /** @var array<string> $serviceDefIds */
        $serviceDefIds = array_keys(
            $container->findTaggedServiceIds('pixel_federation_circuit_breaker.circuit_broken_service')
        );

        if (count($serviceDefIds) === 0) {
            return;
        }

        $serviceClasses = [];

        foreach ($serviceDefIds as $serviceDefId) {
            $serviceDef = $container->findDefinition($serviceDefId);
            $serviceDef->setLazy(false); // turn off lazy - becase this will be lazy loaded automaticaly
            $proxySvcId = sprintf('%s.circuit_broken_service_proxy', $serviceDefId);
            $innerSvcId = sprintf('%s.inner', $proxySvcId);
            $proxyDef = new Definition($serviceDef->getClass());
            $this->validateProxyDefinition($serviceDefId, $proxyDef);
            $proxyDef->setDecoratedService($serviceDefId, $innerSvcId);
            $proxyDef->setFactory([new Reference(Instantiator::class), 'newInstance']);
            $proxyDef->addArgument(new Reference($innerSvcId));
            $container->setDefinition($proxySvcId, $proxyDef);
            $serviceClasses[] = $serviceDef->getClass();
        }

        $methodExtractorDef = $container->findDefinition(ReflectionMethodExtractor::class);
        $methodExtractorDef->setArgument('$serviceClasses', $serviceClasses);
    }

    /**
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function validateProxyDefinition(string $serviceDefId, Definition $definition): void
    {
        /** @var class-string|null $className */
        $className = $definition->getClass();

        if ($className === null) {
            throw new UnexpectedValueException(
                sprintf('Missing class name for definition %s.', $serviceDefId)
            );
        }

        $reflectionClass = new ReflectionClass($className);
        if ($reflectionClass->isFinal()) {
            throw new RuntimeException(
                sprintf(
                    'Unable to create circuit broken proxy for class "%s". '
                     . 'Please remove the "final" keyword from class',
                    $className
                )
            );
        }
    }
}
