<?php

/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty.class.php
 * SVN:         $Id: Smarty.class.php 4745 2013-06-17 18:27:16Z Uwe.Tews@googlemail.com $
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com
 *
 * @link http://www.smarty.net/
 * @copyright 2008 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 * @author Rodney Rehm
 * @package Smarty
 * @version 3.2-DEV
 */

/**
 * This is the main Smarty class
 * @package Smarty
 */
class Smarty extends Smarty_Variable_Methods
{
    /*     * #@+
    * constant definitions
    */

    /**
     * smarty version
     */
    const SMARTY_VERSION = 'Smarty 3.2-DEV';

    /**
     * define variable scopes
     */
    const SCOPE_LOCAL = 0;
    const SCOPE_PARENT = 1;
    const SCOPE_ROOT = 2;
    const SCOPE_GLOBAL = 3;

    /**
     * define object and variable scope type
     */
    const IS_SMARTY = 0;
    const IS_SMARTY_TPL_CLONE = 1;
    const IS_TEMPLATE = 2;
    const IS_DATA = 3;
    const IS_CONFIG = 4;

    /**
     * define caching modes
     */
    const CACHING_OFF = 0;
    const CACHING_LIFETIME_CURRENT = 1;
    const CACHING_LIFETIME_SAVED = 2;
    const CACHING_NOCACHE_CODE = 3; // create nocache code but no cache file
    /**
     * define constant for clearing cache files be saved expiration datees
     */
    const CLEAR_EXPIRED = -1;
    /**
     * define compile check modes
     */
    const COMPILECHECK_OFF = 0;
    const COMPILECHECK_ON = 1;
    const COMPILECHECK_CACHEMISS = 2;
    /**
     * modes for handling of "<?php ... ?>" tags in templates.
     */
    const PHP_PASSTHRU = 0; //-> print tags as plain text
    const PHP_QUOTE = 1; //-> escape tags as entities
    const PHP_REMOVE = 2; //-> escape tags as entities
    const PHP_ALLOW = 3; //-> escape tags as entities
    /**
     * filter types
     */
    const FILTER_POST = 'post';
    const FILTER_PRE = 'pre';
    const FILTER_OUTPUT = 'output';
    const FILTER_VARIABLE = 'variable';
    /**
     * plugin types
     */
    const PLUGIN_FUNCTION = 'function';
    const PLUGIN_BLOCK = 'block';
    const PLUGIN_COMPILER = 'compiler';
    const PLUGIN_MODIFIER = 'modifier';
    const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';
    /**
     * unassigend template variable handling
     */
    const UNASSIGNED_IGNORE = 0;
    const UNASSIGNED_NOTICE = 1;
    const UNASSIGNED_EXCEPTION = 2;

    /**
     * define resource group
     */
    const SOURCE = 0;
    const COMPILED = 1;
    const CACHE = 2;

    /*     * #@- */

    /**
     * assigned template vars
     * @internal
     * @var Smarty_Variable_Scope
     */
    public $_tpl_vars = null;

    /**
     * Declare the type template variable storage
     *
     * @internal
     * @var Smarty::IS_SMARTY | IS_SMARTY_TPL_CLONE
     */
    public $_usage = self::IS_SMARTY;

    /**
     * assigned global tpl vars
     * @internal
     * @var stdClass
     */
    public static $_global_tpl_vars = null;

    /**
     * error handler returned by set_error_hanlder() in Smarty::muteExpectedErrors()
     * @internal
     */
    public static $_previous_error_handler = null;

    /**
     * contains directories outside of SMARTY_DIR that are to be muted by muteExpectedErrors()
     * @internal
     * @var array
     */
    public static $_muted_directories = array();

    /**
     * contains trace callbacks to invoke on events
     * @internal
     * @var array
     */
    public static $_trace_callbacks = array();

    /**
     * Flag denoting if Multibyte String functions are available
     * @internal
     * @var bool
     */
    public static $_MBSTRING = false;

    /**
     * The character set to adhere to (e.g. "UTF-8")
     * @var string
     */
    public static $_CHARSET = "UTF-8";

    /**
     * The date format to be used internally
     * (accepts date() and strftime())
     * @var string
     */
    public static $_DATE_FORMAT = '%b %e, %Y';

    /**
     * Flag denoting if PCRE should run in UTF-8 mode
     * @internal
     * @var string
     */
    public static $_UTF8_MODIFIER = 'u';

    /**
     * Folder of Smarty build in plugins
     * @internal
     * @var string
     */
    public static $_SMARTY_PLUGINS_DIR = '';
    /** #@+
     * variables
     */

    /**
     * auto literal on delimiters with whitspace
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.auto.literal.tpl
     */
    public $auto_literal = true;

    /**
     * display error on not assigned variables
     * @var integer
     * @link <missing>
     * @uses UNASSIGNED_IGNORE as possible value
     * @uses UNASSIGNED_NOTICE as possible value
     * @uses UNASSIGNED_EXCEPTION as possible value
     */
    public $error_unassigned = self::UNASSIGNED_IGNORE;

    /**
     * look up relative filepaths in include_path
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.use.include.path.tpl
     */
    public $use_include_path = false;

    /**
     * template directory
     * @var array
     * @internal
     * @link http://www.smarty.net/docs/en/variable.template.dir.tpl
     */
    private $_template_dir = array();

    /**
     * joined template directory string used in cache keys
     * @var string
     * @internal
     */
    public $_joined_template_dir = null;

    /**
     * config directory
     * @var array
     * @internal
     * @link http://www.smarty.net/docs/en/variable.fooobar.tpl
     */
    private $_config_dir = array();

    /**
     * joined config directory string used in cache keys
     * @var string
     * @internal
     */
    public $_joined_config_dir = null;

    /**
     * compile directory
     * @var string
     * @internal
     * @link http://www.smarty.net/docs/en/variable.compile.dir.tpl
     */
    private $_compile_dir = '';

    /**
     * plugins directory
     * @var array
     * @internal
     * @link http://www.smarty.net/docs/en/variable.plugins.dir.tpl
     */
    private $_plugins_dir = array();

    /**
     * cache directory
     * @var string
     * @internal
     * @link http://www.smarty.net/docs/en/variable.cache.dir.tpl
     */
    private $_cache_dir = '';

    /**
     * disable core plugins in {@link loadPlugin()}
     * @var boolean
     * @link <missing>
     */
    public $disable_core_plugins = false;

    /**
     * force template compiling?
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.force.compile.tpl
     */
    public $force_compile = false;

