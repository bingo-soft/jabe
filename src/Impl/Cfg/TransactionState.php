<?php

namespace Jabe\Impl\Cfg;

class TransactionState
{
    public const COMMITTED = "commited";
    public const ROLLED_BACK = "rolled_back";
    public const COMMITTING = "committing";
    public const ROLLINGBACK = "rollingback";
}
