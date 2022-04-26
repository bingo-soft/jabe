<?php

namespace Jabe\Engine\Impl\Tree;

use Jabe\Engine\Impl\Pvm\Process\ScopeImpl;

class ScopeCollector implements TreeVisitorInterface
{
    protected $scopes = [];

    public function visit(/*ScopeImpl */$obj): void
    {
        if ($obj != null && $obj->isScope()) {
            $this->scopes[] = $obj;
        }
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }
}
