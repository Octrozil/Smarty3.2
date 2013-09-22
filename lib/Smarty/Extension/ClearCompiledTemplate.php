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
 * Class for clearCompiledTemplate method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_ClearCompiledTemplate
{
    /**
     *  Smarty object
     *
     * @var Smarty
     */
    public $smarty;

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
     * Delete compiled template file
     *
     * @api
     * @param  string $resource_name template name
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string $type     resource type
     * @return integer number of template files deleted
     */
    public function clearCompiledTemplate($resource_name = null, $compile_id = null, $exp_time = null, $type = null)
    {
        $type = $type ? $type : $this->smarty->compiled_type;
        // load compiled resource
        $compiled =  $this->smarty->_loadResource(Smarty::COMPILED, $type);
        // invalidate complete cache
        // TODO
        //unset(Smarty::$template_cache[Smarty::COMPILED]);
        return $compiled->clear($this->smarty, $resource_name, $compile_id, $exp_time, false);
    }
}
