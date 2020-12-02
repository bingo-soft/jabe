<?php

namespace BpmPlatform\Engine\Query;

interface QueryPropertyInterface extends \Serializable
{
    public function getName(): string;
    public function getFunction(): string;
}
