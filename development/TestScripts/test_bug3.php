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
$smarty->php_handling = Smarty::PHP_REMOVE;
$smarty->setErrorReporting(E_ALL + E_STRICT);
error_reporting(E_ALL + E_STRICT);
$smarty->plugins_dir[] = './plugins';
// $smarty->_parserdebug= true;
//$smarty->auto_literal = false;
//$smarty->force_cache = true;
//$smarty->force_compile = true;
//$smarty->compile_check = false;
// $smarty->cache_lifetime = -1;
//$smarty->debugging = true;
// $smarty->default_modifiers = array('escape:"htmlall"','strlen');
// $smarty->use_sub_dirs = true;
// $smarty->setCacheLifetime(10);
// $smarty->setCaching(2);
// $smarty->cache_modified_check = true;
//$smarty->merge_compiled_includes = true;
// $smarty->error_unassigned = true;
// $smarty->security=true;;
// $smarty->enableSecurity();
// $smarty->loadFilter('output', 'trimwhitespace');
// $smarty->loadFilter("variable", "htmlspecialchars");
// $smarty->loadFilter('pre', 'translate');
// $smarty->display('child.tpl');
//$smarty->clearAllCache(time()-100);
// echo $smarty->clear_cache(null,'uwe');
// $smarty->compile_id = 'bar';
// $smarty->cache_id = 'uwe|tews';
// $smarty->left_delimiter = '[%';
// $smarty->right_delimiter = '%]';
$smarty->assign('foo', 'bar');
$smarty->setCacheLifetime(10);
$smarty->setCaching(1);
$smarty->registerPlugin('function', 'nonCachingFunction',
    'smarty_function_nonCachingFunction', false, array('value',
        'variable'));
function smarty_function_nonCachingFunction(array $params,
                                            Smarty_Internal_Template $template)
{
    var_dump(date('c'), $params);

}

if (!$smarty->isCached('demo.tpl')) {
    $smarty->assign('cached', date('c'));
}

$smarty->display('demo.tpl');
echo '<br>' . memory_get_peak_usage();
