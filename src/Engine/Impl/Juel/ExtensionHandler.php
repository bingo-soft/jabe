<?php

namespace Jabe\Engine\Impl\Juel;

abstract class ExtensionHandler
{
    private $point;

    public function __construct(string $point)
    {
        $this->point = $point;
    }

    /**
     * @return string the extension point specifying where this syntax extension is active
     */
    public function getExtensionPoint(): string
    {
        return $this->point;
    }

    /**
     * Called by the parser if it handles a extended token associated with this handler
     * at the appropriate extension point.
     * @param children
     * @return abstract syntax tree node
     */
    abstract public function createAstNode(array $children): AstNode;
}
