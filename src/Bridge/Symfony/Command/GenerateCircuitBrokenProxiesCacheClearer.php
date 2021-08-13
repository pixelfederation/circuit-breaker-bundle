<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle\Bridge\Symfony\Command;

use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

final class GenerateCircuitBrokenProxiesCacheClearer implements CacheClearerInterface
{
    private PruneableInterface $cache;

    private Filesystem $filesystem;

    private string $cacheDirectory;

    public function __construct(PruneableInterface $cache, Filesystem $filesystem, string $cacheDirectory)
    {
        $this->cache = $cache;
        $this->filesystem = $filesystem;
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clear(string $cacheDir): void // phpcs:ignore
    {
        $this->cache->prune();
        $this->filesystem->remove($this->cacheDirectory);
    }
}
