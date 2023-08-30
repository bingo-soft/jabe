<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\Delegate\ExpressionInterface;

class DataAssociation
{
    protected $source;

    protected $sourceExpression;

    protected $target;

    protected $variables;

    protected $businessKeyExpression;

    public function __construct($source, ?string $target, ?string $variables = null, ExpressionInterface $businessKeyExpression = null)
    {
        if (is_string($source)) {
            $this->source = $source;
        } elseif ($source instanceof ExpressionInterface) {
            $this->sourceExpression = $source;
        }
        $this->target = $target;
        $this->variables = $variables;
        $this->businessKeyExpression = $businessKeyExpression;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function getSourceExpression(): ?ExpressionInterface
    {
        return $this->sourceExpression;
    }

    public function getVariables(): ?string
    {
        return $this->variables;
    }

    public function getBusinessKeyExpression(): ?ExpressionInterface
    {
        return $this->businessKeyExpression;
    }
}
