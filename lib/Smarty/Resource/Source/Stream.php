<?php

/**
 * Smarty Resource Source Stream Plugin
 *
 * Implements the streams as resource for Smarty template
 *
 * @package Resource\Source
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source Stream Plugin
 *
 * Implements the streams as resource for Smarty template
 *
 * @link http://php.net/streams
 *
 * @package Resource\Source
 */
class Smarty_Resource_Source_Stream extends Smarty_Resource_Source_File
{
    /**
     * Flag that source must always be recompiled
     *
     * @var bool
     */
    public $recompiled = true;

    /**
     * Content from stream resource
     *
     * @var string
     */
    public $content = null;

    /**
     * This resource allows relative path
     *
     * @var false
     */
    public $_allow_relative_path = false;

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty_Context $context
     */
    public function populate(Smarty_Context $context)
    {
        $context->filepath = $this->buildFilepath($context);
        $context->uid = false;
        $context->timestamp = false;
        $context->exists = $this->getContent($context);
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  Smarty_Context $context
     * @return string           fully qualified filepath
     */
    public function buildFilepath(Smarty_Context $context)
    {
        if (strpos($context->name, '//') !== 0) {
            return $context->type . '://' . $context->name;
        } else {
            return $context->type . ':' . $context->name;
        }
    }

    /**
     * Load template's source from stream into current template object
     *
     * @return string           template source
     * @throws Smarty_Exception if source cannot be loaded
     */
    /**
     * Load template's source from stream into current template object
     *
     * @param Smarty_Context $context
     * @return boolean false|string
     */
    public function getContent(Smarty_Context $context)
    {
        if ($context->content !== null) {
            return $context->content;
        }
        // the availability of the stream has already been checked in Smarty_Resource_Source::fetch()
        $fp = fopen($context->filepath, 'r+');
        if ($fp) {
            while (!feof($fp) && ($current_line = fgets($fp)) !== false) {
                $context->content .= $current_line;
            }
            fclose($fp);

            if (isset($context->content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine basename for compiled filename
     *
     * Always returns an empty string.
     *
     * @param Smarty_Context $context
     * @return string resource's basename
     */
    public function getBasename(Smarty_Context $context)
    {
        return '';
    }
}
