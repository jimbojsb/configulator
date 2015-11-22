<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Configulator\Manager as Configulator;

class ConfigulatorTest extends PHPUnit_Framework_TestCase
{
    public function testGlobalSingleton()
    {
        $config1 = Configulator();
        $config2 = Configulator();

        $this->assertEquals(spl_object_hash($config1), spl_object_hash($config2));
    }

    public function testArrayAccess()
    {
        $m = new Configulator;
        $m->setOptions(['foo' => 'bar', 'baz' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $m['foo']);
        $this->assertEquals('bar', $m['baz']['foo']);
    }

    public function testImmutable()
    {
        $m = new Configulator;
        $m->setOptions(['foo' => 'bar', 'baz' => ['foo' => 'bar']]);
        $this->setExpectedException('RuntimeException');
        $m['foo'] = 'bar';
    }

    public function testServiceFactoryWithPrefab()
    {
        $m = new Configulator;
        $m->register('testService1', new stdClass);
        $m->register('testService2', new stdClass, false);

        $ts1 = $m->testService1();
        $this->assertEquals(spl_object_hash($ts1), spl_object_hash($m->testService1()));

        $ts2 = $m->testService2();
        $this->assertNotEquals(spl_object_hash($ts2), spl_object_hash($m->testService2()));
    }

    public function testServiceFactoryWithCallable()
    {
        $m = new Configulator;
        $m->setOptions(['foo' => 'bar']);
        $m->register('testService1', function() {
            return new \stdClass;
        });

        $m->register('testService2', function() {
            return new \stdClass;
        }, false);

        $ts1 = $m->testService1();
        $this->assertEquals(spl_object_hash($ts1), spl_object_hash($m->testService1()));

        $ts2 = $m->testService2();
        $this->assertNotEquals(spl_object_hash($ts2), spl_object_hash($m->testService2()));
    }

    public function testServiceFactoryWithConfigAndCallable()
    {
        $m = new Configulator;
        $m->setOptions(['foo' => 'bar']);
        $m->register('testService1', function($config) {
            $s = new \stdClass;
            $s->foo = $config["foo"];
            return $s;
        });

        $this->assertEquals('bar', $m->testService1()->foo);
    }

    public function testAllowMutableOptions()
    {
        $m = new Configulator;
        $m->setOptions(['foo' => 'bar']);
        try {
            $m["foo"] = "baz";
            $this->fail('Should not allow changing of options');
        } catch (Exception $e) {
        }
        $m->setAllowMutableOptions(true);
        $m["foo"] = "baz";
        $this->assertEquals("bar", $m["foo"]);
    }
}