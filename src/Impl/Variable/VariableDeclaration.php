<?php

namespace Jabe\Impl\Variable;

use Jabe\ProcessEngineException;
use Jabe\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};

class VariableDeclaration implements \Serializable
{
    protected $name;
    protected $type;
    protected $sourceVariableName;
    protected $sourceExpression;
    protected $destinationVariableName;
    protected $destinationExpression;
    protected $link;
    protected $linkExpression;

    public function __construct(?string $name, ?string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function initialize(VariableScopeInterface $innerScopeInstance, VariableScopeInterface $outerScopeInstance): void
    {
        if ($this->sourceVariableName !== null) {
            if ($outerScopeInstance->hasVariable($this->sourceVariableName)) {
                $value = $outerScopeInstance->getVariable($this->sourceVariableName);
                $innerScopeInstance->setVariable($this->destinationVariableName, $value);
            } else {
                throw new ProcessEngineException("Couldn't create variable '"
                        . $this->destinationVariableName . "', since the source variable '"
                        . $this->sourceVariableName . "does not exist");
            }
        }

        if ($this->sourceExpression !== null) {
            $value = $this->sourceExpression->getValue($outerScopeInstance);
            $innerScopeInstance->setVariable($this->destinationVariableName, $value);
        }

        if ($this->link !== null) {
            if ($outerScopeInstance->hasVariable($this->sourceVariableName)) {
                $value = $outerScopeInstance->getVariable($this->sourceVariableName);
                $innerScopeInstance->setVariable($this->destinationVariableName, $value);
            } else {
                throw new ProcessEngineException("Couldn't create variable '" . $this->destinationVariableName .
                        "', since the source variable '" . $this->sourceVariableName
                        . "does not exist");
            }
        }

        if ($this->linkExpression !== null) {
            $value = $this->sourceExpression->getValue($outerScopeInstance);
            $innerScopeInstance->setVariable($this->destinationVariableName, $value);
        }
    }

    public function destroy(VariableScopeInterface $innerScopeInstance, VariableScopeInterface $outerScopeInstance): void
    {

        if ($this->destinationVariableName !== null) {
            if ($innerScopeInstance->hasVariable($this->sourceVariableName)) {
                $value = $innerScopeInstance->getVariable($this->sourceVariableName);
                $outerScopeInstance->setVariable($this->destinationVariableName, $value);
            } else {
                throw new ProcessEngineException("Couldn't destroy variable " . $this->sourceVariableName .
                                                 ", since it does not exist");
            }
        }

        if ($this->destinationExpression !== null) {
            $value = $this->destinationExpression->getValue($innerScopeInstance);
            $outerScopeInstance->setVariable($this->destinationVariableName, $value);
        }

        if ($this->link !== null) {
            if ($innerScopeInstance->hasVariable($this->sourceVariableName)) {
                $value = $innerScopeInstance->getVariable($this->sourceVariableName);
                $outerScopeInstance->setVariable($this->destinationVariableName, $value);
            } else {
                throw new ProcessEngineException("Couldn't destroy variable " . $this->sourceVariableName .
                                                 ", since it does not exist");
            }
        }

        if ($this->linkExpression !== null) {
            $value = $this->sourceExpression->getValue($innerScopeInstance);
            $outerScopeInstance->setVariable($this->destinationVariableName, $value);
        }
    }

    public function __toString()
    {
        return "VariableDeclaration[" . $this->name . ":" . $this->type . "]";
    }

    public function serialize()
    {
        return json_encode([
            'name' => $this->name,
            'type' => $this->type
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->type = $json->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getSourceVariableName(): ?string
    {
        return $this->sourceVariableName;
    }

    public function setSourceVariableName(?string $sourceVariableName): void
    {
        $this->sourceVariableName = $sourceVariableName;
    }

    public function getSourceExpression(): ?ExpressionInterface
    {
        return $this->sourceExpression;
    }

    public function setSourceExpression(ExpressionInterface $sourceExpression): void
    {
        $this->sourceExpression = $sourceExpression;
    }

    public function getDestinationVariableName(): ?string
    {
        return $this->destinationVariableName;
    }

    public function setDestinationVariableName(?string $destinationVariableName): void
    {
        $this->destinationVariableName = $destinationVariableName;
    }

    public function getDestinationExpression(): ExpressionInterface
    {
        return $this->destinationExpression;
    }

    public function setDestinationExpression(ExpressionInterface $destinationExpression): void
    {
        $this->destinationExpression = $destinationExpression;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function getLinkExpression(): ?ExpressionInterface
    {
        return $this->linkExpression;
    }

    public function setLinkExpression(ExpressionInterface $linkExpression): void
    {
        $this->linkExpression = $linkExpression;
    }
}
