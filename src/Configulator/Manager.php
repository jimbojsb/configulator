<?php
namespace Configulator;

use Configulator\ConfigFile\Php,
    Configulator\ConfigFile\Yaml,
    Configulator\ConfigFile\Ini,
    Configulator\ConfigFile\Json;

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
        $f = new \SplFileObject($file);
        switch ($f->getExtension()) {
            case "php":
                return $this->options = Php::getOptions($file);
            case "ini":
                return $this->options = Ini::getOptions($file);
            case "yaml":
            case "yml":
                return $this->options = Yaml::getOptions($file);
            case "json":
                return $this->options = Json::getOptions($file);
        }
        throw new \InvalidArgumentException("File type " . $f->getExtension() . " not supported");
    }

    public function register($serviceName, $service, $shared = true)
    {
        $this->services[$serviceName] = ['service' => $service, 'shared' => $shared];
    }

    public function release($serviceName)
    {
        unset($this->services[$serviceName]['instance']);
    }

    public function offsetExists($offset)
    {
        return isset($this->config);
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

