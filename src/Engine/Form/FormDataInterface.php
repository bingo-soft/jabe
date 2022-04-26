<?php

namespace Jabe\Engine\Form;

interface FormDataInterface
{
    /** User-defined reference to a form. In the camunda tasklist application,
     * it is assumed that the form key specifies a resource in the deployment
     * which is the template for the form.  But users are free to
     * use this property differently. */
    public function getFormKey(): string;

    /** The deployment id of the process definition to which this form is related
     *  */
    public function getDeploymentId(): ?string;

    /** returns the form fields which make up this form. */
    public function getFormFields(): array;

    public function addFormField(FormFieldInterface $field): void;
}
