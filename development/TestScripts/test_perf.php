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
$start = microtime(true);
require '../../distribution/libs/Smarty.class.php';
//require('../../distribution/libs/Smarty.compressed.php');
// set_time_limit(1000);
//ini_set('pcre.backtrack_limit', -1);
for ($i = 0; $i < 1000; $i++) {
    $smarty = new Smarty();
    $smarty->use_sub_dirs = true;
    $smarty->cache_id = 'uwe|tews';
    for ($u = 0; $u < 100; $u++) {
        $smarty->assign('a' . $u, $u);
    }
//$smarty->caching=1;
    $r = $smarty->fetch('helloworld.tpl');
    Smarty::$global_tpl_vars = new Smarty_Variable_Container();
    Smarty::$_smarty_vars = array();
    Smarty_Resource::$sources = array();
    Smarty_Compiled::$compileds = array();
}
echo '<br>memory ' . memory_get_usage();
echo '<br> memory peak ' . memory_get_peak_usage();
echo '<br>' . (microtime(true) - $start);
