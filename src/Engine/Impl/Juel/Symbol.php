<?php

namespace BpmPlatform\Engine\Impl\Juel;

class Symbol
{
    public const EOF = '\0';
    public const PLUS = "'+'";
    public const MINUS = "'-'";
    public const MUL = "'*'";
    public const DIV = "'/'|'div'";
    public const MOD = "'%'|'mod'";
    public const LPAREN = "'('";
    public const RPAREN = "')'";
    public const IDENTIFIER = 'identifier';
    public const NOT = "'!'|'not'";
    public const AND = "'&&'|'and'";
    public const OR = "'||'|'or'";
    public const EMPTY = "'empty'";
    public const INSTANCEOF = "'instanceof'";
    public const INTEGER = 'integer';
    public const FLOAT = 'float';
    public const TRUE = "'true'";
    public const FALSE = "'false'";
    public const STRING = 'string';
    public const NULL = "'null'";
    public const LE = "'<='|'le'";
    public const LT = "'<'|'lt'";
    public const GE = "'>='|'ge'";
    public const GT = "'>'|'gt'";
    public const EQ = "'=='|'eq'";
    public const NE = "'!='|'ne'";
    public const QUESTION = "'?'";
    public const COLON = "':'";
    public const TEXT = 'text';
    public const DOT = "'.'";
    public const LBRACK = "'['";
    public const RBRACK = "']'";
    public const COMMA = "','";
    public const START_EVAL_DEFERRED = "'#{'";
    public const START_EVAL_DYNAMIC = '\'${\'';
    public const END_EVAL = "'}'";
    public const EXTENSION = 'extension'; // used in syntax extensions
    private $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }


    public function __toString()
    {
        return $this->string;
    }
}
