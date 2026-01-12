<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker as CircuitBreakerMetadata;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use ReflectionClass;
use ReflectionMethod;

interface MetadataReader
{
    /**
     * @param ReflectionClass<CircuitBrokenService> $serviceClass
     */
    public function getServiceMetadata(ReflectionClass $serviceClass): ?CircuitBreakerService;

    public function getMethodMetadata(ReflectionMethod $method): ?CircuitBreakerMetadata;
}
