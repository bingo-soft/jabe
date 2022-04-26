<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\ELException;

class TreeBuilderException extends ELException
{
    private $expression;
    private $position;
    private $encountered;
    private $expected;

    public function __construct(string $expression, int $position, string $encountered, string $expected, string $message)
    {
        parent::__construct(LocalMessages::get("error.build", $expression, $message));
        $this->expression = $expression;
        $this->position = $position;
        $this->encountered = $encountered;
        $this->expected = $expected;
    }

    /**
     * @return the expression string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @return the error position
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return the substring (or description) that has been encountered
     */
    public function getEncountered(): string
    {
        return $this->encountered;
    }

    /**
     * @return the substring (or description) that was expected
     */
    public function getExpected(): string
    {
        return $this->expected;
    }
}