    /**
     * check template for modifications?
     * @var int
     * @link http://www.smarty.net/docs/en/variable.compile.check.tpl
     * @uses COMPILECHECK_OFF as possible value
     * @uses COMPILECHECK_ON as possible value
     * @uses COMPILECHECK_CACHEMISS as possible value
     */
    public $compile_check = self::COMPILECHECK_ON;

    /**
     * developer mode
     *
     * @var bool
     */
    public $developer_mode = false;

    /**
     * enable trace back callback
     *
     * @var bool
     */
    public $enable_trace = false;

    /**
     * use sub dirs for compiled/cached files?
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.use.sub.dirs.tpl
     */
    public $use_sub_dirs = false;

    /**
     * allow ambiguous resources (that are made unique by the resource handler)
     * @var boolean
     */
    public $allow_ambiguous_resources = false;

    /*
    * caching enabled
    * @var integer
    * @link http://www.smarty.net/docs/en/variable.caching.tpl
    * @uses CACHING_OFF as possible value
    * @uses CACHING_LIFETIME_CURRENT as possible value
    * @uses CACHING_LIFETIME_SAVED as possible value
    */
    public $caching = self::CACHING_OFF;

    /**
     * merge compiled includes
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.merge.compiled.includes.tpl
     */
    public $merge_compiled_includes = false;

    /**
     * cache lifetime in seconds
     * @var integer
     * @link http://www.smarty.net/docs/en/variable.cache.lifetime.tpl
     */
    public $cache_lifetime = 3600;

    /**
     * force cache file creation
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.force.cache.tpl
     */
    public $force_cache = false;

    /**
     * Set this if you want different sets of cache files for the same
     * templates.
     * @var string
     * @link http://www.smarty.net/docs/en/variable.cache.id.tpl
     */
    public $cache_id = null;

    /**
     * Set this if you want different sets of compiled files for the same
     * templates.
     * @var string
     * @link http://www.smarty.net/docs/en/variable.compile.id.tpl
     */
    public $compile_id = null;

    /**
     * Save last setting of compile_id
     * @var string
     */
    public $_tpl_old_compile_id = null;

    /**
     * template left-delimiter
     * @var string
     * @link http://www.smarty.net/docs/en/variable.left.delimiter.tpl
     */
    public $left_delimiter = "{";

    /**
     * template right-delimiter
     * @var string
     * @link http://www.smarty.net/docs/en/variable.right.delimiter.tpl
     */
    public $right_delimiter = "}";

    /**
     * default template handler
     * @var callable
     * @link http://www.smarty.net/docs/en/variable.default.template.handler.func.tpl
     */
    public $default_template_handler_func = null;

    /**
     * default config handler
     * @var callable
     * @link http://www.smarty.net/docs/en/variable.default.config.handler.func.tpl
     */
    public $default_config_handler_func = null;

    /**
     * default plugin handler
     * @var callable
     * @link <missing>
     */
    public $default_plugin_handler_func = null;

    /**
     * default variable handler
     * @var callable
     * @link <missing>
     */
    public $default_variable_handler_func = null;

    /**
     * default config variable handler
     * @var callable
     * @link <missing>
     */
    public $default_config_variable_handler_func = null;


    /*     * #@+
    * security
    */

    /**
     * class name
     *
     * This should be instance of Smarty_Security.
     * @var string
     * @see Smarty_Security
     * @link <missing>
     */
    public $security_class = 'Smarty_Security';

    /**
     * implementation of security class
     * @var Smarty_Security
     * @see Smarty_Security
     * @link <missing>
     */
    public $security_policy = null;

    /**
     * controls handling of PHP-blocks
     * @var integer
     * @link http://www.smarty.net/docs/en/variable.php.handling.tpl
     * @uses PHP_PASSTHRU as possible value
     * @uses PHP_QUOTE as possible value
     * @uses PHP_REMOVE as possible value
     * @uses PHP_ALLOW as possible value
     */
    public $php_handling = self::PHP_PASSTHRU;

    /**
     * controls if the php template file resource is allowed
     * @var boolean
     * @link http://www.smarty.net/docs/en/api.variables.tpl#variable.allow.php.templates
     */
    public $allow_php_templates = false;

    /**
     * Should compiled-templates be prevented from being called directly?
     *
     * {@internal
     * Currently used by Smarty_Internal_Template_ only.
     * }}
     *
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.direct.access.security.tpl
     */
    public $direct_access_security = true;
    /*     * #@- */

    /**
     * debug mode
     *
     * Setting this to true enables the debug-console.
     *
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.debugging.tpl
     */
    public $debugging = false;

    /**
     * This determines if debugging is enable-able from the browser.
     * <ul>
     *  <li>NONE => no debugging control allowed</li>
     *  <li>URL => enable debugging when SMARTY_DEBUG is found in the URL.</li>
     * </ul>
     * @var string
     * @link http://www.smarty.net/docs/en/variable.debugging.ctrl.tpl
     */
    public $debugging_ctrl = 'NONE';

    /**
     * Name of debugging URL-param.
     * Only used when $debugging_ctrl is set to 'URL'.
     * The name of the URL-parameter that activates debugging.
     * @var string
     * @link http://www.smarty.net/docs/en/variable.smarty.debug.id.tpl
     */
    public $smarty_debug_id = 'SMARTY_DEBUG';

    /**
     * Path of debug template.
     * @var string
     * @link http://www.smarty.net/docs/en/variable.debugtpl_obj.tpl
     */
    public $debug_tpl = null;

    /**
     * Path of error template.
     * @var string
     */
    public $error_tpl = null;

    /**
     * enable error processing
     * @var boolean
     */
    public $error_processing = true;

    /**
     * When set, smarty uses this value as error_reporting-level.
     * @var integer
     * @link http://www.smarty.net/docs/en/variable.error.reporting.tpl
     */
    public $error_reporting = null;

    /**
     * Internal flag for getTags()
     * @var boolean
     * @internal
     */
    public $get_used_tags = false;

    /*     * #@+
    * config var settings
    */

    /**
     * Controls whether variables with the same name overwrite each other.
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.config.overwrite.tpl
     */
    public $config_overwrite = true;

    /**
     * Controls whether config values of on/true/yes and off/false/no get converted to boolean.
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.config.booleanize.tpl
     */
    public $config_booleanize = true;

    /**
     * Controls whether hidden config sections/vars are read from the file.
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.config.read.hidden.tpl
     */
    public $config_read_hidden = false;

    /*     * #@- */

    /*     * #@+
    * resource locking
    */

    /**
     * locking concurrent compiles
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.compile.locking.tpl
     */
    public $compile_locking = true;

    /**
     * Controls whether cache resources should emply locking mechanism
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.cache.locking.tpl
     */
    public $cache_locking = false;

