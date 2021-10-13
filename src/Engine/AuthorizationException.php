<?php

namespace BpmPlatform\Engine;

class AuthorizationException extends \Exception
{
    /**
     * @param mixed $userId
     */
    public function __construct($userId)
    {
        parent::__construct(sprintf("The user with id '%s' does not have enough permissions", $userId));
    }
}
