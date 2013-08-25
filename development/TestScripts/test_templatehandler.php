<?php
/**
 * Test script for the default template handler
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = false;
$smarty->caching = true;
$smarty->cache_lifetime = 1000;
$smarty->registerDefaultTemplateHandler("template_handler");

$smarty->display('blabla.tpl');

function template_handler($resource_type, $resource_name, &$template_source, &$template_timestamp, &$tpl)
{
    $output = "Recsource $resource_name of type $resource_type not found";
    $template_source = $output;
    $template_timestamp = time();

    return true;
}
