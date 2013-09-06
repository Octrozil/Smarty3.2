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
 * Class for registerTraceCallback method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_RegisterTraceCallback
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
     * @api
     * @param  string|array $event
     * @param  callable $callback class/method name
     * @throws Smarty_Exception
     */
    public function registerTraceCallback($event, $callback = null)
    {
        if (is_array($event)) {
            foreach ($event as $_event => $_callback) {
                if (!is_callable($_callback)) {
                    throw new Smarty_Exception("registerCallback(): \"{$_event}\" not callable");
                }
                Smarty::$_trace_callbacks[$_event][] = $_callback;
            }
        } else {
            if (!is_callable($callback)) {
                throw new Smarty_Exception("registerCallback(): \"{$event}\" not callable");
            }
            Smarty::$_trace_callbacks[$event][] = $callback;
        }
        return $this->smarty;
    }
}