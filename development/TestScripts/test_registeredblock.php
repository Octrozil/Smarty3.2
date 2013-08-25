<?php
/**
 * Test script for registered resources
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

function myblock($params, $content, &$smarty_tpl, &$repeat)
{
    global $loop;
    if (is_null($content)) {
        if (isset($params['count'])) {
            $loop = $params['count'];

            return;
        }
    }

    $loop--;

    if ($loop) $repeat = true;
    return $content . $loop;
}

class myblockclass
{
    public static function execute($params, $content, &$smarty_tpl, &$repeat)
    {
        global $loop;
        if (is_null($content)) {
            if (isset($params['count'])) {
                $loop = $params['count'];

                return;
            }
        }

        $loop--;

        if ($loop) $repeat = true;
        return $content . $loop;
    }
}

$smarty = new Smarty;
// $smarty->debugging = true;
//$smarty->caching = 1;
$smarty->cache_lifetime = 10;
$smarty->force_compile = true;
$smarty->assign('x', 3);
$smarty->assign('y', 30);
$smarty->assign('z', 300);
$smarty->register->block('testblock', 'myblock', false);
//$smarty->register->block('testblock', array('myblockclass','execute'), false);
$smarty->display('test_register_block.tpl');
