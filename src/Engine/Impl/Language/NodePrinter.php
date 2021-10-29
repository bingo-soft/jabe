<?php

namespace BpmPlatform\Engine\Impl\Language;

class NodePrinter
{
    private static function isLastSibling(Node $node, ?Node $parent = null): bool
    {
        if ($parent != null) {
            return $node == $parent->getChild($parent->getCardinality() - 1);
        }
        return true;
    }

    private static function dump(Node $node, ?array $predecessors = []): string
    {
        $result = "";
        $i = count($predecessors) - 1;
        if (!empty($predecessors)) {
            $parent = null;
            for ($i; $i >= 0; $i -= 1) {
                $predecessor = $predecessors[$i];
                if ($this->isLastSibling($predecessor, $parent)) {
                    $result .= "   ";
                } else {
                    $result .=  "|  ";
                }
                $parent = $predecessor;
            }
            $result .=  "|";
        }
        $parent = null;
        for ($i; $i >= 0; $i -= 1) {
            $predecessor = $predecessors[$i];
            if ($this->isLastSibling($predecessor, $parent)) {
                $result .= "   ";
            } else {
                $result .= "|  ";
            }
            $parent = $predecessor;
        }
        $result .= "+- ";
        $result .= $node->__toString();

        $predecessors[] = $node;
        for ($i = 0; $i < $node->getCardinality(); $i++) {
            $this->dump($node->getChild($i), $predecessors);
        }
        unset($predecessors[count($predecessors) - 1]);
    }
}
