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
class Smarty_Method_ResourceStatus extends Smarty_Exception_Magic
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
    public $timestamp = false;

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
        $status = clone $this;
        if (!empty($cache_id) && is_object($cache_id)) {
            $parent = $cache_id;
            $cache_id = null;
        }
       if ($template === null && ($status->smarty->_usage == Smarty::IS_SMARTY_TPL_CLONE|| $status->_usage == self::IS_CONFIG)) {
            $template = $status->smarty;
        }
        if (is_object($template)) {
            // get source from template clone
            $source = $template->source;
            $tpl_obj = $template;
        } else {
            if ($parent == null) {
                $tpl_obj = $status->smarty;
            } else {
                // if parent was passed we must create a clone
                $tpl_obj = clone $status->smarty;
                $tpl_obj->parent = $parent;
            }

            //get source object from cache  or create new one
            $source = $tpl_obj->_getSourceObject($template);
        }
        // fill basic data
        $status->resource_group = $resource_group;
        $status->compile_id = isset($compile_id) ? $compile_id : $tpl_obj->compile_id;
        $status->cache_id = isset($cache_id) ? $cache_id : $tpl_obj->cache_id;
        $status->caching = isset($caching) ? $caching : $tpl_obj->caching;
        $status->recompiled = $source->recompiled;
        $status->uncompiled = $source->uncompiled;
        $status->exists = $source->exists;
        $status->uid = $source->uid;
        if (!$status->exists) {
            // source does not exists so exit here
            return $status;
        }
        switch ($resource_group) {
            case Smarty::SOURCE:
                $status->isValid = true;
                $status->filepath = $source->filepath;
                $status->timestamp = $source->timestamp;
                // done for source request
                return $status;
            case Smarty::COMPILED:
                if ($source->recompiled) {
                    $status->type = 'recompiled';
                } else {
                    $status->type = $tpl_obj->compiled_type;
                }
                $param = $status->caching;
                break;
            case Smarty::CACHE:
                if (!$status->caching) {
                    $status->exists = false;
                    return $status;
                }
                $status->type = $tpl_obj->caching_type;
                $param = $status->cache_id;
                break;
        }
        // common handling for COMPILED and CACHE
        $res_obj = $tpl_obj->_loadResource($resource_group, $status->type);
        $status->timestamp = $status->exists = false;
        $status->filepath = $res_obj->buildFilepath($tpl_obj, $source, $status->compile_id, $param);
        $res_obj->populateTimestamp($tpl_obj, $status->filepath, $status->timestamp, $status->exists);
        if ($status->exists) {
            if ($status->timestamp < $source->timestamp) {
                return $status;
            }
            $template_obj = $source->_getTemplateObject($tpl_obj, $resource_group, $parent, $status->compile_id, $status->cache_id, $status->caching);
//            $template_obj = $tpl_obj->_getTemplateObject($resource_group, $source, null, $compile_id, $cache_id, $caching, true);
            if ($template_obj === false) {
                if ($tpl_obj->force_compile) {
                    return $status;
                }
                try {
                    $template_class_name = '';
                    // load existing compiled template class
                    $template_class_name = $res_obj->loadTemplateClass($status->filepath);
                    if (class_exists($template_class_name, false)) {
                        $template_obj = new $template_class_name($tpl_obj, $source, $status->filepath, $status->timestamp);
                    } else {
                        return $status;
                    }
                } catch (Exception $e) {
                    return $status;
                }
            }
            $status->template_obj = $template_obj;
            $status->isValid = $template_obj->isValid;
        }
        return $status;
    }
}
