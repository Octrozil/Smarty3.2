<?php
/**
 * Smarty Config Lexer
 *
 * This is the lexer to break the config file source into tokens
 * @package Smarty
 * @subpackage Config
 * @author Uwe Tews
 */
/**
 * Smarty Compiler Config Lexer
 */
class Smarty_Compiler_Config_Lexer extends Smarty_Exception_Magic
{

    public $data;
    public $counter;
    public $token;
    public $value;
    public $node;
    public $line;
    public $compiler;
    public $mbstring_overload;
    private $state = 1;
    public static $yyTraceFILE;
    public static $yyTracePrompt;
    public $smarty_token_names = array( // Text for parser error messages
    );


    function __construct($data, $compiler)
    {
        $this->data = $data . "\n"; //now all lines are \n-terminated
        $this->counter = 0;
        $this->line = 1;
        $this->compiler = $compiler;
        $this->mbstring_overload = ini_get('mbstring.func_overload') & 2;
    }


    private $_yy_state = 1;
    private $_yy_stack = array();

    public function yylex()
    {
        return $this->{'yylex' . $this->_yy_state}();
    }

    public function yypushstate($state)
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sState push %d\n", self::$yyTracePrompt, $state);
        }
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
    }

    public function yypopstate()
    {
        $this->_yy_state = array_pop($this->_yy_stack);
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sState pop %d\n", self::$yyTracePrompt, $this->_yy_state);
        }

    }

    public function yybegin($state)
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sState set %d\n", self::$yyTracePrompt, $state);
        }
        $this->_yy_state = $state;
    }


    public function yylex1()
    {
        $tokenMap = array(
            1 => 0,
            2 => 0,
        );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\xEF\xBB\xBF|\xFE\xFF|\xFF\xFE)|\G([\s\S]?)/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter, 2000000000, 'latin1'), $yymatches) : preg_match($yy_global_pattern, $this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                            $this->counter, 5) . '... state BOM');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r1_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const BOM = 1;

    function yy_r1_1($yy_subpatterns)
    {

        $this->yypushstate(self::START);
        return false;
    }

    function yy_r1_2($yy_subpatterns)
    {

        $this->yypushstate(self::START);
        return true;
    }


    public function yylex2()
    {
        $tokenMap = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 0,
        );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(#|;)|\G(\\[)|\G(\\])|\G(=)|\G([ \t\r]+)|\G(\n)|\G([0-9]*[a-zA-Z_]\\w*)|\G([\S\s])/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter, 2000000000, 'latin1'), $yymatches) : preg_match($yy_global_pattern, $this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                            $this->counter, 5) . '... state START');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r2_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const START = 2;

    function yy_r2_1($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_COMMENTSTART;
        $this->yypushstate(self::COMMENT);
    }

    function yy_r2_2($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_OPENB;
        $this->yypushstate(self::SECTION);
    }

    function yy_r2_3($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_CLOSEB;
    }

    function yy_r2_4($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_EQUAL;
        $this->yypushstate(self::VALUE);
    }

    function yy_r2_5($yy_subpatterns)
    {

        return false;
    }

    function yy_r2_6($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_NEWLINE;
    }

    function yy_r2_7($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_ID;
    }

    function yy_r2_8($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_OTHER;
    }


    public function yylex3()
    {
        $tokenMap = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 0,
            9 => 0,
        );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G([ \t\r]+)|\G(\\d+\\.\\d+(?=[ \t\r]*[\n#;]))|\G(\\d+(?=[ \t\r]*[\n#;]))|\G(\"\"\")|\G('[^'\\\\]*(?:\\\\.[^'\\\\]*)*'(?=[ \t\r]*[\n#;]))|\G(\"[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*\"(?=[ \t\r]*[\n#;]))|\G([a-zA-Z]+(?=[ \t\r]*[\n#;]))|\G([^\n]+?(?=[ \t\r]*\n))|\G(\n)/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter, 2000000000, 'latin1'), $yymatches) : preg_match($yy_global_pattern, $this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                            $this->counter, 5) . '... state VALUE');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r3_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const VALUE = 3;

    function yy_r3_1($yy_subpatterns)
    {

        return false;
    }

    function yy_r3_2($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_FLOAT;
        $this->yypopstate();
    }

    function yy_r3_3($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_INT;
        $this->yypopstate();
    }

    function yy_r3_4($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_TRIPPLE_QUOTES;
        $this->yypushstate(self::TRIPPLE);
    }

    function yy_r3_5($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_SINGLE_QUOTED_STRING;
        $this->yypopstate();
    }

    function yy_r3_6($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_DOUBLE_QUOTED_STRING;
        $this->yypopstate();
    }

    function yy_r3_7($yy_subpatterns)
    {

        if (!$this->compiler->context->smarty->config_booleanize || !in_array(strtolower($this->value), Array("true", "false", "on", "off", "yes", "no"))) {
            $this->yypopstate();
            $this->yypushstate(self::NAKED_STRING_VALUE);
            return true; //reprocess in new state
        } else {
            $this->token = Smarty_Compiler_Config_Parser::TPC_BOOL;
            $this->yypopstate();
        }
    }

    function yy_r3_8($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_NAKED_STRING;
        $this->yypopstate();
    }

    function yy_r3_9($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_NAKED_STRING;
        $this->value = "";
        $this->yypopstate();
    }


    public function yylex4()
    {
        $tokenMap = array(
            1 => 0,
        );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G([^\n]+?(?=[ \t\r]*\n))/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter, 2000000000, 'latin1'), $yymatches) : preg_match($yy_global_pattern, $this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                            $this->counter, 5) . '... state NAKED_STRING_VALUE');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r4_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const NAKED_STRING_VALUE = 4;

    function yy_r4_1($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_NAKED_STRING;
        $this->yypopstate();
    }


    public function yylex5()
    {
        $tokenMap = array(
            1 => 0,
            2 => 0,
            3 => 0,
        );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G([ \t\r]+)|\G([^\n]+?(?=[ \t\r]*\n))|\G(\n)/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter, 2000000000, 'latin1'), $yymatches) : preg_match($yy_global_pattern, $this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                            $this->counter, 5) . '... state COMMENT');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r5_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const COMMENT = 5;

    function yy_r5_1($yy_subpatterns)
    {

        return false;
    }

    function yy_r5_2($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_NAKED_STRING;
    }

    function yy_r5_3($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_NEWLINE;
        $this->yypopstate();
    }


    public function yylex6()
    {
        $tokenMap = array(
            1 => 0,
            2 => 0,
        );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\\.)|\G(.*?(?=[\.=[\]\r\n]))/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter, 2000000000, 'latin1'), $yymatches) : preg_match($yy_global_pattern, $this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                            $this->counter, 5) . '... state SECTION');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r6_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const SECTION = 6;

    function yy_r6_1($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_DOT;
    }

    function yy_r6_2($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_SECTION;
        $this->yypopstate();
    }


    public function yylex7()
    {
        $tokenMap = array(
            1 => 0,
            2 => 0,
        );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\"\"\"(?=[ \t\r]*[\n#;]))|\G([\S\s])/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter, 2000000000, 'latin1'), $yymatches) : preg_match($yy_global_pattern, $this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                            $this->counter, 5) . '... state TRIPPLE');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r7_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value, 'latin1') : strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data, 'latin1') : strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }
            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const TRIPPLE = 7;

    function yy_r7_1($yy_subpatterns)
    {

        $this->token = Smarty_Compiler_Config_Parser::TPC_TRIPPLE_QUOTES_END;
        $this->yypopstate();
        $this->yypushstate(self::START);
    }

    function yy_r7_2($yy_subpatterns)
    {

        if ($this->mbstring_overload) {
            $to = mb_strlen($this->data, 'latin1');
        } else {
            $to = strlen($this->data);
        }
        preg_match("/\"\"\"[ \t\r]*[\n#;]/", $this->data, $match, PREG_OFFSET_CAPTURE, $this->counter);
        if (isset($match[0][1])) {
            $to = $match[0][1];
        } else {
            $this->compiler->error("missing or misspelled literal closing tag");
        }
        if ($this->mbstring_overload) {
            $this->value = mb_substr($this->data, $this->counter, $to - $this->counter, 'latin1');
        } else {
            $this->value = substr($this->data, $this->counter, $to - $this->counter);
        }
        $this->token = Smarty_Compiler_Config_Parser::TPC_TRIPPLE_TEXT;
    }


}
