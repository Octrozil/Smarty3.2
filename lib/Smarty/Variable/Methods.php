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
     * template variables
     *
     * @var array
     */
    public $tpl_vars = null;

    /**
     * parent template (if any)
     *
     * @var Smarty
     */
    public $parent = null;

    /**
     * usage of Smarty_Variable_Methods
     * @var int
     * @uses IS_SMARTY as possible value
     * @uses IS_TEMPLATE as possible value
     * @uses IS_CONFIG as possible value
     * @uses IS_DATA as possible value
     */
    public $usage = null;

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
        // TODO check behavior
        //        $ptr = $this;
        //        while (isset($ptr->IS_TEMPLATE) && $ptr->IS_TEMPLATE) {
        //            $ptr->assign($tpl_var, $value, $nocache);
        //            $ptr = $ptr->parent;
        //        }
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
        if (is_array($tpl_var)) {
            // $tpl_var is an array, ignore $value
            foreach ($tpl_var as $varname => $_val) {
                if ($varname != '') {
                    if (!isset($this->_tpl_vars->$varname)) {
                        $tpl_var_inst = $this->getVariable($varname, null, true, false);
                        if ($tpl_var_inst === null) {
                            $this->_tpl_vars->$varname = new Smarty_Variable(null, $nocache);
                        } else {
                            $this->_tpl_vars->$varname = clone $tpl_var_inst;
                        }
                    }
                    if (!(is_array($this->_tpl_vars->$varname->value) || $this->_tpl_vars->$varname->value instanceof ArrayAccess)) {
                        settype($this->_tpl_vars->$varname->value, 'array');
                    }
                    if ($merge && is_array($_val)) {
                        foreach ($_val as $_mkey => $_mval) {
                            $this->_tpl_vars->$varname->value[$_mkey] = $_mval;
                        }
                    } else {
                        $this->_tpl_vars->$varname->value[] = $_val;
                    }
                }
            }
        } else {
            if ($tpl_var != '' && isset($value)) {
                if (!isset($this->_tpl_vars->$tpl_var)) {
                    $tpl_var_inst = $this->getVariable($tpl_var, null, true, false);
                    if ($tpl_var_inst === null) {
                        $this->_tpl_vars->$tpl_var = new Smarty_Variable(null, $nocache);
                    } else {
                        $this->_tpl_vars->$tpl_var = clone $tpl_var_inst;
                    }
                }
                if (!(is_array($this->_tpl_vars->$tpl_var->value) || $this->_tpl_vars->$tpl_var->value instanceof ArrayAccess)) {
                    settype($this->_tpl_vars->$tpl_var->value, 'array');
                }
                if ($merge && is_array($value)) {
                    foreach ($value as $_mkey => $_mval) {
                        $this->_tpl_vars->$tpl_var->value[$_mkey] = $_mval;
                    }
                } else {
                    $this->_tpl_vars->$tpl_var->value[] = $value;
                }
            }
        }

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
        if (isset($varname)) {
            $result = $this->getVariable($varname, $_ptr, $search_parents, false);
            if ($result === null) {
                return false;
            } else {
                return $result->value;
            }
        } else {
            $_result = array();
            if ($_ptr === null) {
                $_ptr = $this;
            }
            while ($_ptr !== null) {
                foreach ($_ptr->_tpl_vars AS $varname => $data) {
                    if (strpos($varname, '___') !== 0 && !isset($_result[$varname])) {
                        $_result[$varname] = $data->value;
                    }
                }
                // not found, try at parent
                if ($search_parents) {
                    $_ptr = $_ptr->parent;
                } else {
                    $_ptr = null;
                }
            }
            if ($search_parents && isset(Smarty::$_global_tpl_vars)) {
                foreach (Smarty::$_global_tpl_vars AS $varname => $data) {
                    if (strpos($varname, '___') !== 0 && !isset($_result[$varname])) {
                        $_result[$varname] = $data->value;
                    }
                }
            }

            return $_result;
        }
    }

    /**
     * gets the object of a template variable
     *
     * @param  string $varname        the name of the Smarty variable
     * @param  object $_ptr           optional pointer to data object
     * @param  boolean $search_parents search also in parent data
     * @param  boolean $error_enable   enable error handling
     * @param  null $property       optional requested variable property
     * @throws Smarty_Exception_Runtime
     * @return mixed                    Smarty_variable object|property of variable
     */
    public function getVariable($varname, $_ptr = null, $search_parents = true, $error_enable = true, $property = null)
    {
        if ($_ptr === null) {
            $_ptr = $this;
        }
        while ($_ptr !== null) {
            if (isset($_ptr->_tpl_vars->$varname)) {
                // found it, return it
                if ($property === null) {
                    return $_ptr->_tpl_vars->$varname;
                } else {
                    return isset($_ptr->_tpl_vars->$varname->$property) ? $_ptr->_tpl_vars->$varname->$property : null;
                }
            }
            // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }
        if (isset(Smarty::$_global_tpl_vars->$varname)) {
            // found it, return it
            if ($property === null) {
                return Smarty::$_global_tpl_vars->$varname;
            } else {
                return isset(Smarty::$_global_tpl_vars->$varname->$property) ? Smarty::$_global_tpl_vars->$varname->$property : null;
            }
        }
        if ($this->usage == Smarty::IS_DATA) {
            $error_unassigned = $this->_tpl_vars->___attributes->tpl_ptr->error_unassigned;
        } else {
            $error_unassigned = $this->error_unassigned;
        }
        if (strpos($varname, '___config_var_') !== 0) {
            if (isset($this->default_variable_handler_func)) {
                $value = null;
                if (call_user_func_array($this->default_variable_handler_func, array($varname, &$value, $this))) {
                    if ($value instanceof Smarty_Variable) {
                        $var = $value;
                    } else {
                        $var = new Smarty_Variable($value);
                    }
                    if ($property === null) {
                        return $var;
                    } else {
                        return isset($var->$property) ? $var->$property : null;
                    }
                }
            }
            if ($error_unassigned != Smarty::UNASSIGNED_IGNORE && $error_enable) {
                $err_msg = "Unassigned template variable '{$varname}'";
                if ($error_unassigned == Smarty::UNASSIGNED_NOTICE) {
                    // force a notice
                    trigger_error($err_msg);
                } elseif ($error_unassigned == Smarty::UNASSIGNED_EXCEPTION) {
                    throw new Smarty_Exception_Runtime($err_msg, $this);
                }
            }
            $var = new Smarty_Variable();
            if ($property === null) {
                return $var;
            } else {
                return isset($var->$property) ? $var->$property : null;
            }

        } else {
            $real_varname = substr($varname, 14);
            if (isset($this->default_config_variable_handler_func)) {
                $value = null;
                if (call_user_func_array($this->default_config_variable_handler_func, array($real_varname, &$value, $this))) {
                    return $value;
                }
            }
            if ($error_unassigned != Smarty::UNASSIGNED_IGNORE && $error_enable) {
                $err_msg = "Unassigned config variable '{$real_varname}'";
                if ($error_unassigned == Smarty::UNASSIGNED_NOTICE) {
                    // force a notice
                    trigger_error($err_msg);
                } elseif ($error_unassigned == Smarty::UNASSIGNED_EXCEPTION) {
                    throw new Smarty_Exception_Runtime($err_msg, $this);
                }
            }
        }
        // unassigned variable which shall be ignored
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
        $_ptr = $this;
        if (isset($varname)) {
            $result = $this->getVariable('___config_var_' . $varname, $_ptr, $search_parents, false);

            return $result;
        } else {
            $_result = array();
            while ($_ptr !== null) {
                foreach ($_ptr->_tpl_vars AS $varname => $data) {
                    $real_varname = substr($varname, 14);
                    if (strpos($varname, '___config_var_') === 0 && !isset($_result[$real_varname])) {
                        $_result[$real_varname] = $data;
                    }
                }
                // not found, try at parent
                if ($search_parents) {
                    $_ptr = $_ptr->parent;
                } else {
                    $_ptr = null;
                }
            }

            return $_result;
        }
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
        $smarty_obj = $this->usage == Smarty::IS_DATA ? $this->_tpl_vars->___attributes->tpl_ptr : $this;
        // TODO nneds rewrite ?
        $tpl_obj = $smarty_obj->createTemplate($config_file, null, null, $this, true);
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
        $_result = '';
        $fp = fopen($variable, 'r+');
        if ($fp) {
            while (!feof($fp) && ($current_line = fgets($fp)) !== false) {
                $_result .= $current_line;
            }
            fclose($fp);

            return $_result;
        }

        if ($this->smarty->error_unassigned) {
            throw new Smarty_Exception('Undefined stream variable "' . $variable . '"');
        } else {
            return null;
        }
    }
}
