<?php

ini_set('pcre.backtrack_limit', - 1);

/**
 * Smarty Code generator
 *
 * @package Smarty\Compiler
 * @author  Uwe Tews
 */

/**
 * Smarty Code generator
 * Methods to manage code output buffer
 *
 * @package Smarty\Compiler
 */
class Smarty_Compiler_Code extends Smarty_Exception_Magic
{

    public $buffer = '';
    public $indentation = 0;
    public $savedIndentation = 0;
    public $indentOn = true;
    public $noIndent = false;
    public $traceback = array();
    public $sourceLineNo = 0;
    public $lastLineNo = 0;
    public $bufferOffset = 0;
    public $bufferLineNo = 0;

    /**
     * Constructor
     *
     * @param int $indentation
     */
    public function __construct($indentation = 0)
    {
        $this->indentation = $indentation;
    }

    /**
     * init tag code block.
     *
     * @param  object $compiler compiler object
     *
     * @return object the current  instance
     */
    public function iniTagCode($compiler)
    {
        $this->buffer = '';
        $this->sourceLineNo = $compiler->lex->taglineno;
        $this->indentation = $this->savedIndentation = $compiler->template_code->indentation;
        $this->noIndent = ! $compiler->suppressNocacheProcessing && $compiler->context->caching && ($compiler->nocache || $compiler->tag_nocache || $compiler->forceNocache);

        return $this;
    }

    /**
     * return tag code.
     *
     * @param  object $compiler compiler object
     *
     * @return string of compiled code
     */
    public function returnTagCode($compiler)
    {
        $compiler->template_code->indentation = $this->indentation;
        $compiler->template_code->savedIndentation = $this->savedIndentation;

        return $this;
    }

    /**
     * Adds source line number
     *
     * @param  int $lineNo source line number
     *
     * @return Smarty_Compiler_code
     */
    public function addSourceLineNo($lineNo)
    {
        if ($lineNo != $this->lastLineNo) {
            $this->updateBufferInfo();
            $this->php("// line {$lineNo}")->newline();
            $this->traceback[$this->bufferLineNo] = $lineNo;
            $this->lastLineNo = $lineNo;
        }

        return $this;
    }

    /**
     * Merge trackeback
     *
     * @param  array $traceback
     *
     * @return Smarty_Compiler_code
     */
    public function mergeTraceBackInfo($traceback)
    {
        $this->updateBufferInfo();
        foreach ($traceback as $bufferLineNo => $lineNo) {
            $this->traceback[$this->bufferLineNo + $bufferLineNo] = $lineNo;
            $this->lastLineNo = $lineNo;
        }

        return $this;
    }

    /**
     * Merge other code buffer into current
     *
     * @param  Smarty_Compiler_Code $code
     *
     * @return Smarty_Compiler_code
     */
    public function mergeCode($code)
    {
        if ($code->sourceLineNo != 0) {
            $this->addSourceLineNo($code->sourceLineNo);
        }
        $this->mergeTraceBackInfo($code->traceback);
        $this->raw($code->buffer);
        return $this;
    }

    /**
     * Update buffer line number and offset
     *
     * @return Smarty_Compiler_code
     */
    public function updateBufferInfo()
    {
        // when mbstring.func_overload is set to 2
        // mb_substr_count() replaces substr_count()
        // but they have different signatures!
        if (ini_get('mbstring.func_overload') & 2) {
            $this->bufferLineNo += mb_substr_count(mb_substr($this->buffer, $this->bufferOffset), "\n");
        } else {
            $this->bufferLineNo += substr_count($this->buffer, "\n", $this->bufferOffset);
        }
        $this->bufferOffset = strlen($this->buffer);

        return $this;
    }

    /**
     * Enable indentation
     *
     * @return object the current instance
     */
    public function indentOn()
    {
        $this->indentOn = true;

        return $this;
    }

    /**
     * Enable indentation
     *
     * @return object the current instance
     */
    public function indent_off()
    {
        $this->indentOn = false;

        return $this;
    }

    /**
     * Adds a raw string to the compiled code.
     *
     * @param  string $string The string
     *
     * @return object the current instance
     */
    public function raw($string)
    {
        $this->buffer .= $string;

        return $this;
    }

    /**
     * Add an indentation to the current buffer.
     *
     * @return object the current instance
     */
    public function addIndentation()
    {
        if ($this->indentOn && ! $this->noIndent) {
            $this->buffer .= str_repeat(' ', $this->indentation * 4);
        }

        return $this;
    }

    /**
     * Add newline to the current buffer.
     *
     * @return object the current instance
     */
    public function newline()
    {
        if (! $this->noIndent) {
            $this->buffer .= "\n";
        }

        return $this;
    }

    /**
     * Add a line of PHP code to output.
     *
     * @param  string $value PHP source
     *
     * @return object the current instance
     */
    public function php($value)
    {
        $this->addIndentation();
        $this->buffer .= $value;

        return $this;
    }

