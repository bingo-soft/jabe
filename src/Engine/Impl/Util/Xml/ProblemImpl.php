<?php

namespace Jabe\Engine\Impl\Util\Xml;

use Jabe\Engine\ProblemInterface;

class ProblemImpl implements ProblemInterface
{
    protected $message;
    protected $line;
    protected $column;
    protected $mainElementId;
    protected $elementIds = [];

    public function __construct($e, Element $element = null, $elementIds = null)
    {
        if ($error instanceof \Exception) {
            $this->concatenateErrorMessages($e);
        } elseif (is_string($e)) {
            $this->message = $errorMessage;
        }
        if ($element != null) {
            $this->extractElementDetails($element);
        }
        if (is_string($elementIds)) {
            $this->mainElementId = $elementIds;
        } elseif (is_array($elementIds) && !empty($elementIds)) {
            $this->mainElementId = $elementIds[0];
            foreach ($elementIds as $elementId) {
                if (!empty($elementId)) {
                    if (!in_array($elementId, $this->elementIds)) {
                        $this->elementIds[] = $elementId;
                    }
                }
            }
        }
    }

    protected function concatenateErrorMessages(\Throwable $throwable = null): void
    {
        while ($throwable != null) {
            if ($this->message == null) {
                $this->message = $throwable->getMessage();
            } else {
                $message .= ": " . $throwable->getMessage();
            }
            if (method_exists($throwable, 'getCause')) {
                $throwable = $throwable->getCause();
            }
        }
    }

    protected function extractElementDetails(Element $element = null): void
    {
        if ($element != null) {
            $this->line = $element->getLine();
            $this->column = $element->getColumn();
            $id = $element->attribute("id");
            if (!empty($id)) {
                $this->mainElementId = $id;
                $this->elementIds[] = $id;
            }
        }
    }

    // getters

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function getMainElementId(): string
    {
        return $this->mainElementId;
    }

    public function getElementIds(): array
    {
        return $this->elementIds;
    }

    public function __toString()
    {
        $string = "";
        if ($this->line > 0) {
            $string .= " | line " . $this->line;
        }
        if ($this->column > 0) {
            $string .= " | column " . $this->column;
        }

        return $string;
    }
}
