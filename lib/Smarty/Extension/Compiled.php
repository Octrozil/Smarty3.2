<?php

/**
 * Smarty Extension Compiled Plugin
 *
 * Smarty class methods
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package Smarty
 */
class Smarty_Extension_Compiled
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
        $compiled =  $this->smarty->_load(Smarty::COMPILED, null, $type);
        // invalidate complete cache
        unset(Smarty::$resource_cache[Smarty::COMPILED]);
        return $compiled->clear($this->smarty, $resource_name, $compile_id, $exp_time, false);
    }

    /**
     * Delete compiled config file
     *
     * @api
     * @param  string $resource_name template name
     * @param  string $compile_id    compile id
     * @param  integer $exp_time      expiration time
     * @param  string $type     resource type
     * @return integer number of template files deleted
     */
    public function clearCompiledConfig($resource_name = null, $compile_id = null, $exp_time = null, $type = null)
    {
        // load compiled resource
        $compiled = Smarty_Resource_Loader::load($this->smarty, Smarty_Resource_Loader::COMPILED, $type);
        // invalidate complete cache
        Smarty_Resource_Compiled::$resource_cache = array();
        return $compiled->clear($this->smarty, $resource_name, $compile_id, $exp_time, true);
    }
}