    /**
     * Adds a quoted string to the compiled code.
     *
     * @param string $value        The string
     * @param bool   $double_quote flag if double quotes shall be used
     *
     * @return object the current instance
     */
    public function string($value, $double_quote = true)
    {
        $length = strlen($value);
        if ($length <= 1000) {
            if ($double_quote) {
                $this->buffer .= sprintf('"%s"', addcslashes($value, "\0\n\r\t\"\$\\"));
            } else {
                $this->buffer .= "'" . $value . "'";
            }
        } else {
            $i = 0;
            while (true) {
                if ($double_quote) {
                    $this->buffer .= sprintf('"%s"', addcslashes(substr($value, $i, 1000), "\0\n\r\t\"\$\\"));
                } else {
                    $this->buffer .= "'" . substr($value, $i, 1000) . "'";
                }
                if ($i == 0) {
                    $this->indent();
                }
                $i += 1000;
                if ($i >= $length) {
                    $this->outdent();
                    break;
                }
                $this->raw("\n")->addIndentation()->raw(', ');
            }
        }

        return $this;
    }

    /**
     * Adds the PHP representation of a given value to the current buffer
     *
     * @param  mixed $value        The value to convert
     * @param  bool  $double_qoute flag to use double quotes on strings
     *
     * @return object the current instance
     */
    public function repr($value, $double_qoute = true)
    {
        if (is_int($value) || is_float($value)) {
            if (false !== $locale = setlocale(LC_NUMERIC, 0)) {
                setlocale(LC_NUMERIC, 'C');
            }

            $this->raw($value);

            if (false !== $locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        } elseif (null === $value) {
            $this->raw('null');
        } elseif (is_bool($value)) {
            $this->raw($value ? 'true' : 'false');
        } elseif (is_array($value)) {
            $this->raw("array(\n")->indent(2)->addIndentation();
            $i = 0;
            foreach ($value as $key => $val) {
                if ($i ++) {
                    $this->raw(",\n")->addIndentation();
                }
                $this->repr($key, $double_qoute);
                $this->raw(' => ');
                $this->repr($val, $double_qoute);
            }
            $this->outdent()->raw("\n")->addIndentation()->raw(')')->outdent();
        } else {
            $this->string($value, $double_qoute);
        }

        return $this;
    }

    /**
     * Indents the generated code.
     *
     * @param  integer $step The number of indentation to add
     *
     * @return object  the current instance
     */
    public function indent($step = 1)
    {
        $this->indentation += $step;

        return $this;
    }

    /**
     * Outdents the generated code.
     *
     * @param integer $step The number of indentation to remove
     *
     * @throws Smarty_Exception
     * @return object           the current instance
     */
    public function outdent($step = 1)
    {
        // can't outdent by more steps that the current indentation level
        if ($this->indentation < $step) {
            throw new Smarty_Exception('Unable to call outdent() as the indentation would become negative');
        }
        $this->indentation -= $step;

        return $this;
    }

    /**
     * Format and add aPHP code block to current buffer.
     *
     * @param  string $value PHP source to format
     *
     * @return object the current instance
     */
    public function formatPHP($value)
    {
        $save = $this->indentOn;
        $this->indentOn = true;
        preg_replace_callback('%(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*")|([\r\n\t ]*(\?>|<\?php)[\r\n\t ]*)|(;[\r\n\t ]*)|({[\r\n\t ]*)|([\r\n\t ]*})|([\r\n\t ]*)|([\r\n\t ]*// line (\d*)[\r\n\t ]*)|(.*?(?=[\'";{}/\n]))%', array($this, '_processPHPoutput'), $value);
        $this->buffer .= "\n";
        $this->indentOn = $save;

        return $this;
    }

    /**
     * preg_replace callback function to process PHP output
     *
     * @param  string $match match string
     *
     * @return string replacement
     */
    public function _processPHPoutput($match)
    {
        if (empty($match[0]) || ! empty($match[2])) {
            return;
        }
//        if ($this->indentOn) {
//            $this->raw("\n");
//        }
        if (! empty($match[7])) {
            return;
        }
        if (! empty($match[1])) {
            $this->raw($match[1]);

            return;
        }
        if (! empty($match[4])) {
            $this->raw(";\n");
            $this->indentOn = true;

            return;
        }
        if (! empty($match[5])) {
            $this->raw("{\n")->indent();
            $this->indentOn = true;

            return;
        }
        if (! empty($match[6])) {
            $this->outdent()->addIndentation()->raw("}\n");
            return;
        }
        if (! empty($match[9])) {
            $this->addSourceLineNo($match[9]);

            return;
        }
        if (! empty($match[10])) {
            if ($this->indentOn) {
                $this->addIndentation();
            }
            $this->raw($match[10]);
            $this->indentOn = false;

            return;
        }

        return;
    }
}
