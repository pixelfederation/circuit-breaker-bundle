<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Psr\SimpleCache\CacheInterface;

final class CachedMethodExtractor implements MethodExtractor
{
    private const CACHE_KEY = 'pixel_federation_circuit_breaker.circuit_breaker_methods';

    private MethodExtractor $decorated;

    private CacheInterface $cache;

    public function __construct(MethodExtractor $decorated, CacheInterface $cache)
    {
        $this->decorated = $decorated;
        $this->cache = $cache;
    }

    public function extractFor(string $serviceClass): ServiceMethods
    {
        $repoMethods = $this->extractAll();

        return $repoMethods->getForService($serviceClass);
    }

    public function extractAll(): ServicesMethods
    {
        if ($this->cache->has(self::CACHE_KEY)) {
            /** @var ServicesMethods $methods */
            $methods = $this->cache->get(self::CACHE_KEY);

            return $methods;
        }

        $methods = $this->decorated->extractAll();

        $this->cache->set(self::CACHE_KEY, $methods);

        return $methods;
    }
}
