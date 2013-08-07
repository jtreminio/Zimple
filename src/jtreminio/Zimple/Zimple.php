<?php

namespace jtreminio\Zimple;

use \Pimple;
use \ReflectionClass;

/**
 * This is a wrapper for Pimple that adds some useful features to make it easier to manage code!
 *
 * @package jtreminio/Zimple
 * @author  Juan Treminio <jtreminio@gmail.com>
 */
class Zimple extends Pimple
{
    protected $lockedValues = array();

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same a name as an existing parameter would break your container).
     *
     * @param string $id        The unique identifier for the parameter or object
     * @param mixed  $value     The value of the parameter or a closure to defined an object
     * @param array $parameters The default parameters to use when calling this object
     * @return self
     */
    public function set($id, $value, array $parameters = array())
    {
        $id = ltrim($id, '\\');

        // We want to keep parameters around if setting this object
        if (!empty($parameters) && !$this->offsetExists("lock.{$id}")) {
            $serializedParams = md5(serialize($parameters));
            $this[$serializedParams] = $parameters;
        }

        // $value is fqcn and has no parameters
        if (is_string($value) && empty($serializedParams) && class_exists($value)) {
            $value = function () use ($value) {
                return new $value;
            };
        }

        // $value is fqcn and has constructor parameters
        if (is_string($value) && !empty($serializedParams)) {
            $value = function ($container) use ($value, $serializedParams) {
                $reflectionClass = new ReflectionClass($value);
                return $reflectionClass->newInstanceArgs($container[$serializedParams]);
            };
        }

        if (!$this->offsetExists("lock.{$id}")) {
            $this->offsetSet($id, $value);
        }

        return $this;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id         The unique identifier for the parameter or object
     * @param array  $parameters Parameters object may require for constructor.
     *                           If no parameters passed and the object was previously set, will use pre-defined params
     * @return mixed
     */
    public function get($id, array $parameters = array())
    {
        $id = ltrim($id, '\\');

        // If service does not exist, return new object without creating definition in container
        if (!$this->offsetExists($id)) {
            return $this->getUndefined($id, $parameters);
        }

        // Only use ReflectionClass if parameters are needed in constructor
        if (!empty($parameters) && !$this->offsetExists("lock.{$id}")) {
            $serializedParams = md5(serialize($parameters));
            $this[$serializedParams] = $parameters;

            $reflectionClass = new ReflectionClass($id);
            return $reflectionClass->newInstanceArgs($this[$serializedParams]);
        }

        return parent::offsetGet($id);
    }

    /**
     * Set a value and prevent modification to return value
     *
     * @param string $id        The unique identifier for the parameter or object
     * @param mixed  $value     The value of the parameter or a closure to defined an object
     * @param array $parameters The default parameters to use when calling this object
     * @return self
     */
    public function lock($id, $value, array $parameters = array())
    {
        $id = ltrim($id, '\\');

        $obj = $this->set($id, $value, $parameters);
        $this->offsetSet("lock.{$id}", true);

        return $obj;
    }

    /**
     * Clear all defined
     */
    public function clear()
    {
        foreach ($this->keys() as $key) {
            $this->offsetUnset($key);
        }
    }

    /**
     * If the service has not been defined within the container, attempt to instantiate the class directly
     *
     * @param string $fqClassName Fully qualified class name
     * @param array  $parameters Parameters object may require for constructor
     * @return object
     */
    private function getUndefined($fqClassName, array $parameters)
    {
        // Only use ReflectionClass if parameters are needed in constructor
        if (!empty($parameters)) {
            $reflectionClass = new ReflectionClass($fqClassName);
            return $reflectionClass->newInstanceArgs($parameters);
        }

        return new $fqClassName;
    }
}
