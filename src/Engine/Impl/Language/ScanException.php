<?php

namespace BpmPlatform\Engine\Impl\Language;

class ScanException extends \Exception
{
    public $position;
    public $encountered;
    public $expected;

    public function __construct(int $position, string $encountered, string $expected)
    {
        parent::__construct(LocalMessages::get("error.scan", $position, $encountered, $expected));
        $this->position = $position;
        $this->encountered = $encountered;
        $this->expected = $expected;
    }
}
