<?php
/**
 * Test script for registered resources
 *
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

class My_Object
{
    public function meth1($params, $smarty_obj)
    {
        return 'this is my meth1';
    }

    public function meth2($params, $p2 = null)
    {
        return 'this is my meth2 ' . $params . $p2;
    }
}

$smarty = new Smarty;
$myobj = new My_Object;

$smarty->assign('obj', $myobj);
// registering the object (will be by reference)
//$smarty->registerObject('foobar',$myobj,null,true);
$smarty->register_object('foobar', $myobj, null, true);
$smarty->display('string:{foobar->meth1 a=hi c=ho}');
