<?php
/**
 * Test script for include performance
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
//set_time_limit(90);

$smarty->addPluginsDir('./plugins');
$smarty->merge_compiled_includes = false;
//$smarty->force_compile = true;
//$smarty->force_cache = true;
$smarty->compile_check = false;
$smarty->cache_lifetime = 20000;

//
//$smarty->caching = true;
//$smarty->debugging = true;

$smarty->assign('foo', 8);
$smarty->assign('foo2', 88);

$tpl = $smarty->createTemplate('include_b.tpl');

if (!$tpl->isCached()) {
    echo 'not valid <br>';
}

$smarty->display($tpl);

echo _get_time() - $start;
echo '<br>' . memory_get_peak_usage(true);
