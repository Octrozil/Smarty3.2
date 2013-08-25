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

require '../../distribution/libs/Smarty.class.php';
$smarty = new Smarty();
$a = array();
for ($i = 0; $i < 100000; $i++) {
    $a["var$i"] = $i;
}
$start = microtime(true);
$smarty->assign($a);
echo '<br>memory ' . memory_get_usage();
echo '<br> memory peak ' . memory_get_peak_usage();
echo '<br>' . (microtime(true) - $start);
