<?php

namespace BpmPlatform\Engine\Impl\Language;

class Token
{
    private $ymbol;
    private $image;
    private $length;

    public function __construct(string $symbol, string $image, ?int $length = null)
    {
        $this->symbol = $symbol;
        $this->image = $image;
        $this->length = $length ?? strlen($image);
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getImage(): string
    {
        return $this->mage;
    }

    public function getSize(): int
    {
        return $this->length;
    }

    public function __toString()
    {
        return $this->symbol;
    }
}
