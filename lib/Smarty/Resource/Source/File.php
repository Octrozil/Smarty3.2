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
     * usage of this resource
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
    public $type = 'file';

    /**
     * resource UID
     *
     * @var string
     */
    public $uid = '';

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
     */
    public function populate(Smarty $smarty)
    {
        $this->filepath = $this->buildFilepath($smarty);

        if ($this->filepath !== false) {
            if (is_object($smarty->security_policy)) {
                $smarty->security_policy->isTrustedResourceDir($this->filepath);
            }
           $this->uid = sha1($this->filepath);
            if ($smarty->compile_check && !isset($this->timestamp)) {
                $this->timestamp = @filemtime($this->filepath);
                $this->exists = !!$this->timestamp;
            }
        }
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  Smarty $smarty template object
     * @return string           fully qualified filepath
     * @throws Smarty_Exception if default template handler is registered but not callable
     */
    public function buildFilepath(Smarty $smarty)
    {
        $file = str_replace('\\', '/', $this->name);
        if ($this->usage == Smarty::IS_CONFIG) {
            $_directories = $smarty->getConfigDir();
            $_default_handler = $smarty->default_config_handler_func;
        } else {
            $_directories = $smarty->getTemplateDir();
            $_default_handler = $smarty->default_template_handler_func;
        }

        // go relative to a given template?
        $_file_is_dotted = $file[0] == '.' && ($file[1] == '.' || $file[1] == '/');
        if ($_file_is_dotted && isset($smarty->parent) && $smarty->parent->usage == Smarty::IS_TEMPLATE) {
            if ($smarty->parent->source->type != 'file' && $smarty->parent->source->type != 'extends' && !$smarty->parent->allow_relative_path) {
                throw new Smarty_Exception_IllegalRelativePath($file, $smarty->parent->source->type);
            }
            $file = dirname($smarty->parent->source->filepath) . '/' . $file;
            $_file_exact_match = true;
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
                // the path gained from the parent template is relative to the current working directory
                // as expansions (like include_path) have already been done
                $file = getcwd() . '/' . $file;
            }
        }

        // resolve relative path
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            $_path = '/' . trim( $file, '/');
            $_was_relative = true;
        } else {
            $_path = $file;
        }
        $_path = $this->normalizePath($_path);

//      // revert to relative
        if (isset($_was_relative)) {
            $_path = substr($_path, 1);
        }

        // this is only required for directories
        $file = rtrim($_path, '/');

        // files relative to a template only get one shot
        if (isset($_file_exact_match)) {
            return $this->fileExists($file) ? $file : false;
        }

        // template_dir index?
        if (preg_match('#^\[(?P<key>[^\]]+)\](?P<file>.+)$#', $file, $match)) {
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
                if ($this->fileExists($_filepath)) {
                    return $_filepath;
                }
            }
        }

        $_stream_resolve_include_path = function_exists('stream_resolve_include_path');
        // relative file name?
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            foreach ($_directories as $_directory) {
                $_filepath = $_directory . $file;
                if ($this->fileExists($_filepath)) {
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
                        if ($this->fileExists($_filepath)) {
                            return $this->normalizePath($_filepath);
                        }
                    }
                }
            }
        }

        // try absolute filepath
        if ($this->fileExists($file)) {
            return $file;
        }

        // no tpl file found
        if ($_default_handler) {
            if (!is_callable($_default_handler)) {
                if ($smarty->usage == Smarty::IS_CONFIG) {
                    throw new DefaultHandlerNotCallable('config');
                } else {
                    throw new DefaultHandlerNotCallable('template');
                }
            }
            $_return = call_user_func_array($_default_handler, array($this->type, $this->name, &$_content, &$_timestamp, $smarty));
            if (is_string($_return)) {
                $this->timestamp = @filemtime($_return);
                $this->exists = !!$this->timestamp;

                return $_return;
            } elseif ($_return === true) {
                $this->content = $_content;
                $this->timestamp = $_timestamp;
                $this->exists = true;

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
     * @param  boolean $ds    respect windows directory separator
     * @return string  normalized path
     */
    protected function normalizePath($_path)
    {
        $offset = 0;
        // resolve simples
        $_path = preg_replace('#/\./(\./)*#', '/', $_path);
        // resolve parents
        while (true) {
            $_parent = strpos($_path, '/../', $offset);
            if (!$_parent) {
                break;
            } elseif ($_path[$_parent - 1] === '.') {
                $offset = $_parent + 3;
                continue;
            }

            $_pos = strrpos($_path, '/', $_parent - strlen($_path) - 1);
            if ($_pos === false) {
                // don't we all just love windows?
                $_pos = $_parent;
            }

            $_path = substr_replace($_path, '', $_pos, $_parent + 3 - $_pos);
        }
        return $_path;
    }

    /**
     * populate Resource with timestamp and exists
     *
     */
    public function populateTimestamp()
    {
        $this->filepath = $this->buildFilepath($smarty);
        $this->timestamp = @filemtime($this->filepath);
        $this->exists = !!$this->timestamp;
    }

    /**
     * read file
     *
     * @return boolean false|string
     */
    public function getContent()
    {
        if ($this->exists) {
            return file_get_contents($this->filepath);
        }
        return false;
    }

    /**
     * Determine basename for compiled filename
     *
     * @return string resource's basename
     */
    public function getBasename()
    {
        $_file = $this->name;
        if (($_pos = strpos($_file, ']')) !== false) {
            $_file = substr($_file, $_pos + 1);
        }

        return basename($_file);
    }

    /**
     * test is file exists and save timestamp
     *
     * @param  string $file file name
     * @return bool   true if file exists
     */
    public function fileExists($file)
    {
        $this->timestamp = @filemtime($file);
        return $this->exists = !!$this->timestamp;
    }

    /**
     * return unique name for this resource
     *
     * @param  Smarty $smarty            Smarty instance
     * @param  string $template_resource resource_name to make unique
     * @return string unique resource name
     */
    public function buildUniqueResourceName(Smarty $smarty, $template_resource)
    {
        return get_class($this) . '#' . $template_resource;
    }
}
