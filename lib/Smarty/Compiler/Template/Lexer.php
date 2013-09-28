<?php
/**
* Smarty Template Lexer
*
* This is the lexer to break the template source into tokens 
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews 
*/
/**
* Smarty Template Lexer
*/
class Smarty_Compiler_Template_Lexer extends Smarty_Exception_Magic
{
    public $data = null;
    public $counter = null;
    public $token = null;
    public $value = null;
    public $node = null;
    public $line = 0;
    public $taglineno = 1;
    public $line_offset = 0;
    public $state = 1;
    public $compiler;
    public $parser_class;
    Public $ldel;
    Public $rdel;
    Public $rdel_length;
    Public $ldel_length;
    Public $dqtag = false;
    Public $mbstring_overload;
    private $heredoc_id_stack = Array();
    public static $yyTraceFILE;
    public static $yyTracePrompt;

    public $smarty_token_names = array (		// Text for parser error messages
                    'IFCOND'    => '(==,eq,!=,<>,neq,ne,>,gt,<,lt,>=,ge,gte,<=,le,lte,===,!==,%,mod)',
//    				'IDENTITY'	=> '===',
//    				'NONEIDENTITY'	=> '!==',
//    				'EQUALS'	=> '==',
//    				'NOTEQUALS'	=> '!=',
//    				'GREATEREQUAL' => '(>=,ge)',
//    				'LESSEQUAL' => '(<=,le)',
//    				'GREATERTHAN' => '(>,gt)',
//    				'LESSTHAN' => '(<,lt)',
//    				'MOD' => '(%,mod)',
    				'NOT'			=> '(!,not)',
    				'LPO'		=> '(&&,and,||,or,xor)',
//    				'LAND'		=> '(&&,and)',
//    				'LOR'			=> '(|,,or)',
//    				'LXOR'			=> 'xor',
    				'OPENP'		=> '(',
    				'CLOSEP'	=> ')',
    				'OPENB'		=> '[',
    				'CLOSEB'	=> ']',
    				'PTR'			=> '->',
    				'APTR'		=> '=>',
    				'EQUAL'		=> '=',
    				'NUMBER'	=> 'number',
    				'INTEGER'	=> 'Integer',
    				'UNIMATH'	=> '+" , "-',
    				'MATH'		=> '*" , "/" , "%',
    				'SPACE'		=> ' ',
    				'DOLLAR'	=> '$',
    				'SEMICOLON' => ';',
    				'COLON'		=> ':',
    				'DOUBLECOLON'		=> '::',
    				'AT'		=> '@',
    				'HATCH'		=> '#',
    				'QUOTE'		=> '"',
    				'BACKTICK'		=> '`',
    				'VERT'		=> '|',
    				'DOT'			=> '.',
    				'COMMA'		=> '","',
    				'ANDSYM'		=> '"&"',
    				'QMARK'		=> '"?"',
    				'ID'			=> 'identifier',
    				'TEXT'		=> 'text',
     				'FAKEPHPSTARTTAG'	=> 'Fake PHP start tag',
     				'PHPSTARTTAG'	=> 'PHP start tag',
     				'PHPENDTAG'	=> 'PHP end tag',
 					'LITERALSTART'  => 'Literal start',
 					'LITERALEND'    => 'Literal end',
    				'LDELSLASH' => 'closing tag',
    				'COMMENT' => 'comment',
    				'AS' => 'as',
    				'TO' => 'to',
    				);
    				
    				
    function __construct($data,$compiler)
    {
//        $this->data = preg_replace("/(\r\n|\r|\n)/", "\n", $data);
        
        if ($data !==null){
           $this->data = $data;
        }
        $this->counter = 0;
        $this->line = 1;
        $this->line_offset = $compiler->line_offset;
        $this->compiler = $compiler;
        $this->ldel = preg_quote($this->compiler->tpl_obj->left_delimiter,'/'); 
        $this->ldel_length = strlen($this->compiler->tpl_obj->left_delimiter); 
        $this->rdel = preg_quote($this->compiler->tpl_obj->right_delimiter,'/');
        $this->rdel_length = strlen($this->compiler->tpl_obj->right_delimiter); 
        $this->smarty_token_names['LDEL'] =	$this->compiler->tpl_obj->left_delimiter;
        $this->smarty_token_names['RDEL'] =	$this->compiler->tpl_obj->right_delimiter;
        $this->mbstring_overload = ini_get('mbstring.func_overload') & 2;
     }

