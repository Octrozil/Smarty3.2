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
 * Class for runFilter method
 *
 * @package Smarty\Extension
 */
class Smarty_Extension_RunFilter
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
     * Run filters over content
     *
     * The filters will be lazy loaded if required
     * class name format: Smarty_FilterType_FilterName
     * plugin filename format: filtertype.filtername.php
     * Smarty2 filter plugins could be used
     *
     * @internal
     * @param  string $type    the type of filter ('pre','post','output') which shall run
     * @param  string $content the content which shall be processed by the filters
     * @throws Smarty_Exception
     * @return string           the filtered content
     */
    public function runFilter($type, $content)
    {
        $output = $content;
        // loop over autoload filters of specified type
        if (!empty($this->smarty->autoload_filters[$type])) {
            foreach ((array)$this->smarty->autoload_filters[$type] as $name) {
                $plugin_name = "Smarty_{$type}filter_{$name}";
                if ($this->smarty->_loadPlugin($plugin_name)) {
                    if (function_exists($plugin_name)) {
                        // use loaded Smarty2 style plugin
                        $output = $plugin_name($output, $this->smarty);
                    } elseif (class_exists($plugin_name, false)) {
                        // loaded class of filter plugin
                        $output = call_user_func(array($plugin_name, 'execute'), $output, $this->smarty);
                    }
                } else {
                    // nothing found, throw exception
                    throw new Smarty_Exception("Unable to load filter {$plugin_name}");
                }
            }
        }
        // loop over registered filters of specified type
        if (!empty($this->smarty->registered_filters[$type])) {
            foreach ($this->smarty->registered_filters[$type] as $key => $name) {
                if (is_array($this->smarty->registered_filters[$type][$key])) {
                    $output = call_user_func($this->smarty->registered_filters[$type][$key], $output, $this->smarty);
                } else {
                    $output = $this->smarty->registered_filters[$type][$key]($output, $this->smarty);
                }
            }
        }
        // return filtered output
        return $output;
    }

}
