<?php

/**
 * Smarty Resource Loader Plugin
 *
 *
 * @package Resources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Loader Plugin
 *
 * Base implementation for resource plugins
 *
 *
 * @package Resources
 */
class Smarty_Resource_Loader extends Smarty_Exception_Magic
{
    /**
     * define resource group
     */
    const SOURCE = 0;
    const COMPILED = 1;
    const CACHE = 2;

    /**
     *  Get handler and create resource object
     *
     * @param  int $resource_group SOURCE|COMPILED|CACHE
     * @param  Smarty           $smarty         Smarty object
     * @param  string           $resource       name of template_resource or the resource handler
     * @throws Smarty_Exception
     * @return Smarty_Source_Resource  Resource Handler
     */
    static function load($smarty, $resource_group, $resource = null)
    {
        static $class_prefix = array(
            self::SOURCE => 'Smarty_Resource_Source',
            self::COMPILED => 'Smarty_Resource_Compiled',
            self::CACHE => 'Smarty_Resource_Cache'
        );

        switch ($resource_group) {
            case self::SOURCE:
                if ($resource == null) {
                    $resource = $smarty->template_resource;
                }
                // parse template_resource into name and type
                $parts = explode(':', $resource, 2);
                if (!isset($parts[1]) || !isset($parts[0][1])) {
                    // no resource given, use default
                    // or single character before the colon is not a resource type, but part of the filepath
                    $type = $smarty->default_resource_type;
                    $name = $resource;
                } else {
                    $type = $parts[0];
                    $name = $parts[1];
                }
                break;
            case self::COMPILED:
                $type = $resource ? $resource : $smarty->compiled_type;
                break;
            case self::CACHE:
                $type = $resource ? $resource : $smarty->caching_type;
                break;
        }

        $type = strtolower($type);
        $res_obj = null;

        if (!$res_obj) {
            $resource_class = $class_prefix[$resource_group] . '_' . ucfirst($type);
            if (isset($smarty->registered_resources[$resource_group][$type])) {
                if ($smarty->registered_resources[$resource_group][$type] instanceof $class_prefix[$resource_group]) {
                    $res_obj = $smarty->registered_resources[$resource_group][$type];
                } else {
                    $res_obj =  new Smarty_Source_Resource_Registered();
                }
            } elseif ($smarty->_loadPlugin($resource_class)) {
                if (class_exists($resource_class, false)) {
                    $res_obj = new $resource_class();
                } elseif ($resource_group == self::SOURCE) {
                    /**
                     * @TODO  This must be rewritten
                     *
                     */
                    $smarty->registerResource($type, array(
                        "smarty_resource_{$type}_source",
                        "smarty_resource_{$type}_timestamp",
                        "smarty_resource_{$type}_secure",
                        "smarty_resource_{$type}_trusted"
                    ));

                    // give it another try, now that the resource is registered properly
                    $res_obj = self::load($smarty, $resource_group, $resource);
                }
            } elseif ($resource_group == self::SOURCE) {

                // try streams
                $_known_stream = stream_get_wrappers();
                if (in_array($type, $_known_stream)) {
                    // is known stream
                    if (is_object($smarty->security_policy)) {
                        $smarty->security_policy->isTrustedStream($type);
                    }
                    $res_obj = new Smarty_Source_Resource_Stream();
                }
            }
        }

        if ($res_obj) {
            if ($resource_group == self::SOURCE) {
                $res_obj->name = $name;
                $res_obj->type = $type;
            }
            // return create resource object
            return $res_obj;
        }

        // TODO: try default_(template|config)_handler
        // give up
        throw new Smarty_Exception("Unknown resource '" . $class_prefix[$resource_group] . "' '{$type}'");
    }
}
