<?php

namespace BpmPlatform\Engine\Impl\Juel;

class Token
{
    private $symbol;
    private $image;
    private $length;

    public function __construct(string $symbol, ?string $image = null, ?int $length = null)
    {
        $this->symbol = $symbol;
        $this->image = $image;
        $this->length = $length ?? strlen($image);
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getImage(): ?string
    {
        return $this->image;
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
