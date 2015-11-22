<?php
namespace Configulator;

class Manager implements \ArrayAccess
{
    protected $options = array();
    protected $allowMutableOptions = false;
    protected $services = array();
    protected $factories = array();

    public function __call($func, $args)
    {
        if ($this->factories[$func]) {
            if ($this->factories[$func]['service'] === false) {
                return $this->factory($func, $args);
            }
        } else {
            throw new \RuntimeException("Cannot invoke non-existing factory $func");
        }
    }

    public function __get($key)
    {
        if (!isset($this->services[$key])) {
            if (isset($this->factories[$key])) {
                if ($this->factories[$key]["service"]) {
                    $this->services[$key] = $this->factory($key);
                }
            }
        }
        return $this->services[$key];
    }

    public function factory($factoryName, $args = array())
    {
        if (isset($this->factories[$factoryName])) {
            $callback = $this->factories[$factoryName]['factory'];
            return call_user_func_array($callback, $args);
        } else {
            throw new \RuntimeException("Cannot invoke non-existing factory $factoryName");
        }

    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function loadFile($file, $profile = null, $localFile = null)
    {
        $this->options = ConfigFile::getOptions($file, $profile, $localFile);
    }

    public function register($factoryName, $factory, $isService = true)
    {
        if (is_object($factory) && !is_callable($factory) && !$isService) {
            throw new \InvalidArgumentException("Non-callable objects can only be registered as a service");
        } else if (is_object($factory) && !is_callable($factory)) {
            $this->services[$factoryName] = $factory;
        } else if (is_callable($factory)) {
            $this->factories[$factoryName] = array(
                "factory" => $factory,
                "service" => $isService
            );
        } else {
            throw new \InvalidArgumentException("Cannot register a non-object or a non-callable");
        }
    }

    public function release($serviceName)
    {
        unset($this->services[$serviceName]['instance']);
    }

    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->options[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($this->allowMutableOptions) {
            $this->options[$offset] = $value;
        } else {
            throw new \RuntimeException("Config values are immutable once loaded");
        }

    }

    public function offsetUnset($offset)
    {
        if ($this->allowMutableOptions) {
            unset($this->options[$offset]);
        } else {
            throw new \RuntimeException("Config values are immutable once loaded");
        }

    }

    public function setAllowMutableOptions($allowMutableOptions)
    {
        $this->allowMutableOptions = $allowMutableOptions;
    }

}

