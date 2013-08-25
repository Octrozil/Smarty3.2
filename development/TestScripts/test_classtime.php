<?php
/**
 * Test script for the Smarty compiler
 *
 * It displays a form in which a template source code can be entered.
 * The template source will be compiled, rendered and the result is displayed.
 * The compiled code is displayed as well
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

function _get_time()
{
    $_mtime = microtime();
    $_mtime = explode(" ", $_mtime);

    return (double) ($_mtime[1]) + (double) ($_mtime[0]);
}

class Test
{
    public function getm()
    {
        return true;
    }

    public static function gets()
    {
        return true;
    }
}

$c = new Test;

$start = _get_time();

for ($i = 0; $i < 100000; $i++) {
    $c = new Test;
    $j = $c->getm();
}

echo '<br><br>';
echo _get_time() - $start;
$start = _get_time();

for ($i = 0; $i < 100000; $i++) {
    $j = Test::gets();
}

echo '<br><br>';
echo _get_time() - $start;
