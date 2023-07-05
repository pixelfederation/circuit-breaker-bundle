<?php

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Psr\Cache\CacheItemPoolInterface;

final class CachedMethodExtractor implements MethodExtractor
{
    private const CACHE_KEY = 'pixel_federation_circuit_breaker.circuit_breaker_methods';

    public function __construct(
        private readonly MethodExtractor $decorated,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function extractFor(string $serviceClass): ServiceMethods
    {
        $repoMethods = $this->extractAll();

        return $repoMethods->getForService($serviceClass);
    }

    public function extractAll(): ServicesMethods
    {
        $item = $this->cache->getItem(self::CACHE_KEY);

        if ($item->isHit()) {
            /** @var ServicesMethods $methods */
            $methods = $item->get();

            return $methods;
        }

        $methods = $this->decorated->extractAll();
        $item->set($methods);
        $this->cache->save($item);

        return $methods;
    }
}
