<?php

namespace BrightleafDigital\Container;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * A simple PSR-11 compliant service container.
 */
class ServiceContainer implements ContainerInterface
{
    /**
     * @var array
     */
    private array $instances = [];

    /**
     * @var array
     */
    private array $factories = [];

    /**
     * Sets a service instance or factory.
     *
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function set(string $id, $value): void
    {
        if (is_callable($value)) {
            $this->factories[$id] = $value;
            // Clean existing instances if we override with a factory
            unset($this->instances[$id]);
        } else {
            $this->instances[$id] = $value;
            // Clean factories if we override with an instance
            unset($this->factories[$id]);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->factories[$id])) {
            $this->instances[$id] = ($this->factories[$id])($this);
            return $this->instances[$id];
        }

        throw new class ("Service not found: $id") extends Exception implements NotFoundExceptionInterface {
        };
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->factories[$id]);
    }
}
