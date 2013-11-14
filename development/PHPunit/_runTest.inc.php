<?php
/**
 * Smarty PHPunit test suite
 *
 * @package PHPunit
 * @author Uwe Tews
 */
require_once '../../lib/SplClassLoader.php';
$classLoader = new SplClassLoader();
$classLoader->register();
/**
 * class for running test suite
 */
class SmartyTests
{   public static $cwd = null;
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
        $smarty->_registered = array();
        $smarty->default_plugin_handler_func = null;
        $smarty->default_modifiers = array();
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
        $smarty->_autoloaded = array();
        $smarty->enable_trace = false;
        $smarty->merge_compiled_includes = false;
        Smarty::$_trace_callbacks = array();
    }

    public static function init()
    {
        chdir(self::$cwd);
        error_reporting(E_ALL | E_STRICT);
        self::_init(SmartyTests::$smarty);
        self::_init(SmartyTests::$smartyBC);
        self::_init(SmartyTests::$smartyBC31);
        Smarty_Context::$_key_counter = 0;
        Smarty_Context::$_compiled_object_cache = array();
        Smarty_Context::$_cached_object_cache = array();
        Smarty::$_resource_cache = array();
        Smarty::$_context_cache = array();
        Smarty::$_resource_cache = array();
        Smarty::$_global_tpl_vars = new stdClass;
        Smarty::$_smarty_vars = array();
        SmartyTests::$smartyBC->registerPlugin('block', 'php', 'smarty_php_tag');
    }
}

SmartyTests::$cwd= getcwd();
SmartyTests::$smartyBC = new Smarty_Smarty2BC();
SmartyTests::$smartyBC31 = new Smarty_Smarty31BC();
SmartyTests::$smarty = new Smarty();
