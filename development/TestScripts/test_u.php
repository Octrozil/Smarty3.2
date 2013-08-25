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

// set_time_limit(1000);
ini_set('pcre.backtrack_limit', -1);

$smarty = new Smarty;
$start = microtime(true);
for ($i = 0; $i < 1001; $i++) {

    $tpl = $smarty->createTemplate('bug.tpl', null, null, false);
}
echo '<br>' . (microtime(true) - $start) . '<br>';
echo '<br>' . memory_get_peak_usage();
