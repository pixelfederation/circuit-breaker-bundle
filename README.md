# Circuit breaker bundle

This bundle tries to copy the famous java [Hystrix](https://github.com/Netflix/Hystrix) library 
and tries to make the usage of the circuit breaking pattern effortless to the developers. 

The developers can just mark any service as circuit broken, using special annotations 
and this bundle makes all the wiring under the hood automatically.

The current backend implementation is using [Ganesha](https://github.com/ackintosh/ganesha).

## Installation

```bash
$ composer require pixelfederation/circuit-breaker-bundle
```

## Configuration

Just enable the bundle. There are no configuration options for now.

```php
// in config/bundles.php add this line:
PixelFederation\CircuitBreakerBundle\Bridge\Symfony\PixelFederationCircuitBreakerBundle::class
```

## Usage

To activate circuit breaking on a given service, the service has to implement 
the `PixelFederation\CircuitBreakerBundle\CircuitBrokenService` interface.

The class mustn't be marked as `final`, because a proxy class is derived from it under the hood.

To configure circuit breaking, you can use the class level configuration or method level configuration.
The class level configuration is valid for all the circuit-broken methods.
It is configured as a class level annotation `@CircuitBreakerService`. To mark a public method as circuit broken, 
the `@CircuitBreaker` annotation has to be used:

```php
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker;

/**
 * @CircuitBreakerService(
 *    defaultFallback="makeDefaultFallbackRequest", 
 *    ignoreExceptions={BadMethodCallException::class}
 * )
 */
class Service {
    /**
     * @CircuitBreaker() 
     */
    public function iShouldBeCircuitBroken(): int
    {
        return 0;
    }
}
```

The configuration options for the `@CircuitBreakerService` are:
- **defaultFallback**: a public method of the same class which should be called on an exception occurrence, 
if no fallback is configured for a circuit-broken method.
- **ignoreExceptions**: exception list, which doesn't trigger marking the wrapped service as failing

The method level annotation `@CircuitBreaker` can override the class level configuration 
with its own configuration options, which are:
- **fallbackMethod**: a public method of the same class which should be called on an exception occurrence
- **ignoreExceptions**: exception list, which doesn't trigger marking the wrapped service as failing

```php
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker;

class Service {
    /**
     * @CircuitBreaker(fallbackMethod="makeSpecialFallbackRequest") 
     */
    public function iShouldBeCircuitBroken(): int
    {
        return 0;
    }
}
```

The fallback methods have to be public as well.

## Full example

```php
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreaker;
use PixelFederation\CircuitBreakerBundle\Annotation\CircuitBreakerService;
use PixelFederation\CircuitBreakerBundle\CircuitBrokenService;
use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;

/**
 * The class level annotation activates circuit breaking configuration on methods
 * marked with the @CircuitBreaker annotation.
 * In this case, also the default fallback for each circuit broken method is configured
 * (the 'makeDefaultFallbackRequest' method)
 * 
 * The ignoreExceptions option sets exceptions, on which occurrence the service won't be marked
 * as failing, e.g. some app/system level exceptions, which don't need to have to do anything 
 * with http requests under the hood.
 * 
 * @CircuitBreakerService(
 *    defaultFallback="makeDefaultFallbackRequest", 
 *    ignoreExceptions={BadMethodCallException::class}
 * )
 */
class TestService implements CircuitBrokenService
{
    private SomeHttpClient $client;

    public function __construct(SomeHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * This method is marked to be circuit-broken. It uses the class level configured fallback
     * and ignores the class level configured exceptions. 
     * 
     * @CircuitBreaker()
     */
    public function makeRequest(): int
    {
        return $this->client->makeRequest(); // it is important to set http timeouts here
    }

    /**
     * This method is marked to be circuit-broken. It uses a different fallback, not the one
     * configured on class level.
     * 
     * @CircuitBreaker(fallbackMethod="makeSpecialFallbackRequest")
     */
    public function makeRequestWithCustomCircuitBreaker(string $param): int
    {
        return $this->client->makeAnotherRequest($param); // it is important to set http timeouts here
    }

    public function makeDefaultFallbackRequest(): void
    {
        return 1; // ideally there is no call to any external dependency in the fallback method
    }
    
    public function makeSpecialFallbackRequest(string $param): void
    {
        return 0; // ideally there is no call to any external dependency in the fallback method
    }
}
```

Enjoy ;)
