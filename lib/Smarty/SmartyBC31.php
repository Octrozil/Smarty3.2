<?php

/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        SmartyBC31.class.php
 * SVN:         $Id: $
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com
 *
 * @link http://www.smarty.net/
 * @copyright 2008 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 * @author Rodney Rehm
 * @package Smarty
 * @subpackage SmartyBC
 */
/**
 * @ignore
 */
require_once (dirname(__FILE__) . '/Smarty.php');

/**
 * Dummy Template class for Smarty 3.1 BC
 *
 * @package Smarty
 * @subpackage SmartyBC
 */
class Smarty_Internal_Template extends Smarty
{

}

/**
 * Smarty Backward Compatibility Wrapper Class for Smarty 3.1
 *
 * @package Smarty
 * @subpackage SmartyBC
 */
class SmartyBC31 extends Smarty_Internal_Template
{

    /**
     * <<magic>> Generic getter.
     * Get Smarty or Template property
     *
     * @param  string           $property_name property name
     * @throws Smarty_Exception
     * @return $this
     */
    public function __get($property_name)
    {
        // resolve 3.1 references from template to Smarty object
        if ($property_name == 'smarty') {
            return $this;
        }

        return parent::__get($property_name);
    }

    /**
     *  DEPRECATED FUNCTION
     * assigns values to template variables by reference
     *
     * @param  string               $tpl_var the template variable name
     * @param  mixed                &$value  the referenced value to assign
     * @param  boolean              $nocache if true any output of this variable will be not cached
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template_) instance for chaining
     */
    public function assignByRef($tpl_var, &$value, $nocache = false)
    {
        if ($tpl_var != '') {
            $this->tpl_vars->$tpl_var = new Smarty_Variable(null, $nocache);
            $this->tpl_vars->$tpl_var->value = & $value;
        }

        return $this;
    }

    /**
     *  DEPRECATED FUNCTION
     * appends values to template variables by reference
     *
     * @param  string               $tpl_var the template variable name
     * @param  mixed                &$value  the referenced value to append
     * @param  boolean              $merge   flag if array elements shall be merged
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template_) instance for chaining
     */
    public function appendByRef($tpl_var, &$value, $merge = false)
    {
        if ($tpl_var != '' && isset($value)) {
            if (!isset($this->tpl_vars->$tpl_var)) {
                $this->tpl_vars->$tpl_var = new Smarty_Variable(array());
            }
            if (!@is_array($this->tpl_vars->$tpl_var->value)) {
                settype($this->tpl_vars->$tpl_var->value, 'array');
            }
            if ($merge && is_array($value)) {
                foreach ($value as $_key => $_val) {
                    $this->tpl_vars->$tpl_var->value[$_key] = & $value[$_key];
                }
            } else {
                $this->tpl_vars->$tpl_var->value[] = & $value;
            }
        }

        return $this;
    }
    /**
     * Registers object to be used in templates
     *
     * @param $object_name
     * @param  string           $object        $object        the referenced PHP object to register
     * @param  array            $allowed       list of allowed methods (empty = all)
     * @param  boolean          $smarty_args   smarty argument format, else traditional
     * @param  array            $block_methods list of block-methods
     * @throws Smarty_Exception
     * @return Smarty
     */
    public function registerObject($object_name, $object, $allowed = array(), $smarty_args = true, $block_methods = array())
    {
        if (!is_object($object)) {
            throw new Smarty_Exception("registerObject(): Invalid parameter for object");
        }
        // test if allowed methods callable
        if (!empty($allowed)) {
            foreach ((array) $allowed as $method) {
                if (!is_callable(array($object, $method)) && !property_exists($object_impl, $method)) {
                    throw new Smarty_Exception("registerObject(): Undefined method or property \"{$method}\"");
                }
            }
        }
        // test if block methods callable
        if (!empty($block_methods)) {
            foreach ((array) $block_methods as $method) {
                if (!is_callable(array($object, $method))) {
                    throw new Smarty_Exception("registerObject(): Undefined method \"{$method}\"");
                }
            }
        }
        // register the object
        $this->registered_objects[$object_name] =
            array($object, (array) $allowed, (boolean) $smarty_args, (array) $block_methods);

        return $this;
    }

    /**
     * return a reference to a registered object
     *
     * @param  string           $name object name
     * @return object
     * @throws Smarty_Exception if no such object is found
     */
    public function getRegisteredObject($name)
    {
        if (!isset($this->registered_objects[$name])) {
            throw new Smarty_Exception("getRegisteredObject(): No object resgistered for \"{$name}\"");
        }

        return $this->registered_objects[$name][0];
    }

    /**
     * unregister an object
     *
     * @param  string $name object name
     * @return Smarty
     */
    public function unregisterObject($name)
    {
        if (isset($this->registered_objects[$name])) {
            unset($this->registered_objects[$name]);
        }

        return $this;
    }

}
