<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command;

use Override;
use PixelFederation\CircuitBreakerBundle\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class GenerateCircuitBrokenProxiesCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly Generator $proxyGenerator,
        private readonly Filesystem $filesystem,
        private readonly string $cacheDirectory,
    ) {
    }

    #[Override]
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @return array<string>
     */
    #[Override]
    public function warmUp(string $cacheDir, ?string $buildDir = null): array // phpcs:ignore
    {
        // The cache directory must exist before the generator's FileLocator is constructed
        $this->filesystem->mkdir($this->cacheDirectory);

        $this->proxyGenerator->generate();

        return [];
    }
}
