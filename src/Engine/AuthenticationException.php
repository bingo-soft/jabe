<?php

namespace Jabe\Engine;

class AuthenticationException extends \Exception
{
    /**
     * @param mixed $userId
     */
    public function __construct($userId)
    {
        parent::__construct(sprintf("The user with id '%s' tries to login without success", $userId));
    }
}
