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

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = true;
//$smarty->force_cache = true;
//$smarty->compile_check = false;
$smarty->cache_lifetime = 1000;

//$smarty->caching = true;
//$smarty->debugging = true;

//$smarty->assign('foo',8);
//$smarty->assign('foo2',88);
$start = _get_time();

//for ($i=0;$i<10;$i++) {
$tpl = new Smarty_Internal_Template('test_speed.tpl', $smarty);
//$a = $tpl->fetch();
//}
//$tpl=$smarty->createTemplate('bug.tpl');
$smarty->display($tpl);

echo _get_time() - $start;
