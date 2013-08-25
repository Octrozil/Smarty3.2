<?php

/**
 * Smarty Resource Plugin
 *
 *
 * @package TemplateResources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Plugin
 *
 * Base implementation for resource plugins
 *
 *
 * @package Resources
 */
class Smarty_Resource extends Smarty_Exception_Magic
{

    /**
     * resource filepath
     *
     * @var string| boolean false
     */
    public $filepath = false;

    /**
     * Resource Timestamp
     * @var integer
     */
    public $timestamp = null;

    /**
     * Resource Existence
     * @var boolean
     */
    public $exists = false;

    /**
     *  Source Resource specific properties
     */

    /**
     * usage of this resoure
     * @var mixed
     */
    public $usage = null;

    /**
     * Template name
     *
     * @var string
     */
    public $name = '';

    /**
     * Resource handler type
     *
     * @var string
     */
    public $type = '';

    /**
     * resource UID
     *
     * @var boolean
     */
    public $uid = false;

    /**
     * Resource compiler class
     * if null default is used
     */
    public $compiler_class = null;

    /**
     * Resource lexer class
     * if null default is used
     */
    public $lexer_class = null;

   /**
     * Resource lexer class
     * if null default is used
     */
    public $parser_class = null;

    /**
     * array of extends components
     *
     * @var array
     */
    public $components = array();

    public $uncompiled = false;
    public $recompiled = false;

    /**
     * Populate source resource properties
     *
     * @param  Smarty          $tpl_obj   template object
     * @param  bool            $is_config is source for config file
     * @param  bool            $error     create error it source does not exists
     * @return Smarty_Resource
     */
    public function populateResource($tpl_obj, $is_config =  false, $error = false)
    {
        $this->usage = $is_config ? Smarty::IS_CONFIG : Smarty::IS_TEMPLATE;
        $this->populate($tpl_obj);
        if ($error) {
            // checks if source exists
            if (!$this->exists) {
                throw new Smarty_Exception("Can not find '{$source->type}:{$source->name}'");
            }
        }

        return $this;
    }

    /**
     * get rendered template output from compiled template
     *
     * @param  Smarty  $tpl_obj          template object
     * @param  boolean $no_output_filter true if output filter shall nit run
     * @return string
     */
    public function getRenderedTemplate($tpl_obj, $no_output_filter = true)
    {
        // TOdo  FIX ->HANDLER
        $output = $this->handler->getRenderedTemplate($this, $tpl_obj);
        if (!$no_output_filter && (isset($tpl_obj->autoload_filters['output']) || isset($tpl_obj->registered_filters['output']))) {
            $output = Smarty_Misc_FilterHandler::runFilter('output', $output, $tpl_obj);
        }

        return $output;
    }

    /**
     * Load template's source into current template object
     *
     * {@internal The loaded source is assigned to $tpl_obj->source->content directly.}}
     *
     * @return string           template source
     * @throws Smarty_Exception if source cannot be loaded
     */
    public function getContent()
    {
    }


    /**
     * populate Source Object with timestamp and exists from Resource
     *
     */
    public function populateTimestamp()
    {
        // intentionally left blank
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param  Smarty $smarty        Smarty instance
     * @param  string $resource_name resource_name to make unique
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(Smarty $smarty, $resource_name)
    {
        return get_class($this) . '#' . ($smarty->usage == Smarty::IS_CONFIG ? $smarty->joined_config_dir : $smarty->joined_template_dir) . '#' . $resource_name;
    }


    /**
     * test is file exists and save timestamp
     *
     * @param  string $file file name
     * @return bool   true if file exists
     */
    protected function fileExists($file)
    {
        $this->timestamp = @filemtime($file);

        return $this->exists = !!$this->timestamp;
    }

    /**
     * Determine basename for compiled filename
     *
     * @return string resource's basename
     */
    protected function getBasename()
    {
        return null;
    }

    /**
     * <<magic>> Generic Setter.
     *
     * @param  string           $property_name valid: timestamp, exists, content, template
     * @param  mixed            $value         new value (is not checked)
     * @throws Smarty_Exception if $property_name is not valid
     */
    public function __set($property_name, $value)
    {
        switch ($property_name) {
            // regular attributes
            case 'timestamp':
            case 'exists':
            case 'content':
                // required for extends: only
            case 'template':
                $this->$property_name = $value;
                break;

            default:
                parent::__set($property_name, $value);
        }
    }

    /**
     * <<magic>> Generic getter.
     *
     * @param  string           $property_name valid: timestamp, exists, content
     * @return mixed
     * @throws Smarty_Exception if $property_name is not valid
     */
    public function __get($property_name)
    {
        switch ($property_name) {
            case 'timestamp':
            case 'exists':
                $this->populateTimestamp($this);

                return $this->$property_name;

            case 'content':
                return $this->content = $this->getContent($this);

            default:
                return parent::__get($property_name);
        }
    }
}
