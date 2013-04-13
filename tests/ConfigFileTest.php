<?php

use Configulator\ConfigFile;

class ConfigFileTest extends PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $phpOptions = ConfigFile::getOptions(__DIR__ . '/resources/test_config.php', 'staging');
        $ymlOptions = ConfigFile::getOptions(__DIR__ . '/resources/test_config.yml', 'staging');
        $jsonOptions = ConfigFile::getOptions(__DIR__ . '/resources/test_config.json', 'staging');

        foreach ([$phpOptions, $ymlOptions, $jsonOptions] as $options) {
            $this->assertEquals('value7', $options['key1']);
            $this->assertEquals('value8', $options['key3'][0]);
            $this->assertEquals('value9', $options['key2']['subkey1']);
            $this->assertEquals('value3', $options['key2']['subkey2']);
        }

    }
}