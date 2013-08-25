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
//require('../../distribution/libs/Smarty.compressed.php');
// set_time_limit(1000);
//ini_set('pcre.backtrack_limit', -1);
$appp = new Smarty();
$appp->muteExpectedErrors();
$appp->php_handling = Smarty::PHP_REMOVE;
$appp->setErrorReporting(E_ALL + E_STRICT);
error_reporting(E_ALL + E_STRICT);
$appp->addPluginsDir('./plugins');
$appp->addPluginsDir("./../../distribution/demo/plugins/");
$appp->addTemplateDir('../PHPunit/templates');
$start = microtime(true);
//$appp->display('child_exception.tpl');
$appp->display('bug.tpl');
//$appp->display('extends:parent_exception.tpl|child_exception.tpl');
//if (!$appp->isCached('bug.tpl')) {
//    echo 'refresh';
//}
//$appp->display('extends:bug_parent.tpl|bug_child.tpl');
//$appp->display('bug_child.tpl');
//$appp->display('bug.tpl');
//$appp->display('template.tpl');
//$appp->display("string: {include './include.tpl'}");

//$appp->clearCache(null,'uwe');
//var_dump(apc_cache_info('user'));
echo '<br>memory ' . memory_get_usage();
echo '<br> memory peak ' . memory_get_peak_usage();
echo '<br>' . (microtime(true) - $start);
