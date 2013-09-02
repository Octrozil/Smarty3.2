<?php

/**
 * Smarty Resource Source Uncompiled Class
 *
 *
 * @package TemplateResources
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source Uncompiled Class
 *
 * Base implementation for resource plugins that don't use the compiler
 *
 *
 * @package TemplateResources
 */
abstract class Smarty_Resource_Source_Uncompiled extends Smarty_Resource_Source
{

    /**
     * Flag that source does not nee compilation
     *
     * @var bool
     */
    public $uncompiled = true;

    /**
     * Render and output the template (without using the compiler)
     *
     * @param Smarty $tpl_obj template object
     * @return
     * @internal param \Smarty_Source_Resource $source source object
     */
    abstract public function renderUncompiled(Smarty $tpl_obj);

    /**
     * get rendered template output from compiled template
     *
     * @param  Smarty_Source_Resource $source  source object
     * @param  Smarty $tpl_obj template object
     * @throws Exception
     * @return string
     */
    public function getRenderedTemplate(Smarty_Source_Resource $source, $tpl_obj)
    {
        if ($tpl_obj->debugging) {
            Smarty_Debug::start_render($tpl_obj);
        }
        try {
            $level = ob_get_level();
            ob_start();
            $this->renderUncompiled($source, $tpl_obj);
            $output = ob_get_clean();
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
        if ($tpl_obj->caching) {
            $cached = Smarty_Cache_Helper_Create::_getCachedObject($tpl_obj);
            $cached->newcache->file_dependency[$source->uid] = array($source->filepath, $source->timestamp, $source->type);
        }
        return $output;
    }
}