    /**
     * seconds to wait for acquiring a lock before ignoring the write lock
     * @var float
     * @link http://www.smarty.net/docs/en/variable.locking.timeout.tpl
     */
    public $locking_timeout = 10;

    /*     * #@- */

    /**
     * global template functions
     * @var array
     * @internal
     */
    public $template_functions = array();

    /**
     * resource type used if none given
     * Must be an valid key of $registered_resources.
     * @var string
     * @link http://www.smarty.net/docs/en/variable.default.resource.type.tpl
     */
    public $default_resource_type = 'file';

    /**
     * caching type
     * Must be an element of $cache_resource_types.
     * @var string
     * @link http://www.smarty.net/docs/en/variable.caching.type.tpl
     */
    public $caching_type = 'file';

    /**
     * compiled type
     * Must be an element of $cache_resource_types.
     * @var string
     * @link http://www.smarty.net/docs/en/variable.caching.type.tpl
     */
    public $compiled_type = 'file';

    /**
     * internal config properties
     * @var array
     * @internal
     */
    public $properties = array();

    /**
     * config type
     * @var string
     * @link http://www.smarty.net/docs/en/variable.default.config.type.tpl
     */
    public $default_config_type = 'file';

    /**
     * Template compiler class
     * @var string
     */
    public $template_compiler_class = 'Smarty_Compiler_Template_Php_Compiler';

    /**
     * Template lexer class
     * @var string
     */
    public $template_lexer_class = 'Smarty_Compiler_Template_Lexer';

    /**
     * Template parser class
     * @var string
     */
    public $template_parser_class = 'Smarty_Compiler_Template_Php_Parser';

    /**
     * Config compiler class
     * @var string
     */
    public $config_compiler_class = 'Smarty_Compiler_Config_Compiler';

    /**
     * Config lexer class
     * @var string
     */
    public $config_lexer_class = 'Smarty_Compiler_Config_Compiler';

    /**
     * Config parser class
     * @var string
     */
    public $config_parser_class = 'Smarty_Compiler_Config_Compiler';

    /**
     * cached template objects
     * @var array
     * @internal
     */
    public static $template_cache = array();

    /**
     * check If-Modified-Since headers
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.cache.modified.check.tpl
     */
    public $cache_modified_check = false;

    /**
     * registered plugins
     * @var array
     * @internal
     */
    public $registered_plugins = array();

    /**
     * plugin search order
     * @var array
     * @link <missing>
     */
    public $plugin_search_order = array('function', 'block', 'compiler', 'class');

    /**
     * registered objects
     * @var array
     * @internal
     */
    public $registered_objects = array();

    /**
     * registered filters
     * @var array
     * @internal
     */
    public $registered_filters = array();

    /**
     * registered classes
     * @var array
     * @internal
     */
    public $registered_classes = array();

    /**
     * registered resources
     * @var array
     * @internal
     */
    public $registered_resources = array();

    /**
     * autoload filter
     * @var array
     * @link http://www.smarty.net/docs/en/variable.autoload.filters.tpl
     */
    public $autoload_filters = array();

    /**
     * default modifier
     * @var array
     * @link http://www.smarty.net/docs/en/variable.default.modifiers.tpl
     */
    public $default_modifiers = array();

    /**
     * autoescape variable output
     * @var boolean
     * @link http://www.smarty.net/docs/en/variable.escape.html.tpl
     */
    public $escape_html = false;

    /**
     * global internal smarty vars
     * @var array
     */
    public static $_smarty_vars = array();

    /**
     * start time for execution time calculation
     * @var integer
     * @internal
     */
    public $start_time = 0;

    /**
     * default file permissions (octal)
     * @var integer
     * @internal
     */
    public $_file_perms = 0644;

    /**
     * default dir permissions (octal)
     * @var integer
     * @internal
     */
    public $_dir_perms = 0771;

    /**
     * block tag hierarchy
     * @var array
     * @internal
     */
    public $_tag_stack = array();

    /**
     * required by the compiler for BC
     * @var string
     * @internal
     */
    public $_current_file = null;

    /**
     * internal flag to enable parser debugging
     * @var boolean
     * @internal
     */
    public $_parserdebug = false;

    /*     * #@- */

    /*     * #@+
    * template properties
    */

    /**
     * individually cached subtemplates
     * @var array
     */
    public $cached_subtemplates = array();

    /**
     * Template resource
     * @var string
     * @internal
     */
    public $template_resource = null;

    /**
     * flag set when nocache code sections are executed
     * @var boolean
     * @internal
     */
    public $is_nocache = false;

    /**
     * root template of hierarchy
     *
     * @var Smarty
     */
    public $rootTemplate = null;

    /**
     * {block} tags of this template
     *
     * @var array
     * @internal
     */
    public $block = array();

    /**
     * variable filters
     * @var array
     * @internal
     */
    public $variable_filters = array();

    /**
     * optional log of tag/attributes
     * @var array
     * @internal
     */
    public $used_tags = array();

    /**
     * internal flag to allow relative path in child template blocks
     * @var boolean
     * @internal
     */
    public $allow_relative_path = false;

    /**
     * flag this is inheritance child template
     *
     * @var bool
     */
    public $is_inheritance_child = false;

    /**
     * Pointer to subtemplate with template functions
     * @var object Smarty_Template
     * @internal
     */
    public $template_function_chain = null;

    /**
     * $compiletime_options
     * value is computed of the compiletime options relevant for config files
     *      $config_read_hidden
     *      $config_booleanize
     *      $config_overwrite
     *
     * @var int
     */
    public $compiletime_options = 0;

    /**
     * loaded Smarty extension objects
     *
     * @internal
     * @var array
     */
    public $_loaded_extensions = array();


    /**
     * resource handler cache
     *
     * @var array
     * @internal
     */
    public static $resource_cache = array();

    /**
     * source object cache
     *
     * @var array
     * @internal
     */
    public static $source_cache = array();


    /*     * #@- */

