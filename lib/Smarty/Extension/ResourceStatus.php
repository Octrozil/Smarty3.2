<?php

/**
 * Smarty Extension
 *
 * Smarty class methods
 *
 * @package Smarty\Extension
 * @author Uwe Tews
 */

/**
 * Class for resourceStatus method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_ResourceStatus extends Smarty_Exception_Magic
{

    /**
     *  Smarty object
     *
     * @var Smarty
     */
    public $smarty;

    /**
     * usage of this resource
     * @var mixed
     */
    public $resource_group = null;

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
     * Cache Is Valid
     * @var boolean
     */
    public $isValid = false;

    /**
     * Template Compile Id (Smarty::$compile_id)
     * @var string
     */
    public $compile_id = null;

    /**
     * Template Cache Id (Smarty::$cache_id)
     * @var string
     */
    public $cache_id = null;

    /**
     * Flag if caching enabled
     * @var boolean
     */
    public $caching = false;

    /**
     * Template object for COMPILED and CACHE
     * @var Smarty_Template
     */
    public $template_obj = '';

    /**
     *  Constructor
     *
     * @param Smarty $smarty Smarty object
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * returns resource status object
     *
     * @api
     * @param $resource_group
     * @param  string|object $template   the resource handle of the template file or template object
     * @param  mixed $cache_id   cache id to be used with this template
     * @param  mixed $compile_id compile id to be used with this template
     * @param null $parent
     * @param null $caching
     * @param bool $isConfig
     * @return string  cache filepath
     */
    public function resourceStatus($resource_group, $template = null, $cache_id = null, $compile_id = null, $parent = null, $caching = null, $isConfig = false)
    {
        if (!empty($cache_id) && is_object($cache_id)) {
            $parent = $cache_id;
            $cache_id = null;
        }
       if ($template === null && ($this->smarty->_usage == Smarty::IS_SMARTY_TPL_CLONE|| $this->_usage == self::IS_CONFIG)) {
            $template = $this->smarty;
        }
        if (is_object($template)) {
            // get source from template clone
            $source = $template->source;
            $tpl_obj = $template;
        } else {
            if ($parent == null) {
                $tpl_obj = $this->smarty;
            } else {
                // if parent was passed we must create a clone
                $tpl_obj = clone $this->smarty;
                $tpl_obj->parent = $parent;
            }

            //get source object from cache  or create new one
            $source = $tpl_obj->_getSourceObject($template);
        }
        // fill basic data
        $this->resource_group = $resource_group;
        $this->compile_id = isset($compile_id) ? $compile_id : $tpl_obj->compile_id;
        $this->cache_id = isset($cache_id) ? $cache_id : $tpl_obj->cache_id;
        $this->caching = isset($caching) ? $caching : $tpl_obj->caching;
        $this->recompiled = $source->recompiled;
        $this->uncompiled = $source->uncompiled;
        $this->exists = $source->exists;
        $this->uid = $source->uid;
        if (!$this->exists) {
            // source does not exists so exit here
            return $this;
        }
        switch ($resource_group) {
            case Smarty::SOURCE:
                $this->isValid = true;
                $this->filepath = $source->filepath;
                $this->timestamp = $source->timestamp;
                // done for source request
                return $this;
            case Smarty::COMPILED:
                if ($source->recompiled) {
                    $this->type = 'recompiled';
                } else {
                    $this->type = $tpl_obj->compiled_type;
                }
                $param = $this->caching;
                break;
            case Smarty::CACHE:
                if (!$this->caching) {
                    $this->exists = false;
                    return $this;
                }
                $this->type = $tpl_obj->caching_type;
                $param = $this->cache_id;
                break;
        }
        // common handling for COMPILED and CACHE
        $res_obj = $tpl_obj->_loadResource($resource_group, $this->type);
        $this->timestamp = $this->exists = false;
        $this->filepath = $res_obj->buildFilepath($tpl_obj, $source, $this->compile_id, $param);
        $res_obj->populateTimestamp($tpl_obj, $this->filepath, $this->timestamp, $this->exists);
        if ($this->exists) {
            if ($this->timestamp < $source->timestamp) {
                return $this;
            }
            $template_obj = $tpl_obj->_getTemplateObject($resource_group, $source, null, $compile_id, $cache_id, $caching, true);
            if ($template_obj === false) {
                if ($tpl_obj->force_compile) {
                    return $this;
                }
                try {
                    $template_class_name = '';
                    // load existing compiled template class
                    $template_class_name = $res_obj->loadTemplateClass($this->filepath);
                    if (class_exists($template_class_name, false)) {
                        $template_obj = new $template_class_name($tpl_obj, $source, $this->filepath, $this->timestamp);
                    } else {
                        return $this;
                    }
                } catch (Exception $e) {
                    return $this;
                }
            }
            $this->template_obj = $template_obj;
            $this->isValid = $template_obj->isValid;
        }
        return $this;
    }
}
