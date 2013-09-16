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
 * Class for unregisterTraceCallback method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_UnregisterTraceCallback
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

    /*
    EVENTS:
    filesystem:write
    filesystem:delete
    */

    /**
     *
     * @api
     * @param  string|array $event
     * @return Smarty
     */
    public function unregisterTraceCallback($event = null)
    {
        if ($event == null) {
            Smarty::$_trace_callbacks = array();
            return $this->smarty;
        } else {
            foreach ($event as $_event) {
                if (isset(Smarty::$_trace_callbacks[$_event])) {
                    unset(Smarty::$_trace_callbacks);
                }
            }
        }

        return $this->smarty;
    }
}
