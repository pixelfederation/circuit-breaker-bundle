<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 * @author Juraj Surman <jsurman@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CircuitBreakerBundle;

use Closure;
use OutOfBoundsException;
use PixelFederation\CircuitBreakerBundle\Exception\ServiceIsNotAvailable;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use ProxyManager\Signature\Exception\MissingSignatureException;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
final class Instantiator
{
    private MethodExtractor $methodExtractor;

    private Generator $proxyGenerator;

    private CircuitBreaker $circuitBreaker;

    /**
     * @var array<string, bool>
     */
    private array $variadicParamsCache = [];

    public function __construct(
        MethodExtractor $methodExtractor,
        Generator $proxyGenerator,
        CircuitBreaker $circuitBreaker
    ) {
        $this->methodExtractor = $methodExtractor;
        $this->proxyGenerator = $proxyGenerator;
        $this->circuitBreaker = $circuitBreaker;
    }

    /**
     * @throws OutOfBoundsException
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     */
    public function newInstance(CircuitBrokenService $service): object
    {
        $methods = $this->methodExtractor->extractFor(get_class($service));
        $callbacks = [];

        foreach ($methods as $method) {
            $callbacks[$method->getName()] = $this->getCallback($method, $methods, $methods->getConfiguration());
        }

        return $this->proxyGenerator->createProxy(
            $service,
            $callbacks,
            []
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getCallback(
        ServiceMethod $serviceMethod,
        ServiceMethods $serviceMethods,
        CircuitBreakerConfiguration $configuration
    ): Closure {
        // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        // phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
        /**
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress MissingClosureReturnType
         * @psalm-suppress UnusedClosureParam
         */

        return function (
            $proxy,
            object $instance,
            string $method,
            array $params,
            &$returnEarly
        ) use (
            $configuration,
            $serviceMethod,
            $serviceMethods
        ) {
            $params = $this->processInvokerParameters($instance, $method, $params);
            /** @var Callable $callable */
            $callable = [$instance, $method];
            $invoker = static fn () => call_user_func_array($callable, $params);
            $fallback = $this->createFallback($serviceMethod, $serviceMethods, $configuration, $instance, $params);

            $configuration = $configuration->withIgnoreExceptions($serviceMethod->getIgnoredExceptions());
            /** @psalm-suppress MixedAssignment */
            $result = $this->circuitBreaker->run($invoker, $fallback, $configuration);
            $returnEarly = true;

            return $result;
        };
    }

    /**
     * if the last of the callback parameters is variadic, the parameters array needs to be changed accordingly
     *
     * @param array<mixed|iterable<mixed>> $params
     * @return array<mixed>
     */
    private function processInvokerParameters(object $instance, string $method, array $params): array
    {
        $params = array_values($params); // fix for php 8 and named parameters

        if (!$this->isLastMethodParamVariadic($instance, $method)) {
            return $params;
        }

        /** @var iterable<mixed> $lastParam */
        $lastParam = array_pop($params);

        return [...$params, ...$lastParam];
    }

    private function isLastMethodParamVariadic(object $instance, string $method): bool
    {
        $cacheKey = sprintf('%s::%s', get_class($instance), $method);

        if (isset($this->variadicParamsCache[$cacheKey])) {
            return $this->variadicParamsCache[$cacheKey];
        }

        $reflClass = new ReflectionClass($instance);
        $reflMethod = $reflClass->getMethod($method);
        $methodParams = $reflMethod->getParameters();

        if (empty($methodParams)) {
            return false;
        }

        $lastMethodParam = array_pop($methodParams);

        return $this->variadicParamsCache[$cacheKey] = $lastMethodParam->isVariadic();
    }

    /**
     * @param array<mixed> $params @codingStandardsIgnoreLine
     */
    private function createFallback(
        ServiceMethod $serviceMethod,
        ServiceMethods $serviceMethods,
        CircuitBreakerConfiguration $configuration,
        object $instance,
        array $params
    ): Closure {
        $fallback = static function (ServiceIsNotAvailable $e): void {
            throw $e;
        };

        $circuitBreaker = $this->circuitBreaker;

        $fallbackMethods = [];
        $currentServiceMethod = $serviceMethod;
        while ($currentServiceMethod !== null && $currentServiceMethod->hasFallback()) {
            $fallbackMethods[] = $currentServiceMethod->getFallback();
            $currentServiceMethod = $serviceMethods->findByMethodName($currentServiceMethod->getFallback());
        }

        foreach (array_reverse($fallbackMethods) as $fallbackMethod) {
            /** @var Closure():mixed $callable */ // phpcs:ignore
            $callable = [$instance, $fallbackMethod];
            /**
             * @psalm-suppress MissingClosureReturnType
             * @psalm-suppress TooManyArguments
             */
            $invoker = static fn () => call_user_func_array($callable, $params);

            $circuitBrokenFallback = $serviceMethods->findByMethodName($fallbackMethod);
            if ($circuitBrokenFallback === null) {
                $fallback = $invoker;

                continue;
            }

            /** @psalm-suppress MissingClosureReturnType */
            $fallback = static function () use (
                $circuitBreaker,
                $invoker,
                $fallback,
                $configuration,
                $circuitBrokenFallback
            ) {
                $configuration = $configuration->withIgnoreExceptions($circuitBrokenFallback->getIgnoredExceptions());

                return $circuitBreaker->run($invoker, $fallback, $configuration);
            };
        }

        return $fallback;
    }
}
