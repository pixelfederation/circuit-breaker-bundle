<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker as CircuitBreakerMetadata;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

final class AttributeMetadataReader implements MetadataReader
{
    /**
     * @param ReflectionClass<object> $serviceClass
     */
    public function getServiceMetadata(ReflectionClass $serviceClass): ?CircuitBreakerService
    {
        $reflAttributes = $serviceClass->getAttributes(CircuitBreakerService::class);
        $attributesCount = count($reflAttributes);

        if ($attributesCount === 0) {
            return null;
        }

        if ($attributesCount > 1) {
            throw new RuntimeException(
                'Multiple CircuitBreakerService annotations found on class ' . $serviceClass->getName(),
            );
        }

        return $reflAttributes[0]->newInstance();
    }

    public function getMethodMetadata(ReflectionMethod $method): ?CircuitBreakerMetadata
    {
        $reflAttributes = $method->getAttributes(CircuitBreakerMetadata::class);
        $attributesCount = count($reflAttributes);

        if ($attributesCount === 0) {
            return null;
        }

        if ($attributesCount > 1) {
            throw new RuntimeException(
                'Multiple CircuitBreaker annotations found on method ' . $method->getName(),
            );
        }

        return $reflAttributes[0]->newInstance();
    }
}
