<?php
/**
 * Test script for the {debug} tag
 * @author Uwe Tews
 * @package SmartyTestScripts
 */
function _get_time()
{
    $_mtime = microtime();
    $_mtime = explode(" ", $_mtime);

    return (double) ($_mtime[1]) + (double) ($_mtime[0]);
}

require '../../distribution/libs/Smarty.class.php';

class My extends Smarty
{
    protected $cachemy = 0;

    public function setcachemy($v)
    {
        $this->cachemy = $v;
    }

    public function setCacheLifeTime($v)
    {
        $this->cache_lifetime = $v;
    }

    public function getTime()
    {
        return $this->cache_lifetime;
    }

    public function __set($name, $v)
    {
        $k = 'set' . $name;
        $this->$k($v);
    }
}

$smarty = new My;

$start = _get_time();
for ($i = 0; $i < 100000; $i++) {
    $smarty->caching = $i;
}
echo 'set direct ' . (_get_time() - $start) . '<br>';
$start = _get_time();
for ($i = 0; $i < 100000; $i++) {
    $smarty->cachemy = $i;
}
echo 'set direct through _set() ' . (_get_time() - $start) . '<br>';
$start = _get_time();
for ($i = 0; $i < 100000; $i++) {
    $smarty->setCacheLifeTime($i);
}
echo 'setter ' . (_get_time() - $start) . '<br>';
$start = _get_time();
for ($i = 0; $i < 100000; $i++) {
    $smarty->setCaching($i);
}
echo 'setter by __call() ' . (_get_time() - $start) . '<br>';
$start = _get_time();
for ($i = 0; $i < 100000; $i++) {
    $v = $smarty->caching;
}
echo 'get direct ' . (_get_time() - $start) . '<br>';
$start = _get_time();
for ($i = 0; $i < 100000; $i++) {
    $v = $smarty->getTime();
}
echo 'getter ' . (_get_time() - $start) . '<br>';
$start = _get_time();
for ($i = 0; $i < 100000; $i++) {
    $v = $smarty->getCaching();
}
echo 'getter by __call() ' . (_get_time() - $start) . '<br>';
