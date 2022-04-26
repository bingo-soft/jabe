<?php

namespace Jabe\Engine\Management;

interface SchemaLogEntryInterface
{
    /**
     * A unique identifier of a schema log entry.
     */
    public function getId(): string;

    /**
     * The creation date of this schema log entry.
     */
    public function getTimestamp(): string;

    /**
     * The schema version.
     */
    public function getVersion(): string;
}
