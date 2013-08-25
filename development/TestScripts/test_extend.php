<?php
/**
 * Test script for extend
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require_once '../../lib/Smarty/Autoloader.php';
function prefilter($content)
{
    return str_replace('%%%uwe%%', '%tews%', $content);
}

$smarty = new Smarty;
$smarty->addPluginsDir('./plugins');
$smarty->debugging = true;
//$smarty->force_compile = true;
//$smarty->caching = 1;
$smarty->cache_lifetime = 100;
$smarty->loadFilter('post', 'trimwhitespace');
if (!$smarty->isCached('test_block.tpl')) {
    $smarty->assign('foo', 'foo');
}
$smarty->assign('includename', 'time.tpl');
$smarty->assign('file', 'test_block_section.tpl');

$smarty->display('test_block.tpl');
