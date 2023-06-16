<?php

namespace Jabe\Query;

interface QueryPropertyInterface
{
    public function getName(): ?string;
    public function getFunction(): ?string;
}
