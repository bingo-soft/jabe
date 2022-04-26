<?php

namespace Jabe\Engine\Impl\Db;

interface HasDbRevisionInterface
{
    public function setRevision(int $revision): void;
    public function getRevision(): int;
    public function getRevisionNext(): int;
}
