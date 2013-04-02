<?php

namespace jtreminio\Container;

use \Pimple;
use \ReflectionClass;

abstract class Container
{
    /** @var Pimple */
    private static $pimple;

    /**
     * Set the Pimple instance
     *
     * @param Pimple $pimple
     */
    public static function setPimple(Pimple $pimple)
    {
        static::$pimple = $pimple;
    }

    /**
     * Get service from the container
     *
     * @param string $service    Service name, "\Namespace\Component\UserIdentity"
     * @param array  $parameters Parameters service may require for constructor
     * @return mixed
     */
    public static function get($service, array $parameters = array())
    {
        self::$pimple->offsetSet('parameters', $parameters);

        // If service does not exist, add it to Pimple
        if (!self::$pimple->offsetExists($service)) {
            self::createNewService($service, $parameters);
        }

        // Flag for finalizing a definition, preventing overwrites
        $finalServiceName = "{$service}-isFinal";

        // Do this again for services called with new set of parameters
        if (!empty($parameters) && !self::$pimple->offsetExists($finalServiceName)) {
            self::createNewService($service, $parameters);
        }

        return self::$pimple[$service];
    }

    /**
     * @param string $serviceName Service name - fully qualified
     * @param mixed  $service     Value to return
     * @param bool   $final       Flag to override service when parameters are sent. This is useful when injecting a
     *                            mocked object from our tests and not allowing our code to override the mock with a
     *                            new service definition.
     */
    public static function set($serviceName, $service, $final = false)
    {
        self::$pimple->offsetUnset($serviceName);

        self::$pimple->offsetSet($serviceName, $service);

        if ($final) {
            $finalServiceName = "{$serviceName}-isFinal";
            self::$pimple->offsetSet($finalServiceName, 1);
        }
    }

    /**
     * Create a new service in Pimple provider
     *
     * @param string $service    Service name - fully qualified
     * @param array  $parameters Array of optional parameters
     */
    private static function createNewService($service, $parameters)
    {
        $object = static::getUndefined($service, $parameters);

        self::$pimple[$service] = function () use ($object) {
            return $object;
        };
    }

    /**
     * If the service has not been defined within the container, attempt to instantiate the class directly
     *
     * @param string $fqClassName Fully qualified class name
     * @return object
     */
    private static function getUndefined($fqClassName)
    {
        $reflectionClass = new ReflectionClass($fqClassName);

        $parameters = self::$pimple->offsetExists('parameters') ? self::$pimple->offsetGet('parameters') : false;

        if (!empty($parameters)) {
            return $reflectionClass->newInstanceArgs($parameters);
        }

        return $reflectionClass->newInstance();
    }
}

