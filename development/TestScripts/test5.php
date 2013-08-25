<?php
/**
 * Test script for the {debug} tag
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

class Test
{
    public function test()
    {
        return 1;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'test':
                return $this->test();
            case 'test':
                return $this->test();
            case 'test':
                return $this->test();
            case 'uwe':
                $this->uwe = 2;

                return 2;
            default:
                echo 'error ' . $name;

                return;
        }
    }
}

$obj = new Test;

$start = microtime(true);

for ($i = 1;
     $i < 4;
     $i++) {
    $a = $obj->test();
}
echo microtime(true) - $start . '<br>';

$start = microtime(true);

for ($i = 1;
     $i < 4;
     $i++) {
    $a = $obj->uwe;
}
echo microtime(true) - $start . '<br>';
echo '<br>' . memory_get_usage(true);