    /**
     * Initialize new Smarty object
     *
     */
    public function __construct()
    {
        // create variable scope for Smarty root
        $this->_tpl_vars = new Smarty_Variable_Scope();
        self::$_global_tpl_vars = new stdClass;
        // PHP options
        if (is_callable('mb_internal_encoding')) {
            mb_internal_encoding(self::$_CHARSET);
            self:: $_MBSTRING = true;
        }
        $this->start_time = microtime(true);
        // set default dirs
        if (empty(Smarty::$_SMARTY_PLUGINS_DIR)) {
            Smarty::$_SMARTY_PLUGINS_DIR = dirname(__FILE__) . '/Plugins/';
        }
        $this->setTemplateDir('./templates/')
            ->setCompileDir('./templates_c/')
            // this plugins dir should not be set at start up to be able to disable with
            // $smarty->disable_core_plugins = true
            //->setPluginsDir(Smarty::$_SMARTY_PLUGINS_DIR)
            ->setCacheDir('./cache/')
            ->setConfigDir('./configs/');

        $this->debug_tpl = 'file:' . dirname(__FILE__) . '/debug.tpl';
        $this->error_tpl = 'file:' . dirname(__FILE__) . '/error.tpl';
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $this->assignGlobal('SCRIPT_NAME', $_SERVER['SCRIPT_NAME']);
        }
    }

    /**
     * fetches a rendered Smarty template
     *
     * @api
     * @param  string $template         the resource handle of the template file or template object
     * @param  mixed $cache_id         cache id to be used with this template
     * @param  mixed $compile_id       compile id to be used with this template
     * @param  Smarty $parent           next higher level of Smarty variables
     * @param  bool $display          true: display, false: fetch
     * @param  bool $no_output_filter if true do not run output filter
     * @param  null $data
     * @param  int $scope_type
     * @param  null $caching
     * @param  null|int $cache_lifetime
     * @param  null| Smarty_Variable_Scope $_scope
     * @throws Smarty_Exception
     * @throws Smarty_Exception_Runtime
     * @return string                      rendered template output
     */

    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $no_output_filter = false, $data = null, $scope_type = Smarty::SCOPE_LOCAL, $caching = null, $cache_lifetime = null, $_scope = null)
    {
        if ($template === null && ($this->_usage == self::IS_SMARTY_TPL_CLONE || $this->_usage == self::IS_CONFIG)) {
            $template = $this;
        }
        if (!empty($cache_id) && is_object($cache_id)) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($parent === null && (!($this->_usage == self::IS_SMARTY_TPL_CLONE || $this->_usage == self::IS_CONFIG) || is_string($template))) {
            $parent = $this;
        }

        if (is_object($template)) {
            // get source from template clone
            $source = $template->source;
            $tpl_obj = $template;
        } else {
            //get source object from cache  or create new one
            $source = $this->_getSourceObject($template);
            // checks if source exists
            if (!$source->exists) {
                throw new Smarty_Exception_SourceNotFound($source->type, $source->name);
            }
            $tpl_obj = $this;
        }

        if (isset($tpl_obj->error_reporting)) {
            $_smarty_old_error_level = error_reporting($tpl_obj->error_reporting);
        }
        // check URL debugging control
        if (!$tpl_obj->debugging && $tpl_obj->debugging_ctrl == 'URL') {
            Smarty_Debug::checkURLDebug($tpl_obj);
        }

        // disable caching for evaluated code
        $caching = $source->recompiled ? false : $caching ? $caching : $tpl_obj->caching;
        $compile_id = isset($compile_id) ? $compile_id : $tpl_obj->compile_id;
        $cache_id = isset($cache_id) ? $cache_id : $tpl_obj->cache_id;

        if ($caching == self::CACHING_LIFETIME_CURRENT || $caching == self::CACHING_LIFETIME_SAVED) {
            $browser_cache_valid = false;
            $_output = $source->_getRenderedTemplate($tpl_obj, self::CACHE, $parent, $compile_id, $cache_id, $caching, $data, $scope_type, $no_output_filter, $display);
            if ($_output === true) {
                $browser_cache_valid = true;
            }
        } else {
            $_output = $source->_getRenderedTemplate($tpl_obj, self::COMPILED, $parent, $compile_id, $cache_id, $caching, $data, $scope_type, $no_output_filter, $display);
        }
        if (isset($tpl_obj->error_reporting)) {
            error_reporting($_smarty_old_error_level);
        }

        // display or fetch
        if ($display) {
            if ($tpl_obj->caching && $tpl_obj->cache_modified_check) {
                if (!$browser_cache_valid) {
                    switch (PHP_SAPI) {
                        case 'cli':
                            if ( /* ^phpunit */
                            !empty($_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS']) /* phpunit$ */
                            ) {
                                $_SERVER['SMARTY_PHPUNIT_HEADERS'][] = 'Last-Modified: ' . gmdate('D, d M Y H:i:s', $tpl_obj->cached->timestamp) . ' GMT';
                            }
                            break;

                        default:
                            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
                            break;
                    }
                    echo $_output;
                }
            } else {
                echo $_output;
            }
            // debug output
            if ($tpl_obj->debugging) {
                Smarty_Debug::display_debug($tpl_obj);
            }

            return;
        } else {
            // return output on fetch
            return $_output;
        }
    }

    /**
     * displays a Smarty template
     *
     * @api
     * @param string $template   the resource handle of the template file or template object
     * @param mixed $cache_id   cache id to be used with this template
     * @param mixed $compile_id compile id to be used with this template
     * @param object $parent     next higher level of Smarty variables
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        // display template
        $this->fetch($template, $cache_id, $compile_id, $parent, true);
    }

    /**
     * creates a template object
     *
     * @api
     * @param  string $template_resource the resource handle of the template file
     * @param  mixed $cache_id          cache id to be used with this template
     * @param  mixed $compile_id        compile id to be used with this template
     * @param  object $parent            next higher level of Smarty variables
     * @param  boolean $isConfig         flag that template will be for config files
     * @throws Smarty_Exception
     * @return Smarty           template object
     */
    public function createTemplate($template_resource, $cache_id = null, $compile_id = null, $parent = null, $isConfig = false)
    {
        if (!is_string($template_resource)) {
            throw new Smarty_Exception('err6', $this);
        }
        if (!empty($cache_id) && (is_object($cache_id) || is_array($cache_id))) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if (!empty($parent) && is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }
        $tpl_obj = clone $this;
        $tpl_obj->_usage = self::IS_SMARTY_TPL_CLONE;
        $tpl_obj->parent = $parent;
        if (isset($cache_id)) {
            $tpl_obj->cache_id = $cache_id;
        }
        if (isset($compile_id)) {
            $tpl_obj->compile_id = $compile_id;
        }
        $source = $this->_getSourceObject($template_resource, $isConfig);
        // checks if source exists
        if (!$source->exists) {
            throw new Smarty_Exception_SourceNotFound($source->type, $source->name);
        }
        $tpl_obj->source = $source;
        $tpl_obj->_tpl_vars = new Smarty_Variable_Scope();
        if (isset($data)) {
            foreach ($data as $varname => $value) {
                $tpl_obj->_tpl_vars->$varname = new Smarty_Variable($value);
            }
        }

        return $tpl_obj;
    }

    /**
     * creates a data object
     *
     * @api
     * @param  Smarty|Smarty_Data|Smarty_Variable_Scope $parent     next higher level of Smarty variables
     * @param  string $name optional name of Smarty_Data object
     * @return object                                   Smarty_Data
     */
    public function createData($parent = null, $name = 'Data unnamed')
    {
        return new Smarty_Data($this, $parent, $name);
    }

    /**
     * Check if a template resource exists
     *
     * @api
     * @param  string $template_resource template name
     * @param  bool $isConfig set true if looking for a config file
     * @return boolean status
     */
    public function templateExists($template_resource, $isConfig = false)
    {
        $source = $this->_getSourceObject($template_resource, $isConfig);
        return $source->exists;
    }


    /**
     * Loads security class and enables security
     *
     * @api
     * @param  string|Smarty_Security $security_class if a string is used, it must be class-name
     * @return Smarty                 current Smarty instance for chaining
     * @throws Smarty_Exception       when an invalid class name is provided
     */
    public function enableSecurity($security_class = null)
    {
        Smarty_Security::enableSecurity($this, $security_class);

        return $this;
    }

    /**
     * Disable security
     *
     * @api
     * @return Smarty current Smarty instance for chaining
     */
    public function disableSecurity()
    {
        $this->security_policy = null;

        return $this;
    }

    /**
     * Set template directory
     *
     * @api
     * @param  string|array $template_dir directory(s) of template sources
     * @return Smarty       current Smarty instance for chaining
     */
    public function setTemplateDir($template_dir)
    {
        $this->_setDir($template_dir, '_template_dir');
        return $this;
    }

    /**
     * Add template directory(s)
     *
     * @api
     * @param  string|array $template_dir directory(s) of template sources
     * @param  string $key          of the array element to assign the template dir to
     * @return Smarty       current Smarty instance for chaining
     */
    public function addTemplateDir($template_dir, $key = null)
    {
        $this->_addDir($template_dir, $key, '_template_dir');
        return $this;
    }

    /**
     * Get template directories
     *
     * @api
     * @param  mixed $index of directory to get, null to get all
     * @return array|string list of template directories, or directory of $index
     */
    public function getTemplateDir($index = null)
    {
        if ($index !== null) {
            return isset($this->_template_dir[$index]) ? $this->_template_dir[$index] : null;
        }

        return (array)$this->_template_dir;
    }

    /**
     * Set config directory
     *
     * @api
     * @param  array|string $config_dir directory(s) of configuration sources
     * @return Smarty       current Smarty instance for chaining
     */
    public function setConfigDir($config_dir)
    {
        $this->_setDir($config_dir, '_config_dir');
        return $this;
    }

    /**
     * Add config directory(s)
     *
     * @api
     * @param  string|array $config_dir directory(s) of config sources
     * @param  string $key        of the array element to assign the config dir to
     * @return Smarty       current Smarty instance for chaining
     */
    public function addConfigDir($config_dir, $key = null)
    {
        $this->_addDir($config_dir, $key, '_config_dir');
        return $this;
    }

    /**
     * Get config directory
     *
     * @api
     * @param  mixed $index of directory to get, null to get all
     * @return array|string configuration directory
     */
    public function getConfigDir($index = null)
    {
        if ($index !== null) {
            return isset($this->_config_dir
            [$index]) ? $this->_config_dir
            [$index] : null;
        }

        return (array)$this->_config_dir;
    }

    /**
     * Set plugins directory
     *
     * @api
     * @param  string|array $plugins_dir directory(s) of plugins
     * @return Smarty       current Smarty instance for chaining
     */
    public function setPluginsDir($plugins_dir)
    {
        $this->_setDir($plugins_dir, '_plugins_dir', false);
        return $this;
    }

    /**
     * Adds directory of plugin files
     *
     * @api
     * @param  string|array $plugins_dir plugin folder names
     * @return Smarty       current Smarty instance for chaining
     */
    public function addPluginsDir($plugins_dir)
    {
        $this->_addDir($plugins_dir, null, '_plugins_dir', false);
        $this->_plugins_dir = array_unique($this->_plugins_dir);
        return $this;
    }

    /**
     * Get plugin directories
     *
     * @api
     * @return array list of plugin directories
     */
    public function getPluginsDir()
    {
        return (array)$this->_plugins_dir;
    }

    /**
     * Set compile directory
     *
     * @api
     * @param  string $compile_dir directory to store compiled templates in
     * @return Smarty current Smarty instance for chaining
     */
    public function setCompileDir($compile_dir)
    {
        $this->_setMutedDir($compile_dir, '_compile_dir');
        return $this;
    }

    /**
     * Get compiled directory
     *
     * @api
     * @return string path to compiled templates
     */
    public function getCompileDir()
    {
        return $this->_compile_dir;
    }

    /**
     * Set cache directory
     *
     * @api
     * @param  string $cache_dir directory to store cached templates in
     * @return Smarty current Smarty instance for chaining
     */
    public function setCacheDir($cache_dir)
    {
        $this->_setMutedDir($cache_dir, '_cache_dir');
        return $this;
    }

    /**
     * Get cache directory
     *
     * @api
     * @return string path of cache directory
     */
    public function getCacheDir()
    {
        return $this->_cache_dir;
    }

    /**
     * Set  directory
     *
     * @internal
     * @param  string|array $dir directory(s) of  sources
     * @param  string $dirprop  name of directory property
     * @param bool $do_join  true if joined directory property must be updated
     *
     */
    private function _setDir($dir, $dirprop, $do_join = true)
    {
        $this->$dirprop = array();
        foreach ((array)$dir as $k => $v) {
            $this->{$dirprop}[$k] = $this->_checkDir($v);
        }
        if ($do_join) {
            $joined = '_joined' . $dirprop;
            $this->$joined = join(DIRECTORY_SEPARATOR, $this->$dirprop);
        }
    }

    /**
     * Add  directory(s)
     *
     * @internal
     * @param  string|array $dir directory(s)
     * @param  string $key      of the array element to assign the dir to
     * @param  string $dirprop  name of directory property
     * @param bool $do_join  true if joined directory property must be updated
     */
    private function _addDir($dir, $key = null, $dirprop, $do_join = true)
    {
        // make sure we're dealing with an array
        $this->$dirprop = (array)$this->$dirprop;

        if (is_array($dir)) {
            foreach ($dir as $k => $v) {
                if (is_int($k)) {
                    // indexes are not merged but appended
                    $this->{$dirprop}[] = $this->_checkDir($v);
                } else {
                    // string indexes are overridden
                    $this->{$dirprop}[$k] = $this->_checkDir($v);
                }
            }
        } elseif ($key !== null) {
            // override directory at specified index
            $this->{$dirprop}[$key] = $this->_checkDir($dir);
        } else {
            // append new directory
            $this->{$dirprop}[] = $this->_checkDir($dir);
        }
        if ($do_join) {
            $joined = '_joined' . $dirprop;
            $this->$joined = join(DIRECTORY_SEPARATOR, $this->$dirprop);
        }
        return;
    }

    /**
     * Set  muted directory
     *
     * @internal
     * @param  string $dir directory
     * @param  string $dirprop  name of directory property
     */
    private function _setMutedDir($dir, $dirprop)
    {
        $this->$dirprop = $this->_checkDir($dir);
        if (!isset(self::$_muted_directories[$this->$dirprop])) {
            self::$_muted_directories[$this->$dirprop] = null;
        }

        return;
    }

    /**
     *  function to check directory path
     *
     * @internal
     * @param  string $path     directory
     * @return string           trimmed filepath
     */
    private function _checkDir($path)
    {
        return rtrim($path, '/\\') . '/';
    }


    /**
     * Enable error handler to mute expected messages
     *
     * @api
     * @return void
     */
    public static function muteExpectedErrors()
    {
        /*
        error muting is done because some people implemented custom error_handlers using
        http://php.net/set_error_handler and for some reason did not understand the following paragraph:

        It is important to remember that the standard PHP error handler is completely bypassed for the
        error types specified by error_types unless the callback function returns FALSE.
        error_reporting() settings will have no effect and your error handler will be called regardless -
        however you are still able to read the current value of error_reporting and act appropriately.
        Of particular note is that this value will be 0 if the statement that caused the error was
        prepended by the @ error-control operator.

        Smarty deliberately uses @filemtime() over file_exists() and filemtime() in some places. Reasons include
        - @filemtime() is almost twice as fast as using an additional file_exists()
        - between file_exists() and filemtime() a possible race condition is opened,
        which does not exist using the simple @filemtime() approach.
        */
        $error_handler = array('Smarty_Extension_MutingErrorHandler', 'mutingErrorHandler');
        $previous = set_error_handler($error_handler);

        // avoid dead loops
        if ($previous !== $error_handler) {
            self::$_previous_error_handler = $previous;
        }
    }

    /**
     * Disable error handler muting expected messages
     *
     * @api
     * @return void
     */
    public static function unmuteExpectedErrors()
    {
        restore_error_handler();
    }

    /**
     * clean up object pointer
     *
     */
    public function cleanPointer()
    {
        unset($this->source, $this->compiled, $this->cached, $this->compiler, $this->must_compile);
        $this->_tpl_vars = $this->parent = $this->template_function_chain = $this->rootTemplate = null;
    }

    /**
     *
     *  runtime routine to create a new variable scope
     *
     * @param  null $data
     * @param $parent
     * @return \Smarty_Variable_Scope
     */
    public function _buildScope($data, $parent)
    {
        if ($parent instanceof Smarty_Variable_Scope) {
            $scope = clone $parent;
        } elseif ($this->_usage == self::IS_SMARTY) {
            return clone $this->_tpl_vars;
        } else {
            $scope = $this->_tpl_vars = $this->_mergeScopes($this);
        }
        // fill data if present
        if ($data != null) {
            // set up variable values
            foreach ($data as $varname => $value) {
                $scope->$varname = new Smarty_Variable($value);
            }
        }

        return $scope;
    }

    /**
     *
     *  merge recursively template variables into one scope
     *
     * @param   Smarty|Smarty_Data|Smarty_Template $ptr
     * @return Smarty_Variable_Scope                    merged tpl vars
     */
    public function _mergeScopes($ptr)
    {
        // Smarty::triggerTraceCallback('trace', ' merge tpl ');

        if ($ptr->parent) {
            $_tpl_vars = $this->_mergeScopes($ptr->parent);
            foreach ($ptr->_tpl_vars as $var => $data) {
                $_tpl_vars->$var = $data;
            }

            return $_tpl_vars;
        } else {
            return clone $ptr->_tpl_vars;
        }
    }

    public function _getSourceObject($resource, $parent = null)
    {
        $parent = isset($parent) ? $parent : $this->parent;
        if ($resource == null) {
            $resource = $this->template_resource;
        }
        $isConfig = (stripos($resource, 'conf:') === 0);
        if ($isConfig) {
            $resource = substr($resource, 5);
        } else {
            $isConfig = (substr($resource, -5) == '.conf');
        }
        if (!($this->allow_ambiguous_resources || isset($this->handler_allow_relative_path))) {
            $_cacheKey = ($isConfig ? $this->_joined_config_dir : $this->_joined_template_dir) . '#' . $resource;
            if (isset($_cacheKey[150])) {
                $_cacheKey = sha1($_cacheKey);
            }
            // source with this $_cacheKey in cache?
            if (isset(self::$source_cache[$_cacheKey])) {
                // return source object
                return self::$source_cache[$_cacheKey];
            }
        }
        // parse template_resource into name and type
        $parts = explode(':', $resource, 2);
        if (!isset($parts[1]) || !isset($parts[0][1])) {
            // no resource given, use default
            // or single character before the colon is not a resource type, but part of the filepath
            $type = $this->default_resource_type;
            $name = $resource;
        } else {
            $type = $parts[0];
            $name = $parts[1];
        }
        $res_obj = isset(self::$resource_cache[self::SOURCE][$type]) ? self::$resource_cache[self::SOURCE][$type] : $this->_loadResource(self::SOURCE, $type);
        if (isset($this->_allow_relative_path) && isset($res_obj->_allow_relative_path) && $_cacheKey = $res_obj->getRelativeKey($resource, $parent)) {
            if (isset($_cacheKey[150])) {
                $_cacheKey = sha1($_cacheKey);
            }
            // source with this $_cacheKey in cache?
            if (isset(self::$source_cache[$_cacheKey])) {
                // return source object
                return self::$source_cache[$_cacheKey];
            }
        }
        if ($this->allow_ambiguous_resources) {
            // get cacheKey
            $_cacheKey = self::$resource_cache[self::SOURCE][$type]->buildUniqueResourceName($this, $resource);
            if (isset($_cacheKey[150])) {
                $_cacheKey = sha1($_cacheKey);
            }
            // source with this $_cacheKey in cache?
            if (isset(self::$source_cache[$_cacheKey])) {
                // return source object
                return self::$source_cache[$_cacheKey];
            }
        }

        // create and return new Source object
        if (false === $source_obj = new Smarty_Source($this, $name, $type, $isConfig, $parent)) {
            return false;
        } else {
            return self::$source_cache[$_cacheKey] = $source_obj;
        }
    }


    public function _getRenderedTemplate($resource_group, $source, $parent, $compile_id, $cache_id, $caching, $data, $scope_type, $no_output_filter = false, $display = false)
    {
        if ($parent instanceof Smarty_Variable_Scope) {
            $scope = clone $parent;
        } elseif ($this->_usage == self::IS_SMARTY) {
            $scope = clone $this->_tpl_vars;
        } else {
            $scope = $this->_mergeScopes($this);
        }
        // fill data if present
        if ($data != null) {
            // set up variable values
            foreach ($data as $varname => $value) {
                $scope->$varname = new Smarty_Variable($value);
            }
        }

        // get template object
        $template_obj = $this->_getTemplateObject($resource_group, $source, $parent, $compile_id, $cache_id, $caching);

        //render template
        return $template_obj->getRenderedTemplate($parent, $scope, $scope_type, $no_output_filter, $display);
    }

    public function _getTemplateObject($resource_group, $source, $parent, $compile_id, $cache_id, $caching, $check = false)
    {
        switch ($resource_group) {
            case self::SOURCE:
            case self::COMPILED:
                if ($source->recompiled) {
                    $type = 'recompiled';
                } else {
                    $type = $this->compiled_type;
                }
                // check runtime cache
                $source_key = isset($source->uid) ? $source->uid : '#null#';
                $compiled_key = $compile_id ? $compile_id : '#null#';
                if ($caching) {
                    $compiled_key .= '#caching';
                }
                if (isset(self::$template_cache[self::COMPILED][$type][$source_key][$compiled_key])) {
                    // is already in cache
                    $template_obj = self::$template_cache[self::COMPILED][$type][$source_key][$compiled_key];
                    // check if up to date
                    if (!$this->force_compile || $template_obj->isUpdated) {
                        break;
                    }
                }
                if ($check) {
                    return false;
                }
                if (isset(self::$resource_cache[self::COMPILED][$type])) {
                    // resource already in cache
                    $res_obj = self::$resource_cache[self::COMPILED][$type];
                } else {
                    $res_obj = $this->_loadResource(self::COMPILED, $type);
                }
                $template_obj = $res_obj->instanceTemplate($this, $source, $compile_id, $caching);
                self::$template_cache[self::COMPILED][$type][$source_key][$compiled_key] = $template_obj;
                break;

            case self::CACHE:
                // check runtime cache
                $source_key = isset($source->uid) ? $source->uid : '#null#';
                $compiled_key = $compile_id ? $compile_id : '#null#';
                $cache_key = $cache_id ? $cache_id : '#null#';
                if (isset(self::$template_cache[self::CACHE][$this->caching_type][$source_key][$compiled_key][$cache_key])) {
                    // is already in cache
                    $template_obj = self::$template_cache[self::CACHE][$this->caching_type][$source_key][$compiled_key][$cache_key];
                    // check if up to date
                    if ((!$this->force_compile && !$this->force_cache) || $template_obj->isUpdated) {
                        break;
                    }
                }
                if ($check) {
                    return false;
                }
                if (isset(self::$resource_cache[self::CACHE][$this->caching_type])) {
                    // resource already in cache
                    $res_obj = self::$resource_cache[self::CACHE][$this->caching_type];
                } else {
                    $res_obj = $this->_loadResource(self::CACHE, $this->caching_type);
                }
                $template_obj = $res_obj->instanceTemplate($this, $source, $compile_id, $caching, $parent, $_scope, $scope_type, $no_output_filter);
        }
        return $template_obj;
    }

    /**
     *  Get handler and create resource object
     *
     * @param  int $resource_group SOURCE|COMPILED|CACHE
     * @param  string $type resource hamdler
     * @throws Smarty_Exception
     * @return Smarty_Resource_xxx | false
     */
    public function _loadResource($resource_group, $type)
    {
        static $class_prefix = array(
            self::SOURCE => 'Smarty_Resource_Source',
            self::COMPILED => 'Smarty_Resource_Compiled',
            self::CACHE => 'Smarty_Resource_Cache'
        );

        // resource group and type already in cache
        if (isset(self::$resource_cache[$resource_group][$type])) {
            // return the handler
            return self::$resource_cache[$resource_group][$type];
        }

        $type = strtolower($type);
        $res_obj = null;

        if (!$res_obj) {
            $resource_class = $class_prefix[$resource_group] . '_' . ucfirst($type);
            if (isset($this->registered_resources[$resource_group][$type])) {
                if ($this->registered_resources[$resource_group][$type] instanceof $resource_class) {
                    $res_obj = $this->registered_resources[$resource_group][$type];
                } else {
                    $res_obj = new Smarty_Resource_Source_Registered();
                }
            } elseif (class_exists($resource_class, true)) {
                $res_obj = new $resource_class();
            } elseif ($this->_loadPlugin($resource_class)) {
                if (class_exists($resource_class, false)) {
                    $res_obj = new $resource_class();
                } elseif ($resource_group == self::SOURCE) {
                    /**
                     * @TODO  This must be rewritten
                     *
                     */
                    $this->registerResource($type, array(
                        "smarty_resource_{$type}_source",
                        "smarty_resource_{$type}_timestamp",
                        "smarty_resource_{$type}_secure",
                        "smarty_resource_{$type}_trusted"
                    ));

                    // give it another try, now that the resource is registered properly
                    $res_obj = $this->_loadResource($resource_group, $type);
                }
            } elseif ($resource_group == self::SOURCE) {

                // try streams
                $_known_stream = stream_get_wrappers();
                if (in_array($type, $_known_stream)) {
                    // is known stream
                    if (is_object($this->security_policy)) {
                        $this->security_policy->isTrustedStream($type);
                    }
                    $res_obj = new Smarty_Resource_Source_Stream();
                }
            }
        }

        if ($res_obj) {
            return self::$resource_cache[$resource_group][$type] = $res_obj;
        }

        // TODO: try default_(template|config)_handler
        // give up
        throw new Smarty_Exception_UnknownResourceType($class_prefix[$resource_group], $type);
    }

    /**
     * Takes unknown classes and loads plugin files for them
     * class name format: Smarty_PluginType_PluginName
     * plugin filename format: plugintype.pluginname.php
     *
     * @internal
     * @param  string $plugin_name    plugin or class name
     * @param  bool $check          check if already loaded
     * @throws Smarty_Exception
     * @return string|boolean   filepath of loaded plugin | true if it was a Smarty core class || false if not found
     */
    public function _loadPlugin($plugin_name, $check = true)
    {
        if ($check) {
            // if function or class exists, exit silently (already loaded)
            if (is_callable($plugin_name) || class_exists($plugin_name, false)) {
                return true;
            }
        }
        // Plugin name is expected to be: Smarty_[Type]_[Name]
        $_name_parts = explode('_', $plugin_name, 3);
        // class name must have at least three parts to be valid plugin
        if (!isset($_name_parts[2]) || strtolower($_name_parts[0]) !== 'smarty') {
            throw new Smarty_Exception("loadPlugin(): Plugin {$plugin_name} is not a valid name format");
        }
        // plugin filename is expected to be: [type].[name].php
        $_plugin_filename = "{$_name_parts[1]}.{$_name_parts[2]}.php";

        $_stream_resolve_include_path = function_exists('stream_resolve_include_path');
        // add SMARTY_PLUGINS_DIR if not present
        $_plugins_dir = $this->getPluginsDir();
        if (!$this->disable_core_plugins) {
            $_plugins_dir[] = Smarty::$_SMARTY_PLUGINS_DIR;
        }

        // loop through plugin dirs and find the plugin
        foreach ($_plugins_dir as $_plugin_dir) {
            $names = array(
                $_plugin_dir . $_plugin_filename,
                $_plugin_dir . strtolower($_plugin_filename),
            );
            foreach ($names as $file) {
                if (file_exists($file)) {
                    require_once($file);

                    return $file;
                }
                if ($this->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_plugin_dir)) {
                    // try PHP include_path
                    if ($_stream_resolve_include_path) {
                        $file = stream_resolve_include_path($file);
                    } else {
                        $file = $this->getIncludePath($file);
                    }
                    if ($file !== false) {
                        require_once($file);

                        return $file;
                    }
                }
            }
        }

        // no plugin loaded
        return false;
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->_usage == self::IS_SMARTY_TPL_CLONE && $this->cache_locking && isset($this->cached) && $this->cached->is_locked) {
            $this->cached->releaseLock($this, $this->cached);
        }
        parent::__destruct();
    }

    /**
     * <<magic>> method
     * remove resource source
     * remove extensions
     */
    public function __clone()
    {
        unset($this->source);
        unset($this->compiled);
        unset($this->cached);
        // clear loaded extension
        $this->_loaded_extensions = array();
    }

    /**
     * <<magic>> Generic getter.
     * Get Smarty or Template property
     *
     * @param  string $property_name property name
     * @throws Smarty_Exception
     * @return $this|bool|\Smarty_Compiled|\Smarty_template_Cached|\Smarty_Template_Source
     */
    public function __get($property_name)
    {
        static $getter = array(
            'template_dir' => 'getTemplateDir',
            'config_dir' => 'getConfigDir',
            'plugins_dir' => 'getPluginsDir',
            'compile_dir' => 'getCompileDir',
            'cache_dir' => 'getCacheDir',
        );
        switch ($property_name) {
            case 'compiled':
                return $this->resourceStatus(self::COMPILED);
            case 'cached':
                return $this->resourceStatus(self::CACHE);
            case 'mustCompile':
                return !$this->isCompiled();

        }
        switch ($property_name) {
            case 'template_dir':
            case 'config_dir':
            case 'plugins_dir':
            case 'compile_dir':
            case 'cache_dir':
                return $this->{$getter[$property_name]}();
        }
        // throw error through parent
        parent::__get($property_name);
    }

    /**
     * <<magic>> Generic setter.
     * Set Smarty or Template property
     *
     * @param  string $property_name property name
     * @param  mixed $value         value
     * @throws Smarty_Exception
     */
    public function __set($property_name, $value)
    {
        static $setter = array(
            'template_dir' => 'setTemplateDir',
            'config_dir' => 'setConfigDir',
            'plugins_dir' => 'setPluginsDir',
            'compile_dir' => 'setCompileDir',
            'cache_dir' => 'setCacheDir',
        );
        switch ($property_name) {
            case 'template_dir':
            case 'config_dir':
            case 'plugins_dir':
            case 'compile_dir':
            case 'cache_dir':
                $this->{$setter[$property_name]}($value);
                return;
            case 'source':
            case 'compiled':
            case 'cached':
                $this->$property_name = $value;
                return;
        }

        // throw error through parent
        parent::__set($property_name, $value);
    }

    /**
     * Handle unknown class methods
     *  - load extensions for external methods
     *  - call generic getter/setter
     *
     * @param  string $name unknown method-name
     * @param  array $args argument array
     * @throws Smarty_Exception
     * @return $this|bool|\Smarty_Compiled|\Smarty_template_Cached|\Smarty_Template_Source
     */
    public function __call($name, $args)
    {
        static $_prefixes = array('set' => true, 'get' => true);
        static $_in_extension = array('setAutoloadFilters' => true, 'getAutoloadFilters' => true,
            'setDefaultModifiers' => true, 'getDefaultModifiers' => true, 'getGlobal' => true,
            'setDebugTemplate' => true, 'getDebugTemplate' => true, 'getCachedVars' => true,);
        static $_resolved_property_name = array();

        // see if this is a set/get for a property
        $first3 = strtolower(substr($name, 0, 3));
        if (isset($_prefixes[$first3]) && !isset($_in_extension[$name]) && isset($name[3]) && $name[3] !== '_') {
            if (isset($_resolved_property_name[$name])) {
                $property_name = $_resolved_property_name[$name];
            } else {
                // try to keep case correct for future PHP 6.0 case-sensitive class methods
                // lcfirst() not available < PHP 5.3.0, so improvise
                $property_name = strtolower(substr($name, 3, 1)) . substr($name, 4);
                // convert camel case to underscored name
                $property_name = preg_replace_callback('/([A-Z])/', array($this, 'replaceCamelcase'), $property_name);
                $_resolved_property_name[$name] = $property_name;
            }
            if ($first3 == 'get') {
                return $this->$property_name;
            } else {
                return $this->$property_name = $args[0];
            }
        }
        // try extensions
        if (isset($this->_loaded_extensions[$name])) {
            return call_user_func_array(array($this->_loaded_extensions[$name], $name), $args);
        }
        $class = 'Smarty_Extension_' . ucfirst($name);
        if (class_exists($class, true)) {
            $obj = new $class($this);
            if (method_exists($obj, $name)) {
                $this->_loaded_extensions[$name] = $obj;
                return call_user_func_array(array($obj, $name), $args);
            }
        }
        if ($name == 'Smarty') {
            throw new Smarty_Exception_OldConstructor();
        }
        // throw error through parent
        parent::__call($name, $args);
    }

    /**
     * preg_replace callback to convert camelcase getter/setter to underscore property names
     *
     * @param  string $match match string
     * @return string replacemant
     */
    private function replaceCamelcase($match)
    {
        return "_" . strtolower($match[1]);
    }
}

// let PCRE (preg_*) treat strings as ISO-8859-1 if we're not dealing with UTF-8
if (Smarty::$_CHARSET !== 'UTF-8') {
    Smarty::$_UTF8_MODIFIER = '';
}
