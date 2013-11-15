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
     * returns resource status object
     *
     * @api
     * @param Smarty $smarty smarty object
     * @param $resource_group
     * @param  string|object $template the resource handle of the template file or template object
     * @param  mixed $cache_id cache id to be used with this template
     * @param  mixed $compile_id compile id to be used with this template
     * @param null $parent
     * @param null $caching
     * @param bool $isConfig
     * @return string  cache filepath
     */
    public function resourceStatus(Smarty $smarty, $resource_group, $template = null, $cache_id = null, $compile_id = null, $parent = null, $caching = null, $isConfig = false)
    {
        $status = clone $this;
        $status->smarty = $smarty;
        if (!empty($cache_id) && is_object($cache_id)) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($template === null && ($status->smarty->_usage == Smarty::IS_SMARTY_TPL_CLONE || $status->_usage == Smarty::IS_CONFIG)) {
            $template = $status->smarty;
        }
        //get context object from cache  or create new one
        $context = $status->smarty->_getContext($template, $cache_id, $compile_id, $parent, false, false, null, null, $caching);
        // fill basic data
        $status->resource_group = $resource_group;
        $status->compile_id = $context->compile_id;
        $status->cache_id = $context->cache_id;
        $status->caching = $context->caching;
        $status->recompiled = $context->handler->recompiled;
        $status->uncompiled = $context->handler->uncompiled;
        $status->exists = $context->exists;
        $status->uid = $context->uid;
        if (!$status->exists) {
            // source does not exists so exit here
            return $status;
        }
        switch ($resource_group) {
            case Smarty::SOURCE:
                $status->isValid = true;
                $status->filepath = $context->filepath;
                $status->timestamp = $context->timestamp;
                // done for source request
                return $status;
            case Smarty::COMPILED:
                if ($status->recompiled) {
                    $status->type = 'recompiled';
                } else {
                    $status->type = $context->smarty->compiled_type;
                }
                break;
            case Smarty::CACHE:
                if (!$status->caching || $status->recompiled) {
                    $status->exists = false;
                    return $status;
                }
                $status->type = $context->smarty->caching_type;
                break;
        }
        // common handling for COMPILED and CACHE
        $res_obj = $context->smarty->_loadResource($resource_group, $status->type);
        $status->timestamp = $status->exists = false;
        $status->filepath = $res_obj->buildFilepath($context);
        $res_obj->populateTimestamp($context->smarty, $status->filepath, $status->timestamp, $status->exists);
        if ($status->exists) {
            if ($status->timestamp < $context->timestamp || $context->smarty->force_compile) {
                return $status;
            }
            try {
                $template_obj = $context->smarty->_getTemplateObject($resource_group, $context);
            } catch (Exception $e) {
                return $status;
            }

            $status->template_obj = $template_obj;
            $status->isValid = $template_obj->isValid;
        }
        return $status;
    }
}
