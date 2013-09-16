<?php

/**
 * Smarty Resource Source File Plugin
 *
 * @package Resource\Source
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Resource Source File Plugin
 *
 * Implements the file system as resource for Smarty templates
 *
 * @package Resource\Source
 */
class Smarty_Resource_Source_File extends Smarty_Exception_Magic
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
     * This resource allows relative path
     *
     * @var true
     */
    public $_allow_relative_path = true;

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty            $smarty Smarty object
     * @param Smarty_Source     $source Source object
     * @param Smarty            $parent
     */
    public function populate(Smarty $smarty, Smarty_Source $source, $parent = null)
    {
        $source->filepath = $this->buildFilepath($smarty, $source, $parent = null);

        if ($source->filepath !== false) {
            if (is_object($smarty->security_policy)) {
                $smarty->security_policy->isTrustedResourceDir($source->filepath);
            }
            $source->uid = sha1($source->filepath);
        }
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  Smarty           $smarty template object
     * @param  Smarty_Source    $source Source object
     * @throws DefaultHandlerNotCallable
     * @throws Smarty_Exception_IllegalRelativePath
     * @return string           fully qualified filepath
     */
    public function buildFilepath(Smarty $smarty, $source, $parent = null)
    {
        $file = $source->name;

        // go relative to a given template?
        $_file_is_dotted = $file[0] == '.' && ($file[1] == '.' || $file[1] == '/' || $file[1] == '\\');
        if ($_file_is_dotted && isset($parent) && $parent->_usage == Smarty::IS_SMARTY_TPL_CLONE) {
            if (!isset($parent->source->handler->allow_relative_path)) {
                throw new Smarty_Exception_IllegalRelativePath($file, $smarty->parent->source->type);
            }
            // get absolute path relative to given template
            $file = dirname($smarty->parent->source->filepath) . '/' . $file;
            $_file_exact_match = true;
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
                // the path gained from the parent template is relative to the current working directory
                // as expansions (like include_path) have already been done
                $file = getcwd() . '/' . $file;
            }
        } else if (!isset($_file_exact_match) && ($file[0] == '/' || $file[0] == '\\' || ($file[1] == ':' && preg_match('/^([a-zA-Z]:[\/\\\\])/', $file)))) {
            // was absolute path
            $_file_exact_match = true;
        }
        // process absolute path
        if (isset($_file_exact_match)) {
            if ($this->fileExists($file, $source)) {
                return $this->normalizePath($file);
            }
            return false;
        }

        // get source directories
        if ($source->_usage == Smarty::IS_CONFIG) {
            $_directories = $smarty->getConfigDir();
        } else {
            $_directories = $smarty->getTemplateDir();
        }
        // template_dir index?
        if ($file[0] == '[' && preg_match('#^\[(?P<key>[^\]]+)\](?P<file>.+)$#', $file, $match)) {
            $_directory = null;
            // try string indexes
            if (isset($_directories[$match['key']])) {
                $_directory = $_directories[$match['key']];
            } elseif (is_numeric($match['key'])) {
                // try numeric index
                $match['key'] = (int)$match['key'];
                if (isset($_directories[$match['key']])) {
                    $_directory = $_directories[$match['key']];
                } else {
                    // try at location index
                    $keys = array_keys($_directories);
                    $_directory = $_directories[$keys[$match['key']]];
                }
            }

            if ($_directory) {
                $_file = substr($file, strpos($file, ']') + 1);
                $_filepath = $_directory . $_file;
                if ($this->fileExists($_filepath, $source)) {
                    $_filepath = $this->normalizePath($_filepath);
                    return $_filepath;
                }
            }
        }

        $_stream_resolve_include_path = function_exists('stream_resolve_include_path');
        foreach ($_directories as $_directory) {
            $_filepath = $_directory . $file;
            if ($this->fileExists($_filepath, $source)) {
                return $this->normalizePath($_filepath);
            }
            if ($smarty->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_directory)) {
                // try PHP include_path
                if ($_stream_resolve_include_path) {
                    $_filepath = stream_resolve_include_path($_filepath);
                } else {
                    $_filepath = $smarty->getIncludePath($_filepath);
                }
                if ($_filepath !== false) {
                    if ($this->fileExists($_filepath, $source)) {
                        return $this->normalizePath($_filepath);
                    }
                }
            }
        }

        // no source file found check default handler
        if ($source->_usage == Smarty::IS_CONFIG) {
            $_default_handler = $smarty->default_config_handler_func;
        } else {
            $_default_handler = $smarty->default_template_handler_func;
        }
        if ($_default_handler) {
            if (!is_callable($_default_handler)) {
                if ($smarty->_usage == Smarty::IS_CONFIG) {
                    throw new Smarty_Exception_DefaultHandlerNotCallable('config');
                } else {
                    throw new Smarty_Exception_DefaultHandlerNotCallable('template');
                }
            }
            $_filepath = call_user_func_array($_default_handler, array($source->type, $source->name, &$_content, &$_timestamp, $smarty));
            if (is_string($_filepath)) {
                if ($this->fileExists($_filepath, $source)) {
                    return $this->normalizePath($_filepath);
                }
                return false;
            } elseif ($_filepath === true) {
                $source->content = $_content;
                $source->timestamp = $_timestamp;
                $source->exists = true;
                return $_filepath;
            }
        }

        // give up
        return false;
    }

    /**
     * Normalize Paths "foo/../bar" to "bar"
     *
     * @param  string $_path path to normalize
     * @return string  normalized path
     */
    function normalizePath($path)
    {
        if (strpos($path, '\\') !== false) {
            $path = str_replace('\\', '/', $path);
        }
        $out = array();
        foreach (explode('/', $path) as $i => $fold) {
            if ($fold == '' || $fold == '.') continue;
            if ($fold == '..' && $i > 0 && end($out) != '..') array_pop($out);
            else $out[] = $fold;
        }
        return ($path{0} == '/' ? '/' : '') . join('/', $out);
    }


    /**
     * read file content
     *
     * @param Smarty_Source $source
     * @return boolean false|string
     */
    public function getContent($source)
    {
        if ($source->exists) {
            return file_get_contents($source->filepath);
        }
        return false;
    }

    /**
     * Determine basename for compiled filename
     *
     * @param Smarty_Source $source
     * @return string resource's basename
     */
    public function getBasename($source)
    {
        $_file = $source->name;
        if (($_pos = strpos($_file, ']')) !== false) {
            $_file = substr($_file, $_pos + 1);
        }

        return basename($_file);
    }

    /**
     * test is file exists and save timestamp
     *
     * @param  string $file file name
     * @param  Smarty_Source $source
     * @return bool   true if file exists
     */
    public function fileExists($file, $source)
    {
        if ($source->exists = is_file($file)) {
            $source->timestamp = filemtime($file);
        }
        return $source->exists;
    }

    /**
     * return unique name for this resource
     *
     * @param  Smarty $smarty            Smarty instance
     * @param  string $template_resource resource_name to make unique
     * @param  Smarty | null $parent
     * @return string unique resource name
     */
    public function buildUniqueResourceName(Smarty $smarty, $template_resource, $parent = null)
    {
        if ($parent == null) {
            return get_class($this) . '#' . $template_resource;
        } else if ($parent->_usage == Smarty::IS_SMARTY_TPL_CLONE && isset($parent->source->handler->_allow_relative_path)
            && $template_resource[0] == '.' && ($template_resource[1] == '.' || $template_resource[1] == '/' || $template_resource[1] == '\\')
        ) {
            // return key for relative path
            return $smarty->_joined_template_dir . '#' . dirname($parent->source->filepath) . '/' . $template_resource;
        } else {
            return false;
        }
    }
}
