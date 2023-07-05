<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

interface MethodExtractor
{
    /**
     * @param class-string<CircuitBrokenService> $serviceClass
     */
    public function extractFor(string $serviceClass): ServiceMethods;

    public function extractAll(): ServicesMethods;
}
