<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command;

use PixelFederation\CircuitBreakerBundle\Generator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class GenerateCircuitBrokenProxiesCacheWarmer implements CacheWarmerInterface
{
    private Generator $proxyGenerator;

    public function __construct(Generator $proxyGenerator)
    {
        $this->proxyGenerator = $proxyGenerator;
    }

    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @return array<string>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function warmUp(string $cacheDir): array // phpcs:ignore
    {
        $this->proxyGenerator->generate();

        return [];
    }
}
