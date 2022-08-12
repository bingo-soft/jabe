<?php

namespace Jabe\Impl\Identity;

interface AccountInterface
{
    public const NAME_ALFRESCO = "Alfresco";
    public const NAME_GOOGLE = "Google";
    public const NAME_SKYPE = "Skype";
    public const NAME_MAIL = "Mail";

    public function getName(): string;
    public function getUsername(): string;
    public function getPassword(): string;
    public function getDetails(): array;
}
