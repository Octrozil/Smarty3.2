<?php
/**
 * Test script for extend
 * @author Uwe Tews
 * @package SmartyTestScripts
 */

require '../../distribution/libs/Smarty.class.php';

class Smarty_Inheritance_Test extends Smarty
{

    public function test1()
    {
        $this->display('user:1');
    }

    public function test2()
    {
        $this->display('extends:user:2|user:1');
    }

    public function user_source($tplId, &$tpl_source, &$smarty)
    {
        switch ($tplId) {
            case 2:
                $tpl_source = "Parent Content({block name='child_content'}No child content supplied{/block})";
                break;
            case 1:
                $tpl_source = "{extends file='user:2'}{block name='child_content'}Child Content{/block}";
                break;
        }

        return true;
    }

    public function user_timestamp($tplId, &$tpl_timestamp, &$smarty)
    {
        $tpl_timestamp = time();

        return true;
    }

    public function user_secure($tplId, &$smarty)
    {
        return true;
    }

    public function user_trusted($tplId, &$smarty)
    { /* not used for templates */
    }
}

$test = new Smarty_Inheritance_Test();
$test->register->resource
    (
        'user',
        array(array($test, 'user_source'),
            array($test, 'user_timestamp'),
            array($test, 'user_secure'),
            array($test, 'user_trusted'))
    );

$test->test1();
// -> Parent Content(Child Content)

$test->test2();
// -> Exception: Unable to load template extends 'user:2|user:1'
