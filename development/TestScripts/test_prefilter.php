<?php
/**
 * Test script for registered resources
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;
error_reporting(E_ALL + E_STRICT);

function myprefilter($input)
{
    return '{$foo}' . $input;
}

$smarty->registerFilter(Smarty::FILTER_PRE, 'myprefilter');
$tpl = $smarty->createTemplate('eval:{" hello world"}');
$tpl->assign('foo', 'bar');
$smarty->display($tpl);
