<?php
/**
 * Test script for the {debug} tag
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

$smarty = new Smarty;
// $smarty->force_compile = true;
function test($smarty)
{
    for ($i = 1; $i <= 10000; $i++) {
        $smarty->assign('akjkhjhkhk' . $i, 'akjkhjhkhk' . $i);
        $smarty->append('akjkhjhkhk' . $i, 'akjkhjhkhk' . $i);
//$smarty->tpl_vars['a'.$i]=$i;
    }
}

$start = _get_time();
test($smarty);
$smarty->display('bug.tpl');
echo _get_time() - $start . '<br>';
echo '<br>' . memory_get_peak_usage();
