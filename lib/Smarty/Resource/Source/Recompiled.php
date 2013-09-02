<?php

/**
 * Smarty Resource Source Recompiled Class
 *
 *
 * @package TemplateResources
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source Recompiled Class
 *
 * Base implementation for resource plugins that don't compile cache
 *
 *
 * @package TemplateResources
 */
abstract class Smarty_Resource_Source_Recompiled extends Smarty_Resource_Source
{
    /**
     * Flag that source must always be recompiled
     *
     * @var bool
     */
    public $recompiled = true;
}