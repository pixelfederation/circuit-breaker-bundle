<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command;

use PixelFederation\CircuitBreakerBundle\Generator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class GenerateCircuitBrokenProxiesCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly Generator $proxyGenerator,
    ) {
    }

    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @return array<string>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->proxyGenerator->generate();

        return [];
    }
}
