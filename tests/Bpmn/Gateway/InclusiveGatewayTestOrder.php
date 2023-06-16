<?php

namespace Tests\Bpmn\Gateway;

class InclusiveGatewayTestOrder
{
    private int $price;

    public function __serialize(): array
    {
        return [
            'price' => $this->price
        ];
    }

    public function __toString()
    {
        return "price=" . $this->price;
    }

    public function __unserialize(array $data): void
    {
        $this->price = $data['price'];
    }

    public function __construct(int $price)
    {
        $this->price = $price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function isBasic(): bool
    {
        return $this->price <= 100;
    }

    public function isStandard(): bool
    {
        return $this->price > 100 && $this->price < 250;
    }

    public function isGold(): bool
    {
        return $this->price >= 250;
    }
}
