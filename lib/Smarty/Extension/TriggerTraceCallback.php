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
 * Class for triggerTraceCallback method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_TriggerTraceCallback
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
     * @internal
     * @param string $event  string event
     * @param mixed $data
     */
    public function triggerTraceCallback($event, $data = array())
    {
        if ($this->smarty->enable_trace && isset(Smarty::$_trace_callbacks[$event])) {
            foreach (Smarty::$_trace_callbacks[$event] as $callback) {
                call_user_func_array($callback, (array)$data);
            }
        }
    }
}
