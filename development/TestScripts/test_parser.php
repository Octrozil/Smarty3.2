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

function _get_time()
{
    $_mtime = microtime();
    $_mtime = explode(" ", $_mtime);

    return (double) ($_mtime[1]) + (double) ($_mtime[0]);
}

function default_variable($variable, $value)
{
    $value = "UNASSIGNED VARIABLE '{$variable}'";

    return true;
}

function default_config_variable($variable, $value)
{
    $value = "UNDEFINED CONFIG VARIABLE '{$variable}'";

    return true;
}

error_reporting(E_ALL | E_STRICT);
require_once '../../lib/Smarty/Autoloader.php';
//require('../../distribution/libs/SmartyBC.class.php');
ini_set('pcre.backtrack_limit', -1);
// ini_set('asp_tags','1');
$smarty = new Smarty;
//$smarty->caching_type = 'mysql';
$smarty->addPluginsDir('./plugins');
//$smarty->caching = true;
$smarty->cache_lifetime = 100000;
$smarty->error_reporting = E_ALL | E_STRICT;
if (isset($_POST['template'])) {
    $template = str_replace("\'", "\\'", $_POST['template']);
} else {
    $template = null;
}
$smarty->assign('template', $template, true);
$tp = $smarty->createTemplate('test_parser.tpl', $smarty);
$tp->display();

$smarty2 = new SmartyBC();
//$smarty2->registerDefaultVariableHandler("default_variable");
//$smarty2->registerDefaultConfigVariableHandler("default_config_variable");
$smarty2->addPluginsDir('./plugins');
$smarty2->addTemplateDir('../PHPunit/templates');
$smarty2->error_reporting = E_ALL | E_STRICT;
$smarty2->php_handling = Smarty::PHP_ALLOW;
$smarty2->debugging = true;
$smarty2->error_unassigned = Smarty::UNASSIGNED_NOTICE;
//$smarty2->configLoad('test.conf');
$smarty2->assign('foo', 'foo');
// $smarty2->auto_literal = false;
// $smarty2->loadFilter('variable','htmlspecialchars');
//$smarty2->loadFilter('pre','form');
// $smarty2->default_modifiers = array('escape:"htmlall"','strlen');
// $smarty2->left_delimiter = '<-';
// $smarty2->right_delimiter = '->';
$smarty2->escape_html = true;
//$smarty2->compile_check = false;
//$smarty2->merge_compiled_includes = true;
//$smarty2->enableSecurity();
//$smarty2->security_policy->allowed_tags=array('counter');
$tpl = $smarty2->createTemplate('eval:' . str_replace("\r", '', $template), null, null, $smarty2);
$tpl->assign('selected', '0');
if (isset($_POST['debug']) && $_POST['debug'][0] == 1) {
    $tpl->_parserdebug = true;
    $tpl->assign('selected', '1');
}
$compiler = Smarty_Compiler::load($tpl, $tpl->source);
echo '<pre><br><br>' . htmlentities($code = $compiler->compileTemplate()) . '</pre><br><br>';
$start = _get_time();
//$tpl->smarty->_parserdebug = false;
/**
$_template = $tpl;
eval('?>'.$code);
echo $_template->compiled->template_obj->_renderTemplate ($_template);
*/

echo '==== start output ====<br>';
$smarty2->display('eval:' . str_replace("\r", '', $template), null, null, $smarty2);
echo '<br>====end  output ====';
echo '<br><br>time: ' . (_get_time() - $start);
echo '<br>memory: ' . memory_get_peak_usage(true);
