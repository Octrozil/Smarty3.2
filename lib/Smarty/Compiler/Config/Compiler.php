<?php

/**
 * Smarty Internal Plugin Config Compiler
 *
 * This is the config compiler class. It calls the lexer and parser to
 * perform the compiling.
 *
 *
 * @package Config
 * @author Uwe Tews
 */

/**
 * Main config file compiler class
 *
 *
 * @package Config
 */
class Smarty_Compiler_Config_Compiler extends Smarty_Compiler_Code
{

    /**
     * Lexer class name
     *
     * @var string
     */
    public $lexer_class = '';

    /**
     * Parser class name
     *
     * @var string
     */
    public $parser_class = '';

    /**
     * Lexer object
     *
     * @var object
     */
    public $lex;

    /**
     * Parser object
     *
     * @var object
     */
    public $parser;

    /**
     * current template
     *
     * @var Smarty
     */
    public $tpl_obj = null;

    /**
     * context object
     *
     * @var Smarty_Context
     */
    public $context = null;

    /**
     * compiled filepath
     *
     * @var string
     */
    public $filepath = null;
    /**
     * file dependencies
     *
     * @var array
     */
    public $file_dependency = array();

    /**
     * Compiled config data sections and variables
     *
     * @var array
     */
    public $config_data = array();

    /**
     * Initialize compiler
     *
     * @param string $lexer_class config lexer class name
     * @param string $parser_class config parser class name
     * @param Smarty_Context $context context object
     * @param $compiled_filepath
     */
    public function __construct($lexer_class, $parser_class, Smarty_Context $context, $compiled_filepath)
    {
        $this->lexer_class = $lexer_class;
        $this->parser_class = $parser_class;
        $this->tpl_obj = $context->smarty;
        $this->context = $context;
        $this->filepath = $compiled_filepath;
        $this->config_data['sections'] = array();
        $this->config_data['vars'] = array();
    }

    /**
     * Method to compile a Smarty config template.
     *
     * @return bool true if compiling succeeded, false if it failed
     */
    public function compileTemplateSource()
    {
        /* here is where the compiling takes place. Smarty
          tags in the templates are replaces with PHP code,
          then written to compiled files. */
        $this->file_dependency[$this->context->uid] = array($this->context->filepath, $this->context->timestamp, $this->context->type);
        // get config file source
        $_content = $this->context->getContent() . "\n";
        // on empty template just return
        if ($_content == '') {
            return true;
        }
        // init the lexer/parser to compile the config file
        $this->lex = new $this->lexer_class($_content, $this);
        $this->parser = new $this->parser_class($this->lex, $this);
        if (Smarty_Compiler::$parserdebug) {
            $this->parser->PrintTrace();
            $this->lex->PrintTrace();
        }
        // get tokens from lexer and parse them
        while ($this->lex->yylex()) {
            if (Smarty_Compiler::$parserdebug)
                echo "<br>Parsing  {$this->parser->yyTokenName[$this->lex->token]} Token {$this->lex->value} Line {$this->lex->line} \n";
            $this->parser->doParse($this->lex->token, $this->lex->value);
        }
        // finish parsing process
        $this->parser->doParse(0, 0);
        // init code buffer
        $this->buffer = '';
        $this->indentation = 0;
        // content class name
        $class = '_SmartyTemplate_' . str_replace('.', '_', uniqid('', true));
        $this->raw("<?php")->newline();
        $this->raw("/* Smarty version " . Smarty::SMARTY_VERSION . ", created on " . strftime("%Y-%m-%d %H:%M:%S") . " compiled from \"" . $this->context->filepath . "\" */")->newline();
        $this->php("if (!class_exists('{$class}',false)) {")->newline()->indent()->php("class {$class} extends Smarty_Template {")->newline()->indent();
        $this->php("public \$version = '" . Smarty::SMARTY_VERSION . "';")->newline();
        $this->php("public \$file_dependency = ")->repr($this->file_dependency, false)->raw(";")->newline()->newline();
        $this->php("public \$config_data = ")->repr($this->config_data)->raw(";")->newline()->newline();

        $this->outdent()->php("}")->newline()->outdent()->php("}")->newline();
        $this->php("\$template_class_name = '{$class}';")->newline();

        $this->tpl_obj->_writeFile($this->filepath, $this->buffer);
        $this->buffer = '';
        $this->config_data = array();
        $this->lex->compiler = null;
        $this->parser->compiler = null;
        $this->lex = null;
        $this->parser = null;
    }

    /**
     * display compiler error messages without dying
     *
     * If parameter $args is empty it is a parser detected syntax error.
     * In this case the parser is called to obtain information about exspected tokens.
     *
     * If parameter $args contains a string this is used as error message
     *
     * @param  string $args individual error message or null
     * @throws Smarty_Exception_Compiler
     */
    public function error($args = null)
    {
        // get template source line which has error
        $line = $this->lex->line;
        if (isset($args)) {
            // $line--;
        }
        $match = preg_split("/\n/", $this->lex->data);
        $error_text = "Syntax error in config file '{$this->context->filepath}' on line {$line} '{$match[$line - 1]}' ";
        if (isset($args)) {
            // individual error message
            $error_text .= $args;
        } else {
            // exspected token from parser
            foreach ($this->parser->yy_get_expected_tokens($this->parser->yymajor) as $token) {
                $exp_token = $this->parser->yyTokenName[$token];
                if (isset($this->lex->smarty_token_names[$exp_token])) {
                    // token type from lexer
                    $expect[] = '"' . $this->lex->smarty_token_names[$exp_token] . '"';
                } else {
                    // otherwise internal token name
                    $expect[] = $this->parser->yyTokenName[$token];
                }
            }
            // output parser error message
            $error_text .= ' - Unexpected "' . $this->lex->value . '", expected one of: ' . implode(' , ', $expect);
        }
        throw new Smarty_Exception_Compiler($error_text);
    }

}
