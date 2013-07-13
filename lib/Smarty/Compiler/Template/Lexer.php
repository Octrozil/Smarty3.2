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
    public $ldel;
    public $rdel;
    public $rdel_length;
    public $ldel_length;
    public $mbstring_overload;
    private $heredoc_id_stack = Array();
    public $smarty_token_names = array (		// Text for parser error messages
                    'IDENTITY'	=> '===',
                    'NONEIDENTITY'	=> '!==',
                    'EQUALS'	=> '==',
                    'NOTEQUALS'	=> '!=',
                    'GREATEREQUAL' => '(>=,ge)',
                    'LESSEQUAL' => '(<=,le)',
                    'GREATERTHAN' => '(>,gt)',
                    'LESSTHAN' => '(<,lt)',
                    'MOD' => '(%,mod)',
                    'NOT'			=> '(!,not)',
                    'LAND'		=> '(&&,and)',
                    'LOR'			=> '(||,or)',
                    'LXOR'			=> 'xor',
                    'OPENP'		=> '(',
                    'CLOSEP'	=> ')',
                    'OPENB'		=> '[',
                    'CLOSEB'	=> ']',
                    'PTR'			=> '->',
                    'APTR'		=> '=>',
                    'EQUAL'		=> '=',
                    'NUMBER'	=> 'number',
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

    public function __construct($data,$compiler)
    {
//        $this->data = preg_replace("/(\r\n|\r|\n)/", "\n", $data);

        if ($data !==null) {
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

    private $_yy_state = 1;
    private $_yy_stack = array();

    public function yylex()
    {
        return $this->{'yylex' . $this->_yy_state}();
    }

    public function yypushstate($state)
    {
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
    }

    public function yypopstate()
    {
        $this->_yy_state = array_pop($this->_yy_stack);
    }

    public function yybegin($state)
    {
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
    public function yy_r1_1($yy_subpatterns)
    {

     $this->token = Smarty_Compiler_Template_Parser::TP_TEMPLATEINIT;
     $this->yypushstate(self::TEXT);
    }
    public function yy_r1_2($yy_subpatterns)
    {

     $this->value = '';
     $this->token = Smarty_Compiler_Template_Parser::TP_TEMPLATEINIT;
     $this->yypushstate(self::TEXT);
    }

    public function yylex2()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 1,
              9 => 2,
              12 => 1,
              14 => 1,
              16 => 1,
              18 => 1,
              20 => 1,
              22 => 1,
              24 => 0,
              25 => 0,
              26 => 1,
              28 => 0,
              29 => 0,
              30 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(\\{\\})|\G(".$this->ldel."strip".$this->rdel.")|\G(".$this->ldel."\\s{1,}strip\\s{1,}".$this->rdel.")|\G(".$this->ldel."\/strip".$this->rdel.")|\G(".$this->ldel."\\s{1,}\/strip\\s{1,}".$this->rdel.")|\G(".$this->ldel."\\s*literal\\s*".$this->rdel.")|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s{1,}\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*(if|elseif|else if|while)\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*for\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*foreach(?![^\s]))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*setfilter\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s{1,})|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel."))|\G(<\\?(?:php\\w+|=|[a-zA-Z]+)?)|\G(\\?>)|\G((".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G(<%)|\G(%>)|\G([\S\s])/iS";

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
    public function yy_r2_1($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
    }
    public function yy_r2_2($yy_subpatterns)
    {

    $this->token = Smarty_Compiler_Template_Parser::TP_STRIPON;
    }
    public function yy_r2_3($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
    $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
    $this->token = Smarty_Compiler_Template_Parser::TP_STRIPON;
  }
    }
    public function yy_r2_4($yy_subpatterns)
    {

    $this->token = Smarty_Compiler_Template_Parser::TP_STRIPOFF;
    }
    public function yy_r2_5($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
    $this->token = Smarty_Compiler_Template_Parser::TP_STRIPOFF;
  }
    }
    public function yy_r2_6($yy_subpatterns)
    {

   $this->token = Smarty_Compiler_Template_Parser::TP_LITERALSTART;
   $this->yypushstate(self::LITERAL);
    }
    public function yy_r2_7($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r2_9($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELIF;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r2_12($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELFOR;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r2_14($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELFOREACH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r2_16($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELSETFILTER;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r2_18($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r2_20($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
    }
    public function yy_r2_22($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
    }
    public function yy_r2_24($yy_subpatterns)
    {

  if (in_array($this->value, Array('<?', '<?=', '<?php'))) {
    $this->token = Smarty_Compiler_Template_Parser::TP_PHPSTARTTAG;
  } elseif ($this->value == '<?xml') {
      $this->token = Smarty_Compiler_Template_Parser::TP_XMLTAG;
  } else {
    $this->token = Smarty_Compiler_Template_Parser::TP_FAKEPHPSTARTTAG;
    $this->value = substr($this->value, 0, 2);
  }
     }
    public function yy_r2_25($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_PHPENDTAG;
    }
    public function yy_r2_26($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
    }
    public function yy_r2_28($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ASPSTARTTAG;
    }
    public function yy_r2_29($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ASPENDTAG;
    }
    public function yy_r2_30($yy_subpatterns)
    {

  if ($this->mbstring_overload) {
    $to = mb_strlen($this->data,'latin1');
  } else {
  $to = strlen($this->data);
  }
  preg_match("/\s*{$this->ldel}--|[^\S\r\n]*{$this->ldel}-|{$this->ldel}|<\?|\?>|<%|%>/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
  if (isset($match[0][1])) {
    $to = $match[0][1];
  }
  if ($this->mbstring_overload) {
    $this->value = mb_substr($this->data,$this->counter,$to-$this->counter,'latin1');
  } else {
  $this->value = substr($this->data,$this->counter,$to-$this->counter);
  }
  $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
    }

    public function yylex3()
    {
        $tokenMap = array (
              1 => 0,
              2 => 1,
              4 => 2,
              7 => 1,
              9 => 1,
              11 => 1,
              13 => 1,
              15 => 1,
              17 => 1,
              19 => 1,
              21 => 2,
              24 => 0,
              25 => 0,
              26 => 0,
              27 => 0,
              28 => 0,
              29 => 0,
              30 => 0,
              31 => 0,
              32 => 1,
              34 => 1,
              36 => 1,
              38 => 0,
              39 => 0,
              40 => 0,
              41 => 0,
              42 => 0,
              43 => 0,
              44 => 0,
              45 => 0,
              46 => 0,
              47 => 0,
              48 => 0,
              49 => 0,
              50 => 0,
              51 => 0,
              52 => 0,
              53 => 0,
              54 => 0,
              55 => 3,
              59 => 0,
              60 => 0,
              61 => 0,
              62 => 0,
              63 => 0,
              64 => 0,
              65 => 0,
              66 => 1,
              68 => 1,
              70 => 1,
              72 => 0,
              73 => 0,
              74 => 0,
              75 => 0,
              76 => 0,
              77 => 0,
              78 => 0,
              79 => 0,
              80 => 0,
              81 => 0,
              82 => 0,
              83 => 0,
              84 => 0,
              85 => 0,
              86 => 0,
              87 => 0,
              88 => 0,
              89 => 0,
              90 => 1,
              92 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s{1,}\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*(if|elseif|else if|while)\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*for\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*foreach(?![^\s]))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s{1,})|\G(\\s{1,}(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel."))|\G((".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*))|\G(\\*([\S\s]*?)\\*(?=(".$this->rdel."|--".$this->rdel."\\s*|-".$this->rdel."[^\S\r\n]*)))|\G(\\s+is\\s+in\\s+)|\G(\\s+as\\s+)|\G(\\s+to\\s+)|\G(\\s+step\\s+)|\G(\\s+instanceof\\s+)|\G(\\s*===\\s*)|\G(\\s*!==\\s*)|\G(\\s*==\\s*|\\s+eq\\s+)|\G(\\s*!=\\s*|\\s*<>\\s*|\\s+(ne|neq)\\s+)|\G(\\s*>=\\s*|\\s+(ge|gte)\\s+)|\G(\\s*<=\\s*|\\s+(le|lte)\\s+)|\G(\\s*>\\s*|\\s+gt\\s+)|\G(\\s*<\\s*|\\s+lt\\s+)|\G(\\s+mod\\s+)|\G(!\\s*|not\\s+)|\G(\\s*&&\\s*|\\s*and\\s+)|\G(\\s*\\|\\|\\s*|\\s*or\\s+)|\G(\\s*xor\\s+)|\G(\\s+is\\s+odd\\s+by\\s+)|\G(\\s+is\\s+not\\s+odd\\s+by\\s+)|\G(\\s+is\\s+odd)|\G(\\s+is\\s+not\\s+odd)|\G(\\s+is\\s+even\\s+by\\s+)|\G(\\s+is\\s+not\\s+even\\s+by\\s+)|\G(\\s+is\\s+even)|\G(\\s+is\\s+not\\s+even)|\G(\\s+is\\s+div\\s+by\\s+)|\G(\\s+is\\s+not\\s+div\\s+by\\s+)|\G(\\((int(eger)?|bool(ean)?|float|double|real|string|binary|array|object)\\)\\s*)|\G(\\s*\\(\\s*)|\G(\\s*\\))|\G(\\[\\s*)|\G(\\s*\\])|\G(\\s*->\\s*)|\G(\\s*=>\\s*)|\G(\\s*=\\s*)|\G(\\s*(\\+|-)\\s*)|\G(\\s*(\\*|\/|%)\\s*)|\G(\\$[0-9]*[a-zA-Z_]\\w*(\\+\\+|--))|\G(\\$)|\G(\\s*;)|\G(::)|\G(\\s*:\\s*)|\G(@)|\G(#)|\G(\")|\G(`)|\G(\\|)|\G(\\.)|\G(\\s*,\\s*)|\G(\\s*&\\s*)|\G(\\s*\\?\\s*)|\G(0[xX][0-9a-fA-F]+)|\G(\\s+[0-9]*[a-zA-Z_][a-zA-Z0-9_\-:]*\\s*=\\s*)|\G([0-9]*[a-zA-Z_]\\w*)|\G(\\d+)|\G(\\s+)|\G((\\\\[0-9]*[a-zA-Z_]\\w*)+)|\G([\S\s])/iS";

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
    public function yy_r3_1($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_SINGLEQUOTESTRING;
    }
    public function yy_r3_2($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r3_4($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELIF;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r3_7($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELFOR;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r3_9($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELFOREACH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r3_11($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r3_13($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_RDEL;
  $this->yypopstate();
    }
    public function yy_r3_15($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
    }
    public function yy_r3_17($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
    }
    public function yy_r3_19($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_RDEL;
     $this->yypopstate();
    }
    public function yy_r3_21($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_COMMENT;
    }
    public function yy_r3_24($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISIN;
    }
    public function yy_r3_25($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_AS;
    }
    public function yy_r3_26($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_TO;
    }
    public function yy_r3_27($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_STEP;
    }
    public function yy_r3_28($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_INSTANCEOF;
    }
    public function yy_r3_29($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_IDENTITY;
    }
    public function yy_r3_30($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_NONEIDENTITY;
    }
    public function yy_r3_31($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_EQUALS;
    }
    public function yy_r3_32($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_NOTEQUALS;
    }
    public function yy_r3_34($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_GREATEREQUAL;
    }
    public function yy_r3_36($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LESSEQUAL;
    }
    public function yy_r3_38($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_GREATERTHAN;
    }
    public function yy_r3_39($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LESSTHAN;
    }
    public function yy_r3_40($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_MOD;
    }
    public function yy_r3_41($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_NOT;
    }
    public function yy_r3_42($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LAND;
    }
    public function yy_r3_43($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LOR;
    }
    public function yy_r3_44($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LXOR;
    }
    public function yy_r3_45($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISODDBY;
    }
    public function yy_r3_46($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISNOTODDBY;
    }
    public function yy_r3_47($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISODD;
    }
    public function yy_r3_48($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISNOTODD;
    }
    public function yy_r3_49($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISEVENBY;
    }
    public function yy_r3_50($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISNOTEVENBY;
    }
    public function yy_r3_51($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISEVEN;
    }
    public function yy_r3_52($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISNOTEVEN;
    }
    public function yy_r3_53($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISDIVBY;
    }
    public function yy_r3_54($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ISNOTDIVBY;
    }
    public function yy_r3_55($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_TYPECAST;
    }
    public function yy_r3_59($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_OPENP;
    }
    public function yy_r3_60($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_CLOSEP;
    }
    public function yy_r3_61($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_OPENB;
    }
    public function yy_r3_62($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_CLOSEB;
    }
    public function yy_r3_63($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_PTR;
    }
    public function yy_r3_64($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_APTR;
    }
    public function yy_r3_65($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_EQUAL;
    }
    public function yy_r3_66($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_UNIMATH;
    }
    public function yy_r3_68($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_MATH;
    }
    public function yy_r3_70($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_IDINCDEC;
    }
    public function yy_r3_72($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_DOLLAR;
    }
    public function yy_r3_73($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_SEMICOLON;
    }
    public function yy_r3_74($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_DOUBLECOLON;
    }
    public function yy_r3_75($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_COLON;
    }
    public function yy_r3_76($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_AT;
    }
    public function yy_r3_77($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_HATCH;
    }
    public function yy_r3_78($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_QUOTE;
  $this->yypushstate(self::DOUBLEQUOTEDSTRING);
    }
    public function yy_r3_79($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_BACKTICK;
  $this->yypopstate();
    }
    public function yy_r3_80($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_VERT;
    }
    public function yy_r3_81($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_DOT;
    }
    public function yy_r3_82($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_COMMA;
    }
    public function yy_r3_83($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ANDSYM;
    }
    public function yy_r3_84($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_QMARK;
    }
    public function yy_r3_85($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_HEX;
    }
    public function yy_r3_86($yy_subpatterns)
    {

  // resolve conflicts with shorttag and right_delimiter starting with '='
  if (substr($this->data, $this->counter + strlen($this->value) - 1, $this->rdel_length) == $this->compiler->tpl_obj->right_delimiter) {
     preg_match("/\s+/",$this->value,$match);
     $this->value = $match[0];
     $this->token = Smarty_Compiler_Template_Parser::TP_SPACE;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_ATTR;
  }
    }
    public function yy_r3_87($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ID;
    }
    public function yy_r3_88($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_INTEGER;
    }
    public function yy_r3_89($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_SPACE;
    }
    public function yy_r3_90($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_NAMESPACE;
    }
    public function yy_r3_92($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
    }

    public function yylex4()
    {
        $tokenMap = array (
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G(".$this->ldel."\\s*literal\\s*".$this->rdel.")|\G(".$this->ldel."\\s*\/literal\\s*".$this->rdel.")|\G(<\\?(?:php\\w+|=|[a-zA-Z]+)?)|\G(\\?>)|\G(<%)|\G(%>)|\G([\S\s])/iS";

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
    public function yy_r4_1($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LITERALSTART;
  $this->yypushstate(self::LITERAL);
    }
    public function yy_r4_2($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LITERALEND;
  $this->yypopstate();
    }
    public function yy_r4_3($yy_subpatterns)
    {

  if (in_array($this->value, Array('<?', '<?=', '<?php'))) {
    $this->token = Smarty_Compiler_Template_Parser::TP_PHPSTARTTAG;
   } else {
    $this->token = Smarty_Compiler_Template_Parser::TP_FAKEPHPSTARTTAG;
    $this->value = substr($this->value, 0, 2);
   }
    }
    public function yy_r4_4($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_PHPENDTAG;
    }
    public function yy_r4_5($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ASPSTARTTAG;
    }
    public function yy_r4_6($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_ASPENDTAG;
    }
    public function yy_r4_7($yy_subpatterns)
    {

  if ($this->mbstring_overload) {
    $to = mb_strlen($this->data,'latin1');
  } else {
  $to = strlen($this->data);
  }
  preg_match("/{$this->ldel}\/?literal{$this->rdel}|<\?|<%|\?>|%>/",$this->data,$match,PREG_OFFSET_CAPTURE,$this->counter);
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
  $this->token = Smarty_Compiler_Template_Parser::TP_LITERAL;
    }

    public function yylex5()
    {
        $tokenMap = array (
              1 => 1,
              3 => 2,
              6 => 1,
              8 => 1,
              10 => 1,
              12 => 1,
              14 => 1,
              16 => 0,
              17 => 0,
              18 => 0,
              19 => 0,
              20 => 3,
              24 => 0,
            );
        if ($this->counter >= ($this->mbstring_overload ? mb_strlen($this->data,'latin1'): strlen($this->data))) {
            return false; // end of input
        }
        $yy_global_pattern = "/\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s{1,}\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*(if|elseif|else if|while)\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*for\\s+)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s*foreach(?![^\s]))|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\\s{1,})|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel.")\/)|\G((\\s*".$this->ldel."--|[^\S\r\n]*".$this->ldel."-|".$this->ldel."))|\G(\")|\G(`\\$)|\G(\\$[0-9]*[a-zA-Z_]\\w*)|\G(\\$)|\G(([^\"\\\\]*?)((?:\\\\.[^\"\\\\]*?)*?)(?=(".$this->ldel."|\\$|`\\$|\")))|\G([\S\s])/iS";

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
    public function yy_r5_1($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r5_3($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELIF;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r5_6($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELFOR;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r5_8($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal && trim(substr($this->value,$this->ldel_length,1)) == '') {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDELFOREACH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r5_10($yy_subpatterns)
    {

  if ($this->compiler->tpl_obj->auto_literal) {
     $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
  } else {
     $this->token = Smarty_Compiler_Template_Parser::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
  }
    }
    public function yy_r5_12($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LDELSLASH;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
    }
    public function yy_r5_14($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_LDEL;
     $this->yypushstate(self::SMARTY);
     $this->taglineno = $this->line + $this->line_offset;
    }
    public function yy_r5_16($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_QUOTE;
  $this->yypopstate();
    }
    public function yy_r5_17($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_BACKTICK;
  $this->value = substr($this->value,0,-1);
  $this->yypushstate(self::SMARTY);
  $this->taglineno = $this->line + $this->line_offset;
    }
    public function yy_r5_18($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_DOLLARID;
    }
    public function yy_r5_19($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
    }
    public function yy_r5_20($yy_subpatterns)
    {

  $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
    }
    public function yy_r5_24($yy_subpatterns)
    {

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
  $this->token = Smarty_Compiler_Template_Parser::TP_TEXT;
    }

}
