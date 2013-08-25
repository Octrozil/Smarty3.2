<?php
/**
 * Test script compiler performance
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

$smarty = new Smarty;

$smarty->force_compile = true;
$smarty->debugging = true;

$tpl = $smarty->createTemplate('compiler.tpl');
$smarty->display($tpl);
