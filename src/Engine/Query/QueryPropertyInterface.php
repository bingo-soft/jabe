<?php

namespace Jabe\Engine\Query;

interface QueryPropertyInterface extends \Serializable
{
    public function getName(): string;
    public function getFunction(): string;
}
