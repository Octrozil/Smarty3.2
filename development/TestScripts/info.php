<?php
/**
 * Example Info
 * @package Example-application
 */
require '../../distribution/libs/Smarty.class.php';

ini_set('pcre.backtrack_limit', -1);

$smarty = new Smarty();
$smarty->setTemplateDir(array(
    'mine' => '../../my-templates',
    'default' => './templates',
));
$smarty->addPluginsDir('./plugins');
$smarty->registerPlugin('modifier', 'strlen', 'strlen');
$smarty->registerPlugin('modifier', 'strlen2', array('InfoFoo', 'strlen'));
//$smarty->registerPlugin('modifier', 'strlen3', function($string){ return strlen($string); });

$smarty->autoload_filters['output'] = array('trimwhitespace');

$smarty->registerFilter('pre', array('InfoFoo', 'prefilterThingie'));
//$smarty->registerFilter('pre', function(){ });
//$smarty->registerFilter('pre', function(){ });

$smarty->registerClass('Foo', 'InfoFoo');

class MySecurity extends Smarty_Security
{
}

$security = new MySecurity($smarty);
$security->streams[] = 'file2';
$smarty->enableSecurity($security);

class InfoFoo
{
    public static function strlen($string)
    {
        return strlen($string);
    }

    public static function prefilterThingie()
    {

    }

    public $isAttribute = true;

    public function isFunction()
    {

    }

    public function isBlock()
    {

    }
}

$smarty->right_delimiter = $smarty->left_delimiter;
$smarty->caching = 1;
$smarty->cache_lifetime = 336699;

$tpl = $smarty->createTemplate('eval:foobar');
$tpl->caching = 2;

echo $tpl->info();
