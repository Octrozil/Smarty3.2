<?php

/**
 * Smarty Resource Source String Plugin
 *
 * @package Resource\Source
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source String Plugin
 *
 * Implements the strings as resource for Smarty template
 *
 * {@internal unlike eval-resources the compiled state of string-resources is saved for subsequent access}}
 *
 *
 * @package Resource\Source
 */
class Smarty_Resource_Source_String extends Smarty_Exception_Magic
{

    /**
     * Flag if source needs no compiler
     *
     * @var bool
     */
    public $uncompiled = false;

    /**
     * Flag if source needs to be always recompiled
     *
     * @var bool
     */
    public $recompiled = false;

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty $smarty Smarty object
     * @param Smarty_Source $source
     */
    public function populate(Smarty $smarty, $source)
    {
        $source->uid = $source->filepath = sha1($source->name);
        $source->timestamp = 0;
        $source->exists = true;
    }

    /**
     * Load template's source from $resource_name into current template object
     *
     * @uses decode() to decode base64 and urlencoded template_resources
     * @param Smarty_Source $source
     * @return string template source
     */
    public function getContent($source)
    {
        return $this->decode($source->name);
    }

    /**
     * decode base64 and urlencode
     *
     * @param  string $string template_resource to decode
     * @return string decoded template_resource
     */
    protected function decode($string)
    {
        // decode if specified
        if (($pos = strpos($string, ':')) !== false) {
            if (strpos($string, 'base64') === 0) {
                return base64_decode(substr($string, 7));
            } elseif (strpos($string, 'urlencode') === 0) {
                return urldecode(substr($string, 10));
            }
        }
        return $string;
    }

    /**
     * Determine basename for compiled filename
     *
     * Always returns an empty string.
     *
     * @return string resource's basename
     */
    public function getBasename()
    {
        return '';
    }

}
