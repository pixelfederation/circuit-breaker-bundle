<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Assert\Assert;
use InvalidArgumentException;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class ReflectionMethodExtractor implements MethodExtractor
{
    /**
     * @param iterable<class-string<CircuitBrokenService>> $serviceClasses
     */
    public function __construct(
        private readonly MetadataReader $reader,
        private readonly iterable $serviceClasses,
    ) {
    }

    /**
     * @param class-string<CircuitBrokenService> $serviceClass
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function extractFor(string $serviceClass): ServiceMethods
    {
        Assert::that($serviceClass)->implementsInterface(CircuitBrokenService::class);
        $reflClass = new ReflectionClass($serviceClass);

        $configuration = $this->createConfiguration($reflClass);
        $methods = $this->extractServiceMethods($reflClass, $configuration);

        return new ServiceMethods($serviceClass, $configuration, ...$methods);
    }

    public function extractAll(): ServicesMethods
    {
        $servicesMethods = new ServicesMethods();

        foreach ($this->serviceClasses as $serviceClass) {
            $methods = $this->extractFor($serviceClass);
            $servicesMethods->add($methods);
        }

        return $servicesMethods;
    }

    /**
     * @param ReflectionClass<CircuitBrokenService> $reflectionClass
     * @return array<ServiceMethod>
     * @throws InvalidArgumentException
     */
    private function extractServiceMethods(
        ReflectionClass $reflectionClass,
        CircuitBreakerConfiguration $configuration,
    ): array {
        $publicMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $serviceMethods = [];
        foreach ($publicMethods as $method) {
            $methodAnnotation = $this->reader->getMethodMetadata($method);

            if ($methodAnnotation === null) {
                continue;
            }

            $fallbackMethod = $this->extractFallbackMethod(
                $reflectionClass,
                $method,
                $methodAnnotation,
                $configuration
            );

            $serviceMethods[] = new ServiceMethod(
                $method->getName(),
                $fallbackMethod,
                $methodAnnotation->getIgnoreExceptions()
            );
        }

        $this->validateRecursiveFallbackCalls($reflectionClass, ...$serviceMethods);

        return $serviceMethods;
    }

    /**
     * @param ReflectionClass<CircuitBrokenService> $invokerReflClass
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function extractFallbackMethod(
        ReflectionClass $invokerReflClass,
        ReflectionMethod $invokerReflMethod,
        CircuitBreaker $methodAnnotation,
        CircuitBreakerConfiguration $configuration,
    ): ?string {
        $fallbackMethod = $methodAnnotation->getFallbackMethod();

        if ($fallbackMethod === null) {
            return $configuration->getDefaultFallback();
        }

        $invokerMethod = $invokerReflMethod->getName();

        if (!$invokerReflClass->hasMethod($fallbackMethod)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Fallback method %s::%s not exists. (Fallback for %s)',
                    $invokerReflClass->getName(),
                    $fallbackMethod,
                    $invokerMethod
                )
            );
        }

        $fallbackReflMethod = $invokerReflClass->getMethod($fallbackMethod);
        if (!$fallbackReflMethod->isPublic()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Fallback method %s::%s needs to be public. (Fallback for %s)',
                    $invokerReflClass->getName(),
                    $fallbackMethod,
                    $invokerMethod
                )
            );
        }

        $invokerParameters = $invokerReflMethod->getParameters();
        $fallbackParameters = $fallbackReflMethod->getParameters();
        if (count($invokerParameters) !== count($fallbackParameters)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Fallback method %s::%s requires same count of input parameters. '
                    . 'Expected %s, got %s. (Fallback for %s)',
                    $invokerReflClass->getName(),
                    $fallbackMethod,
                    count($invokerParameters),
                    count($fallbackParameters),
                    $invokerMethod
                )
            );
        }

        foreach ($invokerParameters as $index => $invokerParameter) {
            $fallbackParameter = $fallbackParameters[$index];

            if (!$invokerParameter->hasType() && !$fallbackParameter->hasType()) {
                continue;
            }

            $invokerParameterType = $invokerParameter->getType();
            $invokerParameterTypeName = $invokerParameterType instanceof ReflectionNamedType ?
                $invokerParameterType->getName() : 'unknown';

            if ($invokerParameter->hasType() && !$fallbackParameter->hasType()) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Missing type "%s" for parameter "%s" in fallback method %s::%s. (Fallback for %s)',
                        $invokerParameterTypeName,
                        $fallbackParameter->getName(),
                        $invokerReflClass->getName(),
                        $fallbackMethod,
                        $invokerMethod
                    )
                );
            }

            $fallbackParameterType = $fallbackParameter->getType();
            $fallbackParameterTypeName = $fallbackParameterType instanceof ReflectionNamedType ?
                $fallbackParameterType->getName() : 'unknown';

            if ($invokerParameterTypeName !== $fallbackParameterTypeName) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Parameter "%s" in fallback method %s::%s does not match. '
                        . 'Expected %s, got %s. (Fallback for %s)',
                        $fallbackParameter->getName(),
                        $invokerReflClass->getName(),
                        $fallbackMethod,
                        $invokerParameterTypeName,
                        $fallbackParameterTypeName,
                        $invokerMethod
                    )
                );
            }
        }

        return $fallbackMethod;
    }

    /**
     * @param ReflectionClass<CircuitBrokenService> $reflectionClass
     * @throws InvalidArgumentException
     */
    private function validateRecursiveFallbackCalls(
        ReflectionClass $reflectionClass,
        ServiceMethod ...$serviceMethods,
    ): void {
        $methods = [];
        foreach ($serviceMethods as $serviceMethod) {
            $methods[$serviceMethod->getName()] = $serviceMethod->hasFallback() ? $serviceMethod->getFallback() : null;
        }

        foreach ($methods as $method => $fallbackMethod) {
            while ($fallbackMethod) {
                if ($fallbackMethod === $method) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Circular reference of fallback detected in %s::%s',
                            $reflectionClass->getName(),
                            $method
                        )
                    );
                }
                $fallbackMethod = $methods[$fallbackMethod] ?? null;
            }
        }
    }

    /**
     * @param ReflectionClass<CircuitBrokenService> $serviceClass
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function createConfiguration(ReflectionClass $serviceClass): CircuitBreakerConfiguration
    {
        $classAnnotation = $this->reader->getServiceMetadata($serviceClass);

        if ($classAnnotation !== null) {
            return CircuitBreakerConfiguration::fromAnnotation($serviceClass->getName(), $classAnnotation);
        }

        return CircuitBreakerConfiguration::fromServiceName($serviceClass->getName());
    }
}
