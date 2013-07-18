# Configulator

[![Build Status](https://travis-ci.org/jimbojsb/configulator.png?branch=master)](https://travis-ci.org/jimbojsb/configulator)

Configulator is a very lightweight configuation manager and service locator for PHP projects. It is designed to be somewhat of a poor man's dependency injection container in that the service factories are always passed the managed configuration options as well as the service factories themselves, such that one can achieve simple dependency resolution and configuration of common service needs, such as database connections.

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
Configulator supports 4 ways to populate it's internal storage with configuration data. You can either directly pass an array of options, or you can load options from one of the following file formats:
* [PHP Array include](https://github.com/jimbojsb/configulator/blob/master/tests/resources/test_config.php)
* [JSON](https://github.com/jimbojsb/configulator/blob/master/tests/resources/test_config.json)
* [YAML](https://github.com/jimbojsb/configulator/blob/master/tests/resources/test_config.yml)

When loading from a file, you have the option of using "configuration profiles", which would like be tied to your environment, such as "production" or "development". All configuration file types support inheritance of other profiles defined within the file.

```php
Configulator()->setOptions(['configItem1' => 'configValue1']);

// or

Configulator()->loadFile("/path/to/myconfig.yml", "production");
```

When using `Configulator()->loadFile()` the second argument is the configuration profile, which is optional. You would likely only use this if you had chosen to structure your config files to take advantage of inheritance. If you do have profiles in your config files and you do _NOT_ pass a profile argument, you will be returned the entire option set contained within the file _WITHOUT_ the inheritance resolved.

### Accessing config options
`Configulator\Manager` implements ArrayAccess, and all config options are available through array notation. However, all config options are immutable once loaded, so code which attempts to use array notation to set values back into Configulator will throw a `RuntimeException`.

```php
$value = Configulator()["configItem1"];
```

### Service Factories
Configulator also manages and locates services within your application. The primary reason to couple this with configuration management is that it becomes trivial to create and configure services with intimiate access to the config options.

Service factories can either be an instance of an object (not really a factory, since this assumes you've pre-bootstrapped this instance), or a callable which returns the service. Generally, this is going to be an anonymous function. If the factory is a callable, `Configulator\Manager` is passed as the last (usually only) argument to the callable, such that you have access to the entire config as well as the easy ability to resolve service dependencies.

Additionally, Configulator has the notion of shared and non-shared services. A shared service is a singleton instance of the service object which is lazy-instantiated on the first request for it and cached for future calls. A non-shared service returns a new instance from the factory callable on every request for it. If you register an pre-fabricated instance instead of a callable as a non-shared service, you will receive a clone of that instance upon request. Services default to shared.

Services are first registered with the `register()` method and then created and retrieved by calling the method corresponding to their name.

```php
Configulator()->register('mongodb', function() {
    return new MongoClient;
});

$mongo = Configulator()->mongodb();

Configulator()->register('mailer', function($configulator) {
    $transport = Swift_SmtpTransport::newInstance($configulator["smtp_host"], $configulator["smtp_port"]);
    return Swif_Mailer::newInstance($transport);
}, false);

$mailer = Configulator()->mailer();

```

### Local Override Files
Often times, it may be necessary to use a local file that isn't committed into source control to override some existing values from the core file. Using a local file will override matching values from any location, regardless of environment. This override is processed before inheritance an environment settings are resolved.

Use the optional 3rd argument for Configulator\Manager::loadFile to pass a local file path.

```php
Configulator()->loadFile('/path/to/myconfig.yml', 'development', '/path/to/local_myconfig.yml');
```


## Contributing
Pull requests are welcome, but should not materially expand the scope of the project.

