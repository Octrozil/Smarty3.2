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
require_once SMARTY_DIR . '../demo/plugins/cacheresource.memcache.php';
//require('../../distribution/libs/Smarty.compressed.php');
function my_plugin($params, $smarty)
{
    $smarty->assign('test', time(), TRUE);
}

// set_time_limit(1000);
//ini_set('pcre.backtrack_limit', -1);
$smarty = new Smarty;
$smarty->php_handling = Smarty::PHP_REMOVE;
$smarty->setErrorReporting(E_ALL + E_STRICT);
error_reporting(E_ALL + E_STRICT);
$smarty->addPluginsDir('./plugins');
$smarty->addTemplateDir('./templates/bug');
//$smarty->caching_type = 'memcache';
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
//$smarty->compile_check = false;
// $smarty->cache_modified_check = true;
//$smarty->merge_compiled_includes = true;
// $smarty->error_unassigned = true;
// $smarty->security=true;;
// $smarty->enableSecurity();
// $smarty->loadFilter('output', 'trimwhitespace');
// $smarty->loadFilter("variable", "htmlspecialchars");
// $smarty->loadFilter('pre', 'translate');
// $smarty->display('child.tpl');
//$smarty->clearAllCache();
// echo $smarty->clear_cache(null,'uwe');
// $smarty->compile_id = 'bar';
// $smarty->cache_id = 'uwe|tews';
// $smarty->left_delimiter = '[%';
// $smarty->right_delimiter = '%]';
//$smarty->use_sub_dirs = true;
//$smarty->enableSecurity();
//$smarty->security_policy->disabled_tags=array('cycle');
//$smarty->security_policy->allowed_tags=array('counter');
$smarty->registerPlugin("function", "val", "return_val", true, array('val'));
$smarty->setCacheLifetime(10000);
$smarty->setCaching(1);
$start = microtime(true);
if (!$smarty->isCached('bugh.tpl')) {
    $smarty->assign('data', 'HELLO');
}
$smarty->assign('dynamic', 'HELLOm', true);
$smarty->display('bugh.tpl');

function return_val($params, &$smarty)
{
    return $params['val'];
}

echo '<br>memory ' . memory_get_usage();
echo '<br>' . (microtime(true) - $start) . '<br>';
echo '<br>' . memory_get_peak_usage();
