<?php

/**
 * Smarty Extension Trace Plugin
 *
 * Smarty class methods
 *
 *
 * @package CoreExtensions
 * @author Uwe Tews
 */

/**
 * Class for modifier methods
 *
 *
 * @package CoreExtensions
 */
class Smarty_Extension_Trace
{
    /*
    EVENTS:
    filesystem:write
    filesystem:delete
    */

    /**
     * @param  Smarty $smarty   Smarty object
     * @param  string|array $event
     * @param  callable $callback class/method name
     * @throws Smarty_Exception
     */
    public function registerTraceCallback(Smarty $smarty, $event, $callback = null)
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
         return $smarty;
    }

    /**
     * @param  Smarty $smarty   Smarty object
     * @param  string|array $event
     * @param  callable $callback class/method name
     * @throws Smarty_Exception
     */
    public function unregisterTraceCallback(Smarty $smarty, $event = null)
    {
        if ($event == null) {
            Smarty::$_trace_callbacks = array();
            return $smarty;
        } else {
            foreach ($event as $_event)  {
                if (isset(Smarty::$_trace_callbacks[$_event])) {
                    unset(Smarty::$_trace_callbacks);
                }
            }
        }

        return $smarty;
     }

    /**
     * @param Smarty $smarty Smarty object
     * @param string $event  string event
     * @param mixed $data
     */
    public function triggerTraceCallback(Smarty $smarty, $event, $data = array())
    {
        if ($smarty->enable_trace && isset(Smarty::$_trace_callbacks[$event])) {
            foreach (Smarty::$_trace_callbacks[$event] as $callback) {
                call_user_func_array($callback, (array)$data);
            }
        }
    }
}
