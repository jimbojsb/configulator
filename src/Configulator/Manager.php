<?php
namespace Configulator;

class Manager implements \ArrayAccess
{
    protected $options;
    protected $services;

    public function __call($func, $args)
    {
        if ($this->services[$func]) {
            if ($this->services[$func]['shared']) {
                if (!isset($this->services[$func]['instance'])) {
                    $this->services[$func]['instance'] = $this->serviceFactory($func, $args);
                }
                return $this->services[$func]['instance'];
            } else {
                return $this->serviceFactory($func, $args);
            }
        }
    }

    public function serviceFactory($serviceName)
    {
        if (is_callable($this->services[$serviceName]['service'])) {
            $callback = $this->services[$serviceName]['service'];
            return $callback($this);
        } else if (is_object($this->services[$serviceName]['service'])) {
            return clone($this->services[$serviceName]['service']);
        } else {
            throw new \RuntimeException("Cannot create a service from a non-object or a non-callable");
        }
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function loadFile($file, $profile = null)
    {
        $this->options = ConfigFile::getOptions($file, $profile);
    }

    public function register($serviceName, $service, $shared = true)
    {
        $this->services[$serviceName] = array('service' => $service, 'shared' => $shared);
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
        throw new \RuntimeException("Config values are immutable once loaded");
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException("Config values are immutable once loaded");
    }

}

