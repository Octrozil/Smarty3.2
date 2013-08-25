<?php

/**
 * @smarty_template_object $b
 */
function smarty_modifier_one($string, $a = '', $b = null)
{
    return $string;
}

/**
 * @smarty_cache_attrs alpha, bravo, charlie
 * comment
 */
function smarty_function_one(array $params, $template)
{
    return $string;
}

function smarty_block_one(array $params, $content, $template, &$repeat)
{
    return $string;
}

class Smarty_Demo
{
    /**
     * @smarty_template_object $c
     */
    public function modifier_one($string, $a = '', $b = null, $c = null)
    {
        return $string;
    }

    /**
     * @smarty_cache_attrs zulu, yankee, x-ray
     */
    public function function_one(array $params, $template)
    {
        return $string;
    }

    public function block_one(array $params, $content, $template, &$repeat)
    {
        return $string;
    }
}

function _injectTemplateObject($callback)
{
    if (is_string($callback)) {
        $reflection = new ReflectionFunction($callback);
    } elseif (is_array($callback)) {
        $cn = is_object($callback[0]) ? 'ReflectionObject' : 'ReflectionClass';
        $reflection = new $cn($callback[0]);
        $reflection = $reflection->getMethod($callback[1]);
    } else {
        throw new Excption("callback must be function name (string) or object/class method array");
    }

    $doc = $reflection->getDocComment();
    if ($doc && preg_match('#@smarty_template_object \$([A-Za-z_0-9]+)#', $doc, $matches)) {
        foreach ($reflection->getParameters() as $param) {
            if ($param->getName() == $matches[1]) {
                return $param->getPosition();
            }
        }

        throw new Exception("@smarty_template_object '{$matches[1]}' not found in '{$reflection->getName()}'");
    }

    return null;
}

function _caching($callback)
{
    if (is_string($callback)) {
        $reflection = new ReflectionFunction($callback);
    } elseif (is_array($callback)) {
        $cn = is_object($callback[0]) ? 'ReflectionObject' : 'ReflectionClass';
        $reflection = new $cn($callback[0]);
        $reflection = $reflection->getMethod($callback[1]);
    } else {
        throw new Excption("callback must be function name (string) or object/class method array");
    }

    $doc = $reflection->getDocComment();
    var_dump($doc);
    if ($doc && preg_match('#@smarty_cache_attrs (.+)$#m', $doc, $matches)) {
        $m = explode(',', $matches[1]);

        return array_map('trim', $m);
    }

    return null;
}

var_dump(_injectTemplateObject('smarty_modifier_one'));
var_dump(_injectTemplateObject(array('Smarty_Demo', 'modifier_one')));

var_dump(_caching('smarty_function_one'));
var_dump(_caching(array('Smarty_Demo', 'function_one')));
