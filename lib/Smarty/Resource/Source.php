<?php

/**
 * Smarty Resource Source Plugin
 *
 *
 * @package TemplateResources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source Plugin
 *
 * Base implementation for resource plugins
 *
 *
 * @package Resources
 */
class Smarty_Resource_Source extends Smarty_Exception_Magic
{

    /**
     * source resource cache
     *
     * @var array
     * @internal
     */
    public static $resource_cache = array();

    /**
     * resource filepath
     *
     * @var string| boolean false
     */
    public $filepath = false;

    /**
     * Resource Timestamp
     * @var integer
     */
    public $timestamp = null;

    /**
     * Resource Existence
     * @var boolean
     */
    public $exists = false;

    /**
     *  Source Resource specific properties
     */

    /**
     * usage of this resource
     * @var mixed
     */
    public $usage = null;

    /**
     * Template name
     *
     * @var string
     */
    public $name = '';

    /**
     * Resource handler type
     *
     * @var string
     */
    public $type = '';

    /**
     * resource UID
     *
     * @var string
     */
    public $uid = '';

    /**
     * Resource compiler class
     * if null default is used
     */
    public $compiler_class = null;

    /**
     * Resource lexer class
     * if null default is used
     */
    public $lexer_class = null;

    /**
     * Resource lexer class
     * if null default is used
     */
    public $parser_class = null;

    /**
     * array of extends components
     *
     * @var array
     */
    public $components = array();

    /**
     * Flag if source needs no compiler
     *
     * @var bool
     */
    public $uncompiled = false;

    /**
     * Flag if source needs to be always recompiled
     *
     * @var bool
     */
    public $recompiled = false;

    /**
     * returns cached source object, or creates a new one
     *
     * @api
     * @param  Smarty $smarty             Smarty object
     * @param  string $template_resource  template and resource name
     * @param  bool $is_config          is source for config file
     * @return Smarty_Resource_Source source object
     */
    static function load($smarty, $template_resource, $is_config = false)
    {
        // already in source cache?
        if ($smarty->allow_ambiguous_resources) {
            $resource = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::SOURCE, $template_resource);
            $_cacheKey = $resource->buildUniqueResourceName($smarty, $template_resource);
        } else {
            $_cacheKey = ($is_config ? $smarty->joined_config_dir : $smarty->joined_template_dir) . '#' . $template_resource;
        }
        if (isset($_cacheKey[150])) {
            $_cacheKey = sha1($_cacheKey);
        }
        if (isset(self::$resource_cache[$_cacheKey])) {
            // return cached object
            $source = self::$resource_cache[$_cacheKey];
        } else {
            // create and load new source object
            $source = Smarty_Resource_Loader::load($smarty, Smarty_Resource_Loader::SOURCE, $template_resource);
            $source->usage = $is_config ? Smarty::IS_CONFIG : Smarty::IS_TEMPLATE;
            $source->populate($smarty);
            // checks if source exists
            if (!$source->exists) {
                throw new Smarty_Exception("Can not find '{$source->type}:{$source->name}'");
            }
            // cache source object under a unique ID
            // do not cache eval resources
            if (!$source->recompiled) {
                self::$resource_cache[$_cacheKey] = $source;
            }
        }

        return $source;
    }

   /**
     * get rendered template output from compiled template
     *
     * @param  Smarty $smarty          template object
     * @param  boolean $no_output_filter true if output filter shall nit run
     * @return string
     */
    public function getRenderedTemplate($smarty, $no_output_filter = true)
    {
        // TOdo  FIX ->HANDLER
        $output = $this->handler->getRenderedTemplate($this, $smarty);
        if (!$no_output_filter && (isset($smarty->autoload_filters['output']) || isset($smarty->registered_filters['output']))) {
            $output = $smarty->runFilter('output', $output);
        }

        return $output;
    }

    /**
     * Load template's source into current template object
     *
     * {@internal The loaded source is assigned to $smarty->source->content directly.}}
     *
     * @return string           template source
     * @throws Smarty_Exception if source cannot be loaded
     */
    public function getContent()
    {
    }

   /**
     * build a unique source resource name
     *
     * @param  Smarty $smarty        Smarty instance
     * @param  string $resource_name resource_name to make unique
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(Smarty $smarty, $resource_name)
    {
        return get_class($this) . '#' . ($smarty->usage == Smarty::IS_CONFIG ? $smarty->joined_config_dir : $smarty->joined_template_dir) . '#' . $resource_name;
    }
}
