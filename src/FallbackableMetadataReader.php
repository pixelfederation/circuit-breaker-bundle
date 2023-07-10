<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker as CircuitBreakerMetadata;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use ReflectionClass;
use ReflectionMethod;

final class FallbackableMetadataReader implements MetadataReader
{
    public function __construct(
        private readonly AttributeMetadataReader $attributeReader,
        private readonly AnnotationMetadataReader $annotationReader,
    ) {
    }

    public function getServiceMetadata(ReflectionClass $serviceClass): ?CircuitBreakerService
    {
        $metadata = $this->attributeReader->getServiceMetadata($serviceClass);

        return $metadata ?? $this->annotationReader->getServiceMetadata($serviceClass);
    }

    public function getMethodMetadata(ReflectionMethod $method): ?CircuitBreakerMetadata
    {
        $metadata = $this->attributeReader->getMethodMetadata($method);

        return $metadata ?? $this->annotationReader->getMethodMetadata($method);
    }
}
