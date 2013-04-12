<?php
use Configulator\Manager;

function Configulator()
{
    static $instance;
    if (!($instance instanceof Manager)) {
        $instance = new Manager();
    }
    return $instance;
}