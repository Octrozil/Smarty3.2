<?php
/**
 * Test script for registered resources
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

error_reporting(E_ALL | E_STRICT);

function smarty_function_asset($param, &$smarty)
{
    return 1;
}

$smarty = new Smarty;
$smarty->deprecation_notices = false;
$smarty->error_reporting = E_ALL | E_STRICT;
$smarty->register->templateFunction('asset', 'smarty_function_asset');
//$smarty->register_function('asset', 'smarty_function_asset');
$smarty->assign('x', 'barvalue');
$smarty->display('eval:{asset foo=\'par\' bar=$x}' . "\n" . '{asset foo=\'par\' bar=$x}');
