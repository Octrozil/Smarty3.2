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

require '../../distribution/libs/Smarty.class.php';
set_time_limit(1000);

$smarty = new Smarty;
$smarty->error_reporting = E_ALL + E_STRICT;
error_reporting(E_ALL + E_STRICT);
$smarty->plugins_dir[] = './plugins';
// $smarty->_parserdebug= true;
//$smarty->force_cache = true;
$smarty->setErrorReporting(E_ALL);
//$smarty->caching = true;
//$smarty->cache_lifetime = -1;
$smarty->debugging = true;
//$smarty->default_modifiers = array('escape:"htmlall"','strlen');
// $smarty->use_sub_dirs = true;
//$smarty->setCacheLifetime(10);
//$smarty->setCaching(2);
// $smarty->cache_modified_check = true;
//$smarty->merge_compiled_includes = true;
// $smarty->error_unassigned = true;
// $smarty->security=true;;
// $smarty->enableSecurity();
// $smarty->loadFilter('output', 'trimwhitespace');
// $smarty->loadFilter("variable", "htmlspecialchars");
// $smarty->loadFilter('pre', 'translate');
// $smarty->display('child.tpl');
// $smarty->clear_all_cache(time()-100);
// echo $smarty->clear_cache(null,'uwe');
// $smarty->compile_id = 'bar';
// $smarty->cache_id = 'uwe|tews';
// $smarty->left_delimiter = '[[';
// $smarty->right_delimiter = ']]';
if (true) {
    $sCacheId = '1_1';
    $start = _get_time();
    $oTemplate = $smarty->createTemplate('bug1.tpl', $sCacheId);
    $oTemplate->setCaching(2);
    $oTemplate->setCacheId($sCacheId);
    if (!$oTemplate->isCached()) {
        $oTemplate->assign('foo', 1);
        echo '<br>is false 1<br>';
    }
    try {
        $h = $oTemplate->fetch();
        echo $h;
    } catch (Exception $e) {
        while (1 < ob_get_level()) {
            ob_end_clean();
        }
// echo  'got Here'.$e->getMessage();
        //   print $e->getMessage();
    }
} else {
    $sCacheId = '1_2';
    $oTemplate = $smarty->createTemplate('bug1.tpl', $sCacheId);
    $oTemplate->setCaching(2);
    $oTemplate->setCacheId($sCacheId);
    if (!$oTemplate->isCached()) {
        $oTemplate->assign('foo', 2);
        echo '<br>is false 2<br>';
    }
    echo $oTemplate->fetch();
}

//$smarty->display ( 'bug1.tpl' );
echo '<br>' . (_get_time() - $start) . '<br>';
echo '<br>' . memory_get_peak_usage();
