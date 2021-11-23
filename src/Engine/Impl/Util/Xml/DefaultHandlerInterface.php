<?php

namespace BpmPlatform\Engine\Impl\Util\Xml;

interface DefaultHandlerInterface
{
    public function startElement(string $name, array $attributes): void;

    public function endElement(string $name): void;
}
