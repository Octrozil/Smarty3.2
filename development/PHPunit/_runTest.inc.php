<?php
/**
 * Smarty PHPunit test suite
 *
 * @package PHPunit
 * @author Uwe Tews
 */

require_once '../../lib/Smarty/Autoloader.php';
Smarty_Autoloader::$checkFile = true;
/**
 * class for running test suite
 */
class SmartyTests
{
    public static $smarty = null;
    public static $smartyBC = null;
    public static $smartyBC31 = null;

    protected static function _init($smarty)
    {
        $smarty->setTemplateDir('./templates/');
        $smarty->setCompileDir('./templates_c/');
        $smarty->setPluginsDir(Smarty::$_SMARTY_PLUGINS_DIR);
        $smarty->setCacheDir('./cache/');
        $smarty->setConfigDir('./configs/');
        $smarty->_tpl_vars = new Smarty_Variable_Scope($smarty, null, Smarty::IS_SMARTY, 'Smarty root');
        $smarty->template_functions = array();
        $smarty->force_compile = false;
        $smarty->force_cache = false;
        $smarty->auto_literal = true;
        $smarty->caching = false;
        $smarty->debugging = false;
        $smarty->registered_plugins = array();
        $smarty->default_plugin_handler_func = null;
        $smarty->registered_objects = array();
        $smarty->default_modifiers = array();
        $smarty->registered_filters = array();
        $smarty->autoload_filters = array();
        $smarty->escape_html = false;
        $smarty->use_sub_dirs = false;
        $smarty->config_overwrite = true;
        $smarty->config_booleanize = true;
        $smarty->config_read_hidden = true;
        $smarty->security_policy = null;
        $smarty->left_delimiter = '{';
        $smarty->right_delimiter = '}';
        $smarty->php_handling = Smarty::PHP_PASSTHRU;
        $smarty->enableSecurity();
        $smarty->error_reporting = null;
        $smarty->error_unassigned = Smarty::UNASSIGNED_NOTICE;
        $smarty->cache_locking = false;
        $smarty->cache_id = null;
        $smarty->compile_id = null;
        $smarty->caching_type = 'file';
        $smarty->compiled_type = 'file';
        $smarty->default_resource_type = 'file';
        $smarty->_smarty_extensions = array();
    }

    public static function init()
    {
        error_reporting(E_ALL | E_STRICT);
        self::_init(SmartyTests::$smarty);
        self::_init(SmartyTests::$smartyBC);
        self::_init(SmartyTests::$smartyBC31);
        Smarty::$resource_cache = array();
        Smarty::$_global_tpl_vars = new stdClass;
        Smarty::$_smarty_vars = array();
        SmartyTests::$smartyBC->registerPlugin('block', 'php', 'smarty_php_tag');
    }
}

SmartyTests::$smartyBC = new SmartyBC();
SmartyTests::$smartyBC31 = new SmartyBC31();
SmartyTests::$smarty = new Smarty();
