<?php

namespace jtreminio\Zimple;

use \Pimple;
use \ReflectionClass;

/**
 * This is a wrapper for Pimple that adds several useful features to make it easier to manage your code!
 *
 * @package jtreminio/Zimple
 * @author  Juan Treminio <jtreminio@gmail.com>
 */
abstract class ZimpleStatic
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
     * @param bool   $final      Flag to override service when parameters are sent.
     * @return mixed
     */
    public static function get($service, array $parameters = array(), $final = false)
    {
        self::$pimple->offsetSet('parameters', $parameters);

        $finalServiceName = "{$service}-isFinal";

        if ($final) {
            self::$pimple->offsetSet($finalServiceName, $service);
        }

        // If service does not exist, add it to Pimple
        if (!self::$pimple->offsetExists($service)) {
            self::createNewService($service);

            return self::$pimple[$service];
        }

        // Do this again for services called with new set of parameters
        if (!empty($parameters) && !self::$pimple->offsetExists($finalServiceName)) {
            self::createNewService($service);
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
     * Clear all defined services
     */
    public static function clear()
    {
        foreach (self::$pimple->keys() as $key) {
            self::$pimple->offsetUnset($key);
        }
    }

    /**
     * Create a new service in Pimple provider
     *
     * @param string $service    Service name - fully qualified
     */
    private static function createNewService($service)
    {
        $object = static::getUndefined($service);

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
        $parameters = self::$pimple->offsetExists('parameters') ? self::$pimple->offsetGet('parameters') : false;

        // Only use ReflectionClass if parameters are needed in constructor
        if (!empty($parameters)) {
            $reflectionClass = new ReflectionClass($fqClassName);
            return $reflectionClass->newInstanceArgs($parameters);
        }

        return new $fqClassName;
    }
}
