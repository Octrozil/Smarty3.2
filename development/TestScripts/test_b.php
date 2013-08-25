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
require '../../distribution/libs/Smarty.class.php';

// set_time_limit(1000);
ini_set('pcre.backtrack_limit', -1);

class smartyTest
{
    private $smarty;

    public function __construct()
    {
        $this->smarty = new Smarty();
        $this->smarty->left_delimiter = "{{";
        $this->smarty->right_delimiter = "}}";
        $this->smarty->compile_check = true;
    }

    public function run()
    {
        //           $this->smarty->load_filter( � ) ;
        $this->smarty->configLoad('test.conf');
        $this->smarty->assign('foo', 'bar');
        $message = $this->smarty->fetch('bug.tpl');

        //code sending mail �
    }

    public function clean()
    {
        $this->smarty->clearAllAssign();
//            $this->smarty->clearAllCache();
//            $this->smarty->clearConfig();
    }
}

$test = new smartyTest();

for ($i = 0; $i < 10000; $i++) {
    $test->run();
    $test->clean();
    echo '<br>' . memory_get_peak_usage(true);
}
