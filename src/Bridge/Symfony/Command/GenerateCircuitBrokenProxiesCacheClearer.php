<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command;

use Override;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

final class GenerateCircuitBrokenProxiesCacheClearer implements CacheClearerInterface
{
    public function __construct(
        private readonly PruneableInterface $cache,
        private readonly Filesystem $filesystem,
        private readonly string $cacheDirectory,
    ) {
    }

    #[Override]
    public function clear(string $cacheDir): void // phpcs:ignore
    {
        $this->cache->prune();
        $this->filesystem->remove($this->cacheDirectory);
    }
}
