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
 * Class for findRootTemplate method
 *
 * @package Smarty\Extension
 */
class Smarty_Method_FindRootTemplate
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
     * Identify and get top-level template instance
     *
     * @api
     * @return Smarty root template object
     */
    public function findRootTemplate()
    {
        $tpl_obj = $this->smarty;
        while ($tpl_obj->parent && ($tpl_obj->parent->_usage == Smarty::IS_SMARTY_TPL_CLONE || $tpl_obj->parent->_usage == Smarty::IS_CONFIG)) {
            if ($tpl_obj->rootTemplate) {
                return $this->smarty->rootTemplate = $tpl_obj->rootTemplate;
            }

            $tpl_obj = $tpl_obj->parent;
        }

        return $this->smarty->rootTemplate = $tpl_obj;
    }
}
