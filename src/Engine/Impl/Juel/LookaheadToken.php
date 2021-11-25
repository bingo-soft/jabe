<?php

namespace BpmPlatform\Engine\Impl\Juel;

class LookaheadToken
{
    public $token;
    public $position;

    public function __construct(Token $token, int $position)
    {
        $this->token = $token;
        $this->position = $position;
    }
}
