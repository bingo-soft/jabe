<?php

namespace Jabe\Engine\Impl\Db;

interface DbEntityInterface
{
    public function getId(): ?string;
    public function setId(string $id): void;

    /**
     * Returns a representation of the object,
     *  as would be stored in the database.
     * Used when deciding if updates have
     *  occurred to the object or not since
     *  it was last loaded.
     */
    public function getPersistentState();
}
