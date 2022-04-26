<?php

namespace Jabe\Engine\Form;

interface FormRefInterface
{
    /**
     * The key of a {@linkFormRef} corresponds to the {@code id} attribute
     * in the Forms JSON.
     */
    public function getKey(): string;

    /**
     * The binding of {@link FormRef} specifies which version of the form
     * to reference. Possible values are: {@code latest}, {@code deployment} and
     * {@code version} (specific version value can be retrieved with {@link #getVersion()}).
     */
    public function getBinding(): string;

    /**
     * If the {@link #getBinding() binding} of a {@link FormRef} is set to
     * {@code version}, the specific version is returned.
     */
    public function getVersion(): int;
}
