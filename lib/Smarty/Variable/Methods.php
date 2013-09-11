<?php

/**
 * Smarty Variable Methods
 *
 * This file contains the basic methods for variable handling
 *
 *
 * @package Smarty
 * @author Uwe Tews
 */

/**
 * Base class with variable methods
 *
 *
 * @package Smarty
 */
class Smarty_Variable_Methods extends Smarty_Exception_Magic
{
    /**
     * parent template (if any)
     *
     * @var Smarty
     */
    public $parent = null;

    /**
     * assigns a Smarty variable
     *
     * @api
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed $value   the value to assign
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $varname => $value) {
                if ($varname != '') {
                    $this->_tpl_vars->$varname = new Smarty_Variable($value, $nocache);
                }
            }
        } else {
            if ($tpl_var != '') {
                $this->_tpl_vars->$tpl_var = new Smarty_Variable($value, $nocache);
            }
        }

        return $this;
    }

    /**
     * assigns a Smarty variable to the current object and all parent elements
     *
     * @api
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed $value   the value to assign
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function assignParents($tpl_var, $value = null, $nocache = false)
    {
        $this->assign($tpl_var, $value, $nocache);
        $node = $this->parent;

        while ($node) {
            $node->assign($tpl_var, $value, $nocache);
            $node = $node->parent;
        }

        return $this;
    }

    /**
     * assigns a global Smarty variable
     *
     * @api
     * @param  string $varname the global variable name
     * @param  mixed $value   the value to assign
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function assignGlobal($varname, $value = null, $nocache = false)
    {
        if ($varname != '') {
            Smarty::$_global_tpl_vars->$varname = new Smarty_Variable($value, $nocache);
        }
        return $this;
    }

    /**
     * assigns a variable hook
     *
     * @api
     * @param  string $varname the variable name
     * @param  callback $callback PHP callback to get variable value
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function assignCallback($varname, $callback, $nocache = false)
    {
        if ($varname != '') {
            if (!is_callable($callback)) {
                throw new Smarty_Exception("assignHook(): Hook for variable \"{$varname}\" not callable");
            } else {
                if (is_object($callback)) {
                    $callback = array($callback, '__invoke');
                }
                $this->_tpl_vars->$varname = new Smarty_Variable_Callback($varname, $callback, $nocache);
            }
        }
        return $this;
    }


    /**
     * appends values to template variables
     *
     * @api
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed $value   the value to append
     * @param  boolean $merge   flag if array elements shall be merged
     * @param  boolean $nocache if true any output of this variable will be not cached
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function append($tpl_var, $value = null, $merge = false, $nocache = false)
    {
        Smarty_Variable_Extension_Append::append($this, $tpl_var, $value, $merge, $nocache);
        return $this;
    }

    /**
     * clear the given assigned template variable.
     *
     * @api
     * @param  string|array $tpl_var the template variable(s) to clear
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function clearAssign($tpl_var)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $curr_var) {
                unset($this->_tpl_vars->$curr_var);
            }
        } else {
            unset($this->_tpl_vars->$tpl_var);
        }

        return $this;
    }

    /**
     * clear all the assigned template variables.
     *
     * @api
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function clearAllAssign()
    {
        $this->_tpl_vars = new Smarty_Variable_Scope();
        return $this;
    }

    /**
     * Returns a single or all template variables
     *
     * @api
     * @param  string $varname        variable name or null
     * @param  string $_ptr           optional pointer to data object
     * @param  boolean $search_parents include parent templates?
     * @return string  variable value or or array of variables
     */
    public function getTemplateVars($varname = null, $_ptr = null, $search_parents = true)
    {
        return Smarty_Variable_Extension_GetVariable::getTemplateVars($this, $varname, $_ptr, $search_parents);
     }


    /**
     * get the object or property of global template variable
     *
     * @param  string $varname        the name of the Smarty variable
     * @param  null $property         optional requested variable property
     * @return mixed                  null|Smarty_variable object|property of variable
     */

    public static function getGlobalVariable($varname, $property = null) {
        if (isset(Smarty::$_global_tpl_vars->$varname)) {
            // found it, return it
            if ($property === null) {
                return Smarty::$_global_tpl_vars->$varname;
            } else {
                return isset(Smarty::$_global_tpl_vars->$varname->$property) ? Smarty::$_global_tpl_vars->$varname->$property : null;
            }
        }
        return null;
    }


    /**
     * Returns a single or all config variables
     *
     * @api
     * @param  string $varname        variable name or null
     * @param  bool $search_parents true to search also in parent templates
     * @return string variable value or or array of variables
     */
    public function getConfigVars($varname = null, $search_parents = true)
    {
        return Smarty_Variable_Extension_GetConfigVariable::getConfigVars($this, $varname, $search_parents);
   }

    /**
     * Deassigns a single or all config variables
     *
     * @api
     * @param  string $varname variable name or null
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function clearConfig($varname = null)
    {
        if (isset($varname)) {
            unset($this->_tpl_vars->{'___config_var_' . $varname});
        } else {
            foreach ($this->_tpl_vars as $key => $var) {
                if (strpos($key, '___config_var_') === 0) {
                    unset($this->_tpl_vars->$key);
                }
            }
        }

        return $this;
    }

    /**
     * load a config file, optionally load just selected sections
     *
     * @api
     * @param  string $config_file filename
     * @param  mixed $sections    array of section names, single section or null
     * @param  string $scope_type  template scope into which config file shall be loaded
     * @return Smarty_Variable_Methods current Smarty_Variable_Methods (or Smarty) instance for chaining
     */
    public function configLoad($config_file, $sections = null, $scope_type = 'local')
    {
        $smarty = $this->_usage == Smarty::IS_DATA ? $this->smarty : $this;
        // TODO nneds rewrite ?
        $tpl_obj = $smarty->createTemplate($config_file, null, null, $this, true);
        $tpl_obj->_tpl_vars->___config_sections = $sections;
        $tpl_obj->_tpl_vars->___config_scope = $scope_type;
        $tpl_obj->compiled->getRenderedTemplate($tpl_obj);
        return $this;
    }

    /**
     * gets  a stream variable
     *
     * @api
     * @param  string $variable the stream of the variable
     * @throws Smarty_Exception
     * @return mixed            the value of the stream variable
     */
    public function getStreamVariable($variable)
    {
        return Smarty_Variable_Extension_GetStreamVariable::getStreamVariable($this, $variable);
    }
}
