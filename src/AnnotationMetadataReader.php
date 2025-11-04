<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Doctrine\Common\Annotations\Reader;
use Override;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker as CircuitBreakerMetadata;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use ReflectionClass;
use ReflectionMethod;

final class AnnotationMetadataReader implements MetadataReader
{
    public function __construct(
        private readonly Reader $annotationsReader,
    ) {
    }

    /**
     * @param ReflectionClass<object> $serviceClass
     */
    #[Override]
    public function getServiceMetadata(ReflectionClass $serviceClass): ?CircuitBreakerService
    {
        return $this->annotationsReader->getClassAnnotation($serviceClass, CircuitBreakerService::class);
    }

    #[Override]
    public function getMethodMetadata(ReflectionMethod $method): ?CircuitBreakerMetadata
    {
        return $this->annotationsReader->getMethodAnnotation($method, CircuitBreakerMetadata::class);
    }
}
