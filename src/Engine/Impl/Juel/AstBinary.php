<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELContext;

class AstBinary extends AstRightValue
{
    public static $ADD;
    public static $AND;
    public static $DIV;
    public static $EQ;
    public static $GE;
    public static $GT;
    public static $LE;
    public static $LT;
    public static $MOD;
    public static $MUL;
    public static $NE;
    public static $OR;
    public static $SUB;

    private $operator;
    private $left;
    private $right;

    public static function add(): BinaryOperator
    {
        if (self::$ADD == null) {
            self::$ADD = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return NumberOperations::add($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "+";
                }
            };
        }
        return self::$ADD;
    }

    public static function and(): BinaryOperator
    {
        if (self::$AND == null) {
            self::$AND = new class implements BinaryOperator
            {
                public function eval(Bindings $bindings, ELContext $context, AstNode $left, AstNode $right)
                {
                    $l = $bindings->convert($left->eval($bindings, $context), "boolean");
                    return $l == true ? $bindings->convert($right->eval($bindings, $context), "boolean") : false;
                }
                public function __toString()
                {
                    return "&&";
                }
            };
        }
        return self::$AND;
    }

    public static function div(): BinaryOperator
    {
        if (self::$DIV == null) {
            self::$DIV = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return NumberOperations::div($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "/";
                }
            };
        }
        return self::$DIV;
    }

    public static function eq(): BinaryOperator
    {
        if (self::$EQ == null) {
            self::$EQ = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return BooleanOperations::eq($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "==";
                }
            };
        }
        return self::$EQ;
    }

    public static function ge(): BinaryOperator
    {
        if (self::$GE == null) {
            self::$GE = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return BooleanOperations::ge($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return ">=";
                }
            };
        }
        return self::$GE;
    }

    public static function gt(): BinaryOperator
    {
        if (self::$GT == null) {
            self::$GT = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return BooleanOperations::gt($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return ">";
                }
            };
        }
        return self::$GT;
    }

    public static function le(): BinaryOperator
    {
        if (self::$LE == null) {
            self::$LE = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return BooleanOperations::le($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "<=";
                }
            };
        }
        return self::$LE;
    }

    public static function lt(): BinaryOperator
    {
        if (self::$LT == null) {
            self::$LT = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return BooleanOperations::lt($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "<";
                }
            };
        }
        return self::$LT;
    }

    public static function mod(): BinaryOperator
    {
        if (self::$MOD == null) {
            self::$MOD = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return NumberOperations::mod($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "%";
                }
            };
        }
        return self::$MOD;
    }

    public static function mul(): BinaryOperator
    {
        if (self::$MUL == null) {
            self::$MUL = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return NumberOperations::mul($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "*";
                }
            };
        }
        return self::$MUL;
    }

    public static function ne(): BinaryOperator
    {
        if (self::$NE == null) {
            self::$NE = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return BooleanOperations::ne($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "!=";
                }
            };
        }
        return self::$NE;
    }

    public static function or(): BinaryOperator
    {
        if (self::$OR == null) {
            self::$OR = new class implements BinaryOperator
            {
                public function eval(Bindings $bindings, ELContext $context, AstNode $left, AstNode $right)
                {
                    $l = $bindings->convert($left->eval($bindings, $context), "boolean");
                    return $l == true ? true : $bindings->convert($right->eval($bindings, $context), "boolean");
                }
                public function __toString()
                {
                    return "||";
                }
            };
        }
        return self::$OR;
    }

    public static function sub(): BinaryOperator
    {
        if (self::$SUB == null) {
            self::$SUB = new class extends SimpleBinaryOperator
            {
                public function apply(TypeConverter $converter, $o1, $o2)
                {
                    return NumberOperations::sub($converter, $o1, $o2);
                }
                public function __toString()
                {
                    return "-";
                }
            };
        }
        return self::$SUB;
    }

    public function __construct(AstNode $left, AstNode $right, BinaryOperator $operator)
    {
        $this->left = $left;
        $this->right = $right;
        $this->operator = $operator;
    }

    public function getOperator(): BinaryOperator
    {
        return $this->operator;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->operator->eval($bindings, $context, $this->left, $this->right);
    }

    public function __toString()
    {
        return "'" . $operator . "'";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $this->left->appendStructure($b, $bindings);
        $b .= ' ';
        $b .= $this->operator;
        $b .= ' ';
        $this->right->appendStructure($b, $bindings);
    }

    public function getCardinality(): int
    {
        return 2;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 0 ? $left : ($i == 1 ? $right : null);
    }
}
