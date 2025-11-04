<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Override;
use Psr\Cache\CacheItemPoolInterface;

final class CachedMethodExtractor implements MethodExtractor
{
    private const string CACHE_KEY = 'pixel_federation_circuit_breaker.circuit_breaker_methods';

    public function __construct(
        private readonly MethodExtractor $decorated,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    #[Override]
    public function extractFor(string $serviceClass): ServiceMethods
    {
        $repoMethods = $this->extractAll();

        return $repoMethods->getForService($serviceClass);
    }

    #[Override]
    public function extractAll(): ServicesMethods
    {
        $item = $this->cache->getItem(self::CACHE_KEY);

        if ($item->isHit()) {
            $methods = $item->get();
            \assert($methods instanceof ServicesMethods);

            return $methods;
        }

        $methods = $this->decorated->extractAll();
        $item->set($methods);
        $this->cache->save($item);

        return $methods;
    }
}
