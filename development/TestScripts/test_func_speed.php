<?php
/**
 * Test script for the {debug} tag
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

class Testc
{
    public function test($a, $b)
    {
        return $a;
    }
}

function test($a, $b)
{
    return $a;
}

$obj = new Testc;

echo '<br>normal<br>';
$start = _get_time();
for ($i = 1;
     $i < 1000;
     $i++) {
    $c = test(1, 2);
}
echo _get_time() - $start . '<br>';

$func = 'test';
echo '<br>variable<br>';
$start = _get_time();
for ($i = 1;
     $i < 1000;
     $i++) {
    $c = $func(1, 2);
}
echo _get_time() - $start . '<br>';

echo '<br>cufa variable<br>';
$start = _get_time();
for ($i = 1;
     $i < 1000;
     $i++) {
    $c = call_user_func_array($func, array(1, 2));
}
echo _get_time() - $start . '<br>';

$object = array($obj, 'test');
echo '<br>cufa object<br>';
$start = _get_time();
for ($i = 1;
     $i < 1000;
     $i++) {
    $c = call_user_func_array($object, array(1, 2));
}
echo _get_time() - $start . '<br>';
echo '<br>cuf variable<br>';
$start = _get_time();
for ($i = 1;
     $i < 1000;
     $i++) {
    $c = call_user_func($func, 1, 2);
}
echo _get_time() - $start . '<br>';

$object = array($obj, 'test');
echo '<br>cuf object<br>';
$start = _get_time();
for ($i = 1;
     $i < 1000;
     $i++) {
    $c = call_user_func($object, 1, 2);
}
echo _get_time() - $start . '<br>';
