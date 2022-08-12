<?php

namespace Jabe\Impl\Tree;

interface TreeVisitorInterface
{
    /**
     * Invoked for a node in tree.
     *
     * @param obj a reference to the node
     */
    public function visit($obj): void;
}
