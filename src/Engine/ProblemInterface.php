<?php

namespace Jabe\Engine;

interface ProblemInterface
{
    /** The message of this problem */
    public function getMessage(): string;

    /** The line where the problem occurs */
    public function getLine(): int;

    /** The column where the problem occurs */
    public function getColumn(): int;

    /**
     * The id of the main element causing the problem. It can be
    * <code>null</code> in case the element doesn't have an id.
    */
    public function getMainElementId(): string;

    /**
     * The ids of all involved elements in the problem. It can be an empty
    * list in case the elements do not have assigned ids.
    */
    public function getElementIds(): array;
}
