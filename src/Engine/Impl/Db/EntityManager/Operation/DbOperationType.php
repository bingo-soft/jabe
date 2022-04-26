<?php

namespace Jabe\Engine\Impl\Db\EntityManager\Operation;

class DbOperationType
{
    public const INSERT = 'insert';

    public const UPDATE = 'update';
    public const UPDATE_BULK = 'update_bulk';

    public const DELETE = 'delete';
    public const DELETE_BULK = 'delete_bulk';
}
