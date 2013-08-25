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
function ErrorHandler($errno, $errstr)
{
    echo '<br>error ' . $errno . ' ' . $errstr . '<br>';
}

//set_error_handler('ErrorHandler');

require '../../distribution/libs/Smarty.class.php';
//require('../../distribution/libs/Smarty.compressed.php');
// set_time_limit(1000);
//ini_set('pcre.backtrack_limit', -1);
$smarty = new Smarty();
//Smarty::muteExpectedErrors();
$smarty->php_handling = Smarty::PHP_PASSTHRU;
$smarty->setErrorReporting(E_ALL + E_STRICT);
error_reporting(E_ALL + E_STRICT);
$smarty->addPluginsDir('./plugins');
$smarty->addPluginsDir("./../../distribution/demo/plugins/");
$smarty->addTemplateDir('../PHPunit/templates');
/*        $smarty->setTemplateDir( array(
            'root' => '../PHPunit/templates',
            '../PHPunit/templates_2',
            '../PHPunit/templates_3',
            '../PHPunit/templates_4',
        ));
*/
//$smarty->testInstall();
//$smarty->addTemplateDir('./templates/bug');
//$smarty->cache_dir=realpath($smarty->cache_dir);
//$smarty->caching_type = 'apc';
//$smarty->_parserdebug= true;
//$smarty->auto_literal = false;
//$smarty->force_cache = true;
//$smarty->force_compile = true;
//$smarty->compile_check = Smarty::COMPILECHECK_CACHEMISS;
// $smarty->cache_lifetime = -1;
//$smarty->debugging_ctrl = 'URL';
//$smarty->debug_tpl = 'file:' . SMARTY_DIR . 'dSmarty.tpl';
//$smarty->debugging = true;
// $smarty->default_modifiers = array('escape:"htmlall"','strlen');
// $smarty->use_sub_dirs = true;
// $smarty->setCacheLifetime(10);
$smarty->compile_check = false;
//$smarty->cache_modified_check = true;
//$smarty->merge_compiled_includes = true;
// $smarty->security=true;;
// $smarty->enableSecurity();
// $smarty->loadFilter('output', 'trimwhitespace');
// $smarty->loadFilter("variable", "htmlspecialchars");
// $smarty->loadFilter('pre', 'translate');
// $smarty->display('child.tpl');
//$smarty->clearAllCache();
// echo $smarty->clear_cache(null,'uwe');
// $smarty->compile_id = 'bar';
//$smarty->cache_id = 'uwe|tews';
// $smarty->left_delimiter = '[%';
// $smarty->right_delimiter = '%]';
//$smarty->use_sub_dirs = true;
$smarty->error_unassigned = Smarty::UNASSIGNED_EXCEPTION;
//$smarty->loadFilter('output', 'testuwe');
//$smarty->enableSecurity();
//$smarty->security_policy->disabled_tags=array('cycle');
//$smarty->security_policy->allowed_tags=array('counter');
//$smarty->cache_locking = true;
$smarty->locking_timeout = 10;
//$smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
//$smarty->assign('foo', false);
//$smarty->setCaching(1);
$smarty->setCacheLifetime(100000);
$start = microtime(true);
for ($i = 0; $i < 5000; $i++) {
    $smarty->fetch('include_normal.tpl');
    Smarty_Resource::$sources = array();
    Smarty_Compiled::$compileds = array();
    $smarty->_source_cache = array();
}
echo '<br> normal' . (microtime(true) - $start);
$start = microtime(true);
for ($i = 0; $i < 5000; $i++) {
    $smarty->fetch('include_inline.tpl');
    Smarty_Resource::$sources = array();
    Smarty_Compiled::$compileds = array();
    $smarty->_source_cache = array();
}
echo '<br> inline' . (microtime(true) - $start);
$start = microtime(true);
for ($i = 0; $i < 5000; $i++) {
    $smarty->fetch('include_import.tpl');
    Smarty_Resource::$sources = array();
    Smarty_Compiled::$compileds = array();
    $smarty->_source_cache = array();
}
echo '<br> import' . (microtime(true) - $start);
