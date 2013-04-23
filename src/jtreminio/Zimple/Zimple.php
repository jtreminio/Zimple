<?php

namespace jtreminio\Zimple;

use \Pimple;
use \ReflectionClass;

/**
 * This is a wrapper for Pimple that adds several useful features to make it easier to manage your code!
 *
 * This is the non-static version.
 *
 * @package jtreminio/Zimple
 * @author  Juan Treminio <jtreminio@gmail.com>
 */
class Zimple
{
    /** @var Pimple */
    private $pimple;

    /**
     * Set the Pimple instance
     *
     * @param Pimple $pimple
     */
    public function setPimple(Pimple $pimple)
    {
        $this->pimple = $pimple;
    }

    /**
     * Get service from the container
     *
     * @param string $service    Service name, "\Namespace\Component\UserIdentity"
     * @param array  $parameters Parameters service may require for constructor
     * @param bool   $final      Flag to override service when parameters are sent.
     * @return mixed
     */
    public function get($service, array $parameters = array(), $final = false)
    {
        $this->pimple->offsetSet('parameters', $parameters);

        $finalServiceName = "{$service}-isFinal";

        if ($final) {
            $this->pimple->offsetSet($finalServiceName, $service);
        }

        // If service does not exist, add it to Pimple
        if (!$this->pimple->offsetExists($service)) {
            $this->createNewService($service);

            return $this->pimple[$service];
        }

        // Do this again for services called with new set of parameters
        if (!empty($parameters) && !$this->pimple->offsetExists($finalServiceName)) {
            $this->createNewService($service);
        }

        return $this->pimple[$service];
    }

    /**
     * @param string $serviceName Service name - fully qualified
     * @param mixed  $service     Value to return
     * @param bool   $final       Flag to override service when parameters are sent. This is useful when injecting a
     *                            mocked object from our tests and not allowing our code to override the mock with a
     *                            new service definition.
     */
    public function set($serviceName, $service, $final = false)
    {
        $this->pimple->offsetUnset($serviceName);

        $this->pimple->offsetSet($serviceName, $service);

        if ($final) {
            $finalServiceName = "{$serviceName}-isFinal";
            $this->pimple->offsetSet($finalServiceName, 1);
        }
    }

    /**
     * Clear all defined services
     */
    public function clear()
    {
        foreach ($this->pimple->keys() as $key) {
            $this->pimple->offsetUnset($key);
        }
    }

    /**
     * Create a new service in Pimple provider
     *
     * @param string $service    Service name - fully qualified
     */
    private function createNewService($service)
    {
        $object = $this->getUndefined($service);

        $this->pimple[$service] = function () use ($object) {
            return $object;
        };
    }

    /**
     * If the service has not been defined within the container, attempt to instantiate the class directly
     *
     * @param string $fqClassName Fully qualified class name
     * @return object
     */
    private function getUndefined($fqClassName)
    {
        $parameters = $this->pimple->offsetExists('parameters') ? $this->pimple->offsetGet('parameters') : false;

        // Only use ReflectionClass if parameters are needed in constructor
        if (!empty($parameters)) {
            $reflectionClass = new ReflectionClass($fqClassName);
            return $reflectionClass->newInstanceArgs($parameters);
        }

        return new $fqClassName;
    }
}
