<?php

namespace BpmPlatform\Engine\History;

interface IncidentStateInterface
{
    public static function default(): IncidentStateInterface;

    public static function resolved(): IncidentStateInterface;

    public static function deleted(): IncidentStateInterface;
}
