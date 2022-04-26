<?php

namespace Jabe\Engine\Impl\Juel;

class Profile
{
    private $features;

    public const DEFAULT = [Feature::METHOD_INVOCATIONS, Feature::VARARGS];

    public function __construct(array $features)
    {
        $this->features = $features;
    }

    public function features(): array
    {
        return $this->features;
    }

    public function contains(string $feature): bool
    {
        return in_array($feature, $this->features);
    }
}
