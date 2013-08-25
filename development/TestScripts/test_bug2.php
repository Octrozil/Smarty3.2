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

$smarty = new Smarty;
$smarty->error_reporting = E_ALL + E_STRICT;
$smarty->plugins_dir[] = './plugins';
// $smarty->force_compile = true;
$smarty->debugging = true;
// $smarty->use_sub_dirs = true;
//$smarty->caching = 1;
// $smarty->enableCaching();
$smarty->cache_lifetime = 100000;
// $smarty->merge_compiled_includes = true;
// $smarty->error_unassigned = true;
$smarty->display('bugclass.tpl');
// $smarty->display('child.tpl');
class CustomClass
{
    public function serveCustomPlugin($plugin)
    {
        // inhere we would extend to another class that hold said plugin
        return new $plugin;
    }
}

class Pluginnamed_whatever
{
    public function runplugin($params = array(), $smarty)
    {
        // for the sake of this example im keeping it simple
        $smarty->assign('blah', 'neato');

        return $smarty->fetch('thepluginsfile.html');
    }
}

class Pluginnamed_whatever2
{
    public function runplugin($params = array(), $smarty)
    {
        // for the sake of this example im keeping it simple
        $smarty->assign('blah', 'neato');

        return $smarty->fetch('thepluginsfile1.html');
    }
}

class Pluginnamed_whatever3
{
    public function runplugin($params = array(), $smarty)
    {
        // for the sake of this example im keeping it simple
        $smarty->assign('blah', 'neato');

        return $smarty->fetch('thepluginsfile2.html');
    }
}

class Pluginnamed_whatever4
{
    public function runplugin($params = array(), $smarty)
    {
        // for the sake of this example im keeping it simple
        $smarty->assign('blah', 'neato');

        return $smarty->fetch('thepluginsfile3.html');
    }
}
