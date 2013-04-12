<?php
require_once __DIR__ . '/../vendor/autoload.php';
Configulator()->register('foo', function($config) {
    return new MongoClient();
});
Configulator()->loadFile('foo.php');
var_dump(Configulator()['foo']);
Configulator()->foo();