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

$start = _get_time();

require '../../distribution/libs/Smarty.class.php';

for ($i = 1; $i < 2; $i++) {
    $smarty = new Smarty;
//$smarty->caching = 1;
    $smarty->compile_check = false;
    $smarty->display('testl.tpl');
}
echo _get_time() - $start . '<br>';
echo '<br>' . memory_get_peak_usage(true);
