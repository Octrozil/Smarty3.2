<?php
/**
 * Test script for the {debug} tag
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

//$smarty->_parserdebug = true;
$smarty->setCompileDir('./template_c_tmp');
$smarty->compileAllTemplates('.tpl', true, 0, 100);
$smarty->compileAllConfig('.conf', true, 0, 10, 100);

echo '<br><br>Memory usage  ', memory_get_peak_usage(true);
