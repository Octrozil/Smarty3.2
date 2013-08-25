<?php
/**
 * Test script for config files
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

function _get_time()
{
    $_mtime = microtime();
    $_mtime = explode(" ", $_mtime);

    return (double) ($_mtime[1]) + (double) ($_mtime[0]);
}

$start = _get_time();

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

//$smarty->_parserdebug= true;
//$smarty->force_compile = true;
//$smarty->force_cache = true;
//$smarty->compile_check = false;
$smarty->cache_lifetime = 1000;
//$smarty->config_overwrite = false;
//$smarty->caching = 1;
$smarty->debugging = true;
$smarty->assign('test', 1, true);
$smarty->assign('test2', 0, true);
$smarty->assign('foo', 'test');
$smarty->cache_id = 'test3';
//$smarty->loadFilter('pre','test');
$smarty->configLoad('file:test2.conf');
//var_dump($smarty->properties);
$d = $smarty->createData($smarty);
$d->configLoad('uwe.conf');
//var_dump($d);
$tpl = $smarty->createTemplate('test_config.tpl', $d);
//$tpl->configLoad('test.conf');
$smarty->display($tpl);
//$smarty->display('test_config.tpl');
//$smarty->display('eval:{#sec2#}');

echo _get_time() - $start;
echo '<br>' . memory_get_usage(true);