    public static function PrintTrace()
    {
        self::$yyTraceFILE = fopen('php://output', 'w');
        self::$yyTracePrompt = '<br>';
    }

     function autoLiteral ($value) {
        if ($this->compiler->tpl_obj->auto_literal) {
            if (false !== $pos = strrpos($value, '-')) {
                $pos++;
            } else if (false !== $pos = strrpos($value, $this->compiler->tpl_obj->left_delimiter)) {
                $pos += strlen ($this->compiler->tpl_obj->left_delimiter);
            }
            if (isset($value[$pos]) && $c = strpbrk($value[$pos], " \n\t\r")) {
                return true;
            }
        }
        return false;
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
             fprintf(self::$yyTraceFILE, "%sState pop %d\n", self::$yyTracePrompt,  $this->_yy_state);
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
        $tokenMap = array (
              1 => 0,
              2 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\xEF\xBB\xBF|\xFE\xFF|\xFF\xFE)|\G([\s\S]?)/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter,2000000000,'latin1'), $yymatches) : preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
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
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const BOM = 1;
    function yy_r1_1($yy_subpatterns)
    {

     $parser_class = $this->parser_class = get_class($this->compiler->parser);
     $this->token = $parser_class::TP_TEMPLATEINIT;
     $this->yypushstate(self::TEXT);
    }
    function yy_r1_2($yy_subpatterns)
    {

     $this->value = '';
     $parser_class = $this->parser_class = get_class($this->compiler->parser);
     $this->token = $parser_class::TP_TEMPLATEINIT;
     $this->yypushstate(self::TEXT);
    }


    public function yylex2()
    {
        $tokenMap = array (
              1 => 0,
              2 => 2,
              5 => 1,
              7 => 2,
              10 => 1,
              12 => 1,
              14 => 1,
              16 => 2,
              19 => 2,
              22 => 3,
              26 => 1,
              28 => 0,
              29 => 0,
              30 => 0,
              31 => 0,
              32 => 1,
              34 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\\{\\})|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\/strip\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*(if|elseif|else if|while)\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*for\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*foreach(?![^\s]))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*strip\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*literal\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\\*([\S\s]*?)\\*\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*)|\G(<\\?(?:php\\w+|=|[a-zA-Z]+)?)|\G(\\?>)|\G(<%)|\G(%>)|\G(\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G([\S\s])/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter,2000000000,'latin1'), $yymatches) : preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state TEXT');
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
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const TEXT = 2;
    function yy_r2_1($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
     $this->token = $parser_class::TP_TEXT;
    }
    function yy_r2_2($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
    $this->token = $parser_class::TP_STRIPOFF;
  }
    }
    function yy_r2_5($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
     $this->token = $parser_class::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    function yy_r2_7($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
     $this->token = $parser_class::TP_LDELIF;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r2_10($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
     $this->token = $parser_class::TP_LDELFOR;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r2_12($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
     $this->token = $parser_class::TP_LDELFOREACH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r2_14($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
    $this->token = $parser_class::TP_TEXT;
  } else {
    $this->token = $parser_class::TP_LDELSLASH;
    $this->yypushstate(self::SMARTY);
    $this->taglineno = $this->line;
  }
    }
    function yy_r2_16($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
   if ($this->autoLiteral($this->value)) {
    $this->token = $parser_class::TP_TEXT;
  } else {
    $this->token = $parser_class::TP_STRIPON;
  }
    }
    function yy_r2_19($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
    $this->token = $parser_class::TP_LITERALSTART;
    $this->yypushstate(self::LITERAL);
   }
    }
    function yy_r2_22($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
    $this->token = $parser_class::TP_TEXT;
  } else {
    $this->token = $parser_class::TP_COMMENT;
    $this->taglineno = $this->line;
  }
    }
    function yy_r2_26($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
    $this->token = $parser_class::TP_TEXT;
  } else {
     $this->token = $parser_class::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r2_28($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if (in_array($this->value, Array('<?', '<?=', '<?php'))) {
    $this->token = $parser_class::TP_PHPSTARTTAG;
  } elseif ($this->value == '<?xml') {
      $this->token = $parser_class::TP_XMLTAG;
  } else {
    $this->token = $parser_class::TP_FAKEPHPSTARTTAG;
    $this->value = substr($this->value, 0, 2);
  }
     }
    function yy_r2_29($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_PHPENDTAG;
    }
    function yy_r2_30($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ASPSTARTTAG;
    }
    function yy_r2_31($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ASPENDTAG;
    }
    function yy_r2_32($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
     $this->token = $parser_class::TP_TEXT;
     $this->yypopstate();
    }
    function yy_r2_34($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->mbstring_overload) {
    $to = mb_strlen($this->data,'latin1');
  } else {
  $to = strlen($this->data);
  }
  preg_match("/\s*{$this->ldel}--|[^\S\r\n]*{$this->ldel}-|{$this->ldel}|{$this->rdel}|<\?|\?>|<%|%>/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
  if (isset($match[0][1])) {
    $to = $match[0][1];
  }
  if ($this->mbstring_overload) {
    $this->value = mb_substr($this->data,$this->counter,$to-$this->counter,'latin1');
  } else {
  $this->value = substr($this->data,$this->counter,$to-$this->counter);
  }
  $this->token = $parser_class::TP_TEXT;
    }


    public function yylex3()
    {
        $tokenMap = array (
              1 => 1,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 0,
              8 => 0,
              9 => 0,
              10 => 4,
              15 => 0,
              16 => 3,
              20 => 0,
              21 => 0,
              22 => 0,
              23 => 0,
              24 => 0,
              25 => 0,
              26 => 0,
              27 => 0,
              28 => 0,
              29 => 0,
              30 => 3,
              34 => 0,
              35 => 0,
              36 => 0,
              37 => 0,
              38 => 0,
              39 => 0,
              40 => 0,
              41 => 1,
              43 => 1,
              45 => 1,
              47 => 0,
              48 => 0,
              49 => 0,
              50 => 0,
              51 => 0,
              52 => 0,
              53 => 0,
              54 => 0,
              55 => 0,
              56 => 0,
              57 => 0,
              58 => 0,
              59 => 0,
              60 => 0,
              61 => 0,
              62 => 0,
              63 => 0,
              64 => 1,
              66 => 2,
              69 => 1,
              71 => 1,
              73 => 3,
              77 => 1,
              79 => 1,
              81 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|\G(\")|\G(\\s+is\\s+in\\s+)|\G(\\s+as\\s+)|\G(\\s+to\\s+)|\G(\\s+step\\s+)|\G(\\s+instanceof\\s+)|\G((\\s*(>=|<=|===|==|!==|!=|<>|>|<)\\s*)|(\\s+(eq|neq|ne|gt|lt|ge|gte|le|lte|mod)\\s+))|\G(!\\s*|not\\s+)|\G(\\s*((&&|\\|\\|)\\s*|(and|or|xor)\\s+))|\G(\\s+is\\s+odd\\s+by\\s+)|\G(\\s+is\\s+not\\s+odd\\s+by\\s+)|\G(\\s+is\\s+odd)|\G(\\s+is\\s+not\\s+odd)|\G(\\s+is\\s+even\\s+by\\s+)|\G(\\s+is\\s+not\\s+even\\s+by\\s+)|\G(\\s+is\\s+even)|\G(\\s+is\\s+not\\s+even)|\G(\\s+is\\s+div\\s+by\\s+)|\G(\\s+is\\s+not\\s+div\\s+by\\s+)|\G(\\((int(eger)?|bool(ean)?|float|double|real|string|binary|array|object)\\)\\s*)|\G(\\s*\\(\\s*)|\G(\\s*\\))|\G(\\[\\s*)|\G(\\s*\\])|\G(\\s*->\\s*)|\G(\\s*=>\\s*)|\G(\\s*=\\s*)|\G(\\s*(\\+|-)\\s*)|\G(\\s*(\\*|\/|%)\\s*)|\G(\\$[0-9]*[a-zA-Z_]\\w*(\\+\\+|--))|\G(\\$)|\G(\\s*;)|\G(::)|\G(\\s*:\\s*)|\G(@)|\G(#)|\G(`)|\G(\\|)|\G(\\.)|\G(\\s*,\\s*)|\G(\\s*&\\s*)|\G(\\s*\\?\\s*)|\G(0[xX][0-9a-fA-F]+|\\d+\\.\\d+)|\G(\\s+[0-9]*[a-zA-Z_][a-zA-Z0-9_\-:]*\\s*=\\s*)|\G([0-9]*[a-zA-Z_]\\w*)|\G(\\d+)|\G(\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*(if|elseif|else if|while)\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*for\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*foreach(?![^\s]))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\\*([\S\s]*?)\\*\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*)|\G((\\\\[0-9]*[a-zA-Z_]\\w*)+)|\G([\S\s])/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter,2000000000,'latin1'), $yymatches) : preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state SMARTY');
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
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const SMARTY = 3;
    function yy_r3_1($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
     $this->token = $parser_class::TP_RDEL;
     $this->yypopstate();
    }
    function yy_r3_3($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_SINGLEQUOTESTRING;
    }
    function yy_r3_4($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_QUOTE;
  $this->yypushstate(self::DOUBLEQUOTEDSTRING);
    }
    function yy_r3_5($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISIN;
    }
    function yy_r3_6($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_AS;
    }
    function yy_r3_7($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_TO;
    }
    function yy_r3_8($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_STEP;
    }
    function yy_r3_9($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_INSTANCEOF;
    }
    function yy_r3_10($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_IFCOND;
    }
    function yy_r3_15($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_NOT;
    }
    function yy_r3_16($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_LOP;
    }
    function yy_r3_20($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISODDBY;
    }
    function yy_r3_21($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISNOTODDBY;
    }
    function yy_r3_22($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISODD;
    }
    function yy_r3_23($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISNOTODD;
    }
    function yy_r3_24($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISEVENBY;
    }
    function yy_r3_25($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISNOTEVENBY;
    }
    function yy_r3_26($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISEVEN;
    }
    function yy_r3_27($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISNOTEVEN;
    }
    function yy_r3_28($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISDIVBY;
    }
    function yy_r3_29($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ISNOTDIVBY;
    }
    function yy_r3_30($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_TYPECAST;
    }
    function yy_r3_34($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_OPENP;
    }
    function yy_r3_35($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_CLOSEP;
    }
    function yy_r3_36($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_OPENB;
    }
    function yy_r3_37($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_CLOSEB;
    }
    function yy_r3_38($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_PTR;
    }
    function yy_r3_39($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_APTR;
    }
    function yy_r3_40($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_EQUAL;
    }
    function yy_r3_41($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_UNIMATH;
    }
    function yy_r3_43($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_MATH;
    }
    function yy_r3_45($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_IDINCDEC;
    }
    function yy_r3_47($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_DOLLAR;
    }
    function yy_r3_48($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_SEMICOLON;
    }
    function yy_r3_49($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_DOUBLECOLON;
    }
    function yy_r3_50($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_COLON;
    }
    function yy_r3_51($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_AT;
    }
    function yy_r3_52($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_HATCH;
    }
    function yy_r3_53($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_BACKTICK;
  $this->yypopstate();
    }
    function yy_r3_54($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_VERT;
    }
    function yy_r3_55($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_DOT;
    }
    function yy_r3_56($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_COMMA;
    }
    function yy_r3_57($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ANDSYM;
    }
    function yy_r3_58($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_QMARK;
    }
    function yy_r3_59($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_NUMBER;
    }
    function yy_r3_60($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  // resolve conflicts with shorttag and right_delimiter starting with '='
  if (substr($this->data, $this->counter + strlen($this->value) - 1, $this->rdel_length) == $this->compiler->tpl_obj->right_delimiter) {
     preg_match("/\s+/",$this->value,$match);
     $this->value = $match[0];
     $this->token = $parser_class::TP_SPACE;
  } else {
     $this->token = $parser_class::TP_ATTR;
  }
    }
    function yy_r3_61($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ID;
    }
    function yy_r3_62($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_INTEGER;
    }
    function yy_r3_63($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_SPACE;
    }
    function yy_r3_64($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
     $this->yypushstate(self::TEXT);
  } else {
     $this->token = $parser_class::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    function yy_r3_66($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
    $this->yypushstate(self::TEXT);
  } else {
     $this->token = $parser_class::TP_LDELIF;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r3_69($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
     $this->token = $parser_class::TP_LDELFOR;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r3_71($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
    $this->yypushstate(self::TEXT);
  } else {
     $this->token = $parser_class::TP_LDELFOREACH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r3_73($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
    $this->token = $parser_class::TP_TEXT;
  } else {
    $this->token = $parser_class::TP_COMMENT;
    $this->taglineno = $this->line;
  }
    }
    function yy_r3_77($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
    $this->token = $parser_class::TP_TEXT;
    $this->yypushstate(self::TEXT);
  } else {
     $this->token = $parser_class::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line;
  }
    }
    function yy_r3_79($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_NAMESPACE;
    }
    function yy_r3_81($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_TEXT;
    }



    public function yylex4()
    {
        $tokenMap = array (
              1 => 2,
              4 => 2,
              7 => 0,
              8 => 0,
              9 => 0,
              10 => 0,
              11 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*literal\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\/literal\\s*(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G(<\\?(?:php\\w+|=|[a-zA-Z]+)?)|\G(\\?>)|\G(<%)|\G(%>)|\G([\S\s])/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter,2000000000,'latin1'), $yymatches) : preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state LITERAL');
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
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const LITERAL = 4;
    function yy_r4_1($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_LITERALSTART;
  $this->yypushstate(self::LITERAL);
    }
    function yy_r4_4($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_LITERALEND;
  $this->yypopstate();
    }
    function yy_r4_7($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  if (in_array($this->value, Array('<?', '<?=', '<?php'))) {
    $this->token = $parser_class::TP_PHPSTARTTAG;
   } else {
    $this->token = $parser_class::TP_FAKEPHPSTARTTAG;
    $this->value = substr($this->value, 0, 2);
   }
    }
    function yy_r4_8($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_PHPENDTAG;
    }
    function yy_r4_9($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ASPSTARTTAG;
    }
    function yy_r4_10($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_ASPENDTAG;
    }
    function yy_r4_11($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  if ($this->mbstring_overload) {
    $to = mb_strlen($this->data,'latin1');
  } else {
  $to = strlen($this->data);
  }
  preg_match("/{$this->ldel}[-]*\/?literal\s*[-]*{$this->rdel}|<\?|<%|\?>|%>/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
  if (isset($match[0][1])) {
    $to = $match[0][1];
  } else {
    $this->compiler->error ("missing or misspelled literal closing tag");
  }  
  if ($this->mbstring_overload) {
    $this->value = mb_substr($this->data,$this->counter,$to-$this->counter,'latin1');
  } else {
    $this->value = substr($this->data,$this->counter,$to-$this->counter);
  }
  $this->token = $parser_class::TP_LITERAL;
    }


    public function yylex5()
    {
        $tokenMap = array (
              1 => 1,
              3 => 2,
              6 => 1,
              8 => 1,
              10 => 1,
              12 => 0,
              13 => 0,
              14 => 0,
              15 => 0,
              16 => 3,
              20 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*(if|elseif|else if|while)\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*for\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*foreach(?![^\s]))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*)|\G(\")|\G(`\\$)|\G(\\$[0-9]*[a-zA-Z_]\\w*)|\G(\\$)|\G(([^\"\\\\]*?)((?:\\\\.[^\"\\\\]*?)*?)(?=(".$this->ldel."|\\$|`\\$|\")))|\G([\S\s])/iS";

        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr($this->data, $this->counter,2000000000,'latin1'), $yymatches) : preg_match($yy_global_pattern,$this->data, $yymatches, null, $this->counter)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        ' an empty string.  Input "' . substr($this->data,
                        $this->counter, 5) . '... state DOUBLEQUOTEDSTRING');
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
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->counter += ($this->mbstring_overload ? mb_strlen($this->value,'latin1'): strlen($this->value));
                    $this->line += substr_count($this->value, "\n");
                    if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Exception('Unexpected input at line' . $this->line .
                    ': ' . $this->data[$this->counter]);
            }
            break;
        } while (true);

    } // end function


    const DOUBLEQUOTEDSTRING = 5;
    function yy_r5_1($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
    if($this->dqtag) {
       $this->dqtag = false;
       $this->token = $parser_class::TP_LDELSLASH;
        $this->yypushstate(self::SMARTY);
    } else {
       $this->dqtag = true;
       $this->token = $parser_class::TP_DQTAG;
       $this->value = '';
    }
  }
    }
    function yy_r5_3($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
    if($this->dqtag) {
       $this->dqtag = false;
       $this->token = $parser_class::TP_LDELIF;
        $this->yypushstate(self::SMARTY);
    } else {
       $this->dqtag = true;
       $this->token = $parser_class::TP_DQTAG;
       $this->value = '';
    }
  }
    }
    function yy_r5_6($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
    if($this->dqtag) {
       $this->dqtag = false;
       $this->token = $parser_class::TP_LDELFOR;
        $this->yypushstate(self::SMARTY);
    } else {
       $this->dqtag = true;
       $this->token = $parser_class::TP_DQTAG;
       $this->value = '';
    }
  }
    }
    function yy_r5_8($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
     $this->token = $parser_class::TP_TEXT;
  } else {
    if($this->dqtag) {
       $this->dqtag = false;
       $this->token = $parser_class::TP_LDELFOREACH;
        $this->yypushstate(self::SMARTY);
    } else {
       $this->dqtag = true;
       $this->token = $parser_class::TP_DQTAG;
       $this->value = '';
    }
  }
    }
    function yy_r5_10($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  if ($this->autoLiteral($this->value)) {
    $this->token = $parser_class::TP_TEXT;
  } else {
    if($this->dqtag) {
       $this->dqtag = false;
       $this->token = $parser_class::TP_LDEL;
        $this->yypushstate(self::SMARTY);
    } else {
       $this->dqtag = true;
       $this->token = $parser_class::TP_DQTAG;
       $this->value = '';
    }
  }
    }
    function yy_r5_12($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_QUOTE;
  $this->yypopstate();
    }
    function yy_r5_13($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_BACKTICK;
  $this->value = substr($this->value,0,-1);
  $this->yypushstate(self::SMARTY);
  $this->taglineno = $this->line + $this->line_offset;
    }
    function yy_r5_14($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_DOLLARID;
    }
    function yy_r5_15($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_TEXT;
    }
    function yy_r5_16($yy_subpatterns)
    {

  $parser_class = $this->parser_class;
  $this->token = $parser_class::TP_TEXT;
    }
    function yy_r5_20($yy_subpatterns)
    {

     $parser_class = $this->parser_class;
  if ($this->mbstring_overload) {
    $to = mb_strlen($this->data,'latin1');
  } else {
  $to = strlen($this->data);
  }
  if ($this->mbstring_overload) {
    $this->value = mb_substr($this->data,$this->counter,$to-$this->counter,'latin1');
  } else {
  $this->value = substr($this->data,$this->counter,$to-$this->counter);
  }
  $this->token = $parser_class::TP_TEXT;
    }

}
