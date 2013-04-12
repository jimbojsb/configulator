# Configulator

Configulator is a very lightweight configuation management and service locator for PHP projects. It is designed to be somewhat of a poor man's dependency injection container in that the service factories are always passed the managed configuration options as well as the service factories themselves, such that one can achieve simple dependency resolution and configuration of common service needs, such as database connections.

## Getting Configulator
Configulator requires [Composer](http://getcomposer.org) and does not provide it's own autoloader. It is available on Packagist [here](https://packagist.org/packages/jimbojsb/configulator). You'll be adding something similar to this to your composer.json file in your project:

```json
"require": {
    "jimbojsb/configulator": "dev-master"
}
```

## Usage
Configulator can be used in two ways. You can create a new instance of `Configulator\Manager` directly, and take responsiblity
for passing the object around in your existing application code as you see fit and necessary.

```php
<?php

// assume here you've at some point require composer's autoloader

use Configulator\Manager;
$configulator = new Configulator\Manager;
```

Alternatively, Configulator provides a global-namespaced singleton of the manager that will be available to your entire project. This is exposed via the Configulator() function, which will return the singleton `Configulator\Manager` instance;

```php
$configulator = Configulator();
```

All of the remaining examples use this syntax, though the functionality is the same if you're using the raw manager instance yourself.


### Loading config options
Configulator supports 5 ways to populate it's internal storage with configuration data. You can either directly pass an array of options, or you can load options from one of the following file formats:
* PHP Array include
* JSON
* YAML
* INI

When loading from a file, you have the option of using "configuration profiles", which would like be tied to your environment, such as "production" or "development"

