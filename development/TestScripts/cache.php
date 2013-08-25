<?php
/**
 * Test script for the {debug} tag
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
//$smarty->force_cache = true;
$smarty->caching = 2;
$smarty->cache_lifetime = 10;

if ($smarty->is_cached('cache.tpl')) {
    echo 'ist gecached<br>';
}
var_dump($smarty->is_cached('cache.tpl'));
$smarty->assign('time', time());
echo $smarty->fetch('cache.tpl');
