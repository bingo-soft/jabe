<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELContext,
    ELException
};

class AstUnary extends AstRightValue
{
    public static $EMPTY;
    public static $NEG;
    public static $NOT;

    private $operator;
    private $child;

    public static function empty(): UnaryOperator
    {
        if (self::$EMPTY == null) {
            self::$EMPTY = new class extends SimpleUnaryOperator
            {
                public function apply(TypeConverter $converter, $o)
                {
                    return BooleanOperations::empty($converter, $o);
                }
                public function __toString()
                {
                    return "empty";
                }
            };
        }
        return self::$EMPTY;
    }

    public static function neg(): UnaryOperator
    {
        if (self::$NEG == null) {
            self::$NEG = new class extends SimpleUnaryOperator
            {
                public function apply(TypeConverter $converter, $o)
                {
                    return NumberOperations::neg($converter, $o);
                }
                public function __toString()
                {
                    return "-";
                }
            };
        }
        return self::$NEG;
    }

    public static function not(): UnaryOperator
    {
        if (self::$NOT == null) {
            self::$NOT = new class extends SimpleUnaryOperator
            {
                public function apply(TypeConverter $converter, $o)
                {
                    return !$converter->convert($o, "boolean");
                }
                public function __toString()
                {
                    return "!";
                }
            };
        }
        return self::$NOT;
    }

    public function __construct(AstNode $child, UnaryOperator $operator)
    {
        $this->child = $child;
        $this->operator = $operator;
    }

    public function getOperator(): UnaryOperator
    {
        return $this->operator;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->operator->eval($bindings, $context, $child);
    }

    public function __toString()
    {
        return "'" . $this->operator . "'";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= $this->operator;
        $b .= ' ';
        $this->child->appendStructure($b, $bindings);
    }

    public function getCardinality(): int
    {
        return 1;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 0 ? $this->child : null;
    }
}
