<?php

namespace Jabe\Impl\Form\Engine;

use Jabe\Form\{
    FormDataInterface,
    FormFieldInterface,
    FormFieldValidationConstraintInterface,
    FormPropertyInterface
};
use Jabe\Impl\Form\Type\{
    BooleanFormType,
    DateFormType,
    EnumFormType,
    StringFormType
};

abstract class AbstractRenderFormDelegate
{
    /* elements */
    protected const FORM_ELEMENT = "form";
    protected const DIV_ELEMENT = "div";
    protected const SPAN_ELEMENT = "span";
    protected const LABEL_ELEMENT = "label";
    protected const INPUT_ELEMENT = "input";
    protected const BUTTON_ELEMENT = "button";
    protected const SELECT_ELEMENT = "select";
    protected const OPTION_ELEMENT = "option";
    protected const I_ELEMENT = "i";
    protected const SCRIPT_ELEMENT = "script";

    /* attributes */
    protected const NAME_ATTRIBUTE = "name";
    protected const CLASS_ATTRIBUTE = "class";
    protected const ROLE_ATTRIBUTE = "role";
    protected const FOR_ATTRIBUTE = "for";
    protected const VALUE_ATTRIBUTE = "value";
    protected const TYPE_ATTRIBUTE = "type";
    protected const SELECTED_ATTRIBUTE = "selected";

    /* datepicker attributes*/
    protected const IS_OPEN_ATTRIBUTE = "is-open";
    protected const DATEPICKER_POPUP_ATTRIBUTE = "datepicker-popup";

    /* camunda attributes */
    //protected const CAM_VARIABLE_TYPE_ATTRIBUTE = "cam-variable-type";
    //protected const CAM_VARIABLE_NAME_ATTRIBUTE = "cam-variable-name";
    //protected const CAM_SCRIPT_ATTRIBUTE = "cam-script";

    /* angular attributes*/
    protected const NG_CLICK_ATTRIBUTE = "ng-click";
    protected const NG_IF_ATTRIBUTE = "ng-if";
    protected const NG_SHOW_ATTRIBUTE = "ng-show";

    /* classes */
    protected const FORM_GROUP_CLASS = "form-group";
    protected const FORM_CONTROL_CLASS = "form-control";
    protected const INPUT_GROUP_CLASS = "input-group";
    protected const INPUT_GROUP_BTN_CLASS = "input-group-btn";
    protected const BUTTON_DEFAULT_CLASS = "btn btn-default";
    protected const HAS_ERROR_CLASS = "has-error";
    protected const HELP_BLOCK_CLASS = "help-block";

    /* input[type] */
    protected const TEXT_INPUT_TYPE = "text";
    protected const CHECKBOX_INPUT_TYPE = "checkbox";

    /* button[type] */
    protected const BUTTON_BUTTON_TYPE = "button";

    /* script[type] */
    protected const TEXT_FORM_SCRIPT_TYPE = "text/form-script";

    /* glyphicons */
    protected const CALENDAR_GLYPHICON = "glyphicon glyphicon-calendar";

    /* generated form name */
    protected const GENERATED_FORM_NAME = "generatedForm";
    protected const FORM_ROLE = "form";

    /* error types */
    protected const REQUIRED_ERROR_TYPE = "required";
    protected const DATE_ERROR_TYPE = "date";

    /* form element selector */
    protected const FORM_ELEMENT_SELECTOR = "this." . self::GENERATED_FORM_NAME . ".%s";

    /* expressions */
    protected const INVALID_EXPRESSION = self::FORM_ELEMENT_SELECTOR . '.$invalid';
    protected const DIRTY_EXPRESSION = self::FORM_ELEMENT_SELECTOR . '.$dirty';
    protected const ERROR_EXPRESSION = self::FORM_ELEMENT_SELECTOR . '.$error';
    protected const DATE_ERROR_EXPRESSION = self::ERROR_EXPRESSION . ".date";
    protected const REQUIRED_ERROR_EXPRESSION = self::ERROR_EXPRESSION . ".required";
    protected const TYPE_ERROR_EXPRESSION = self::ERROR_EXPRESSION . ".camVariableType";

    /* JavaScript snippets */
    protected const DATE_FIELD_OPENED_ATTRIBUTE = "dateFieldOpened%s";
    protected const OPEN_DATEPICKER_SNIPPET = '$scope.open%s = function ($event) { $event.preventDefault(); $event.stopPropagation(); $scope.dateFieldOpened%s = true; };"';
    protected const OPEN_DATEPICKER_FUNCTION_SNIPPET = 'open%s($event)';

    /* date format */
    protected const DATE_FORMAT = "dd/MM/yyyy";

    /* messages */
    protected const REQUIRED_FIELD_MESSAGE = "Required field";
    protected const TYPE_FIELD_MESSAGE = "Only a %s value is allowed";
    protected const INVALID_DATE_FIELD_MESSAGE = "Invalid date format: the date should have the pattern '" . self::DATE_FORMAT . "'";

    protected function renderFormData(FormDataInterface $formData): string
    {
        if (
            $formData === null
            || ($formData->getFormFields() === null || $formData->getFormFields()->isEmpty())
            && ($formData->getFormProperties() === null || $formData->getFormProperties()->isEmpty())
        ) {
            return null;
        } else {
            $formElement = (new HtmlElementWriter(self::FORM_ELEMENT))
                ->attribute(self::NAME_ATTRIBUTE, self::GENERATED_FORM_NAME)
                ->attribute(self::ROLE_ATTRIBUTE, self::FORM_ROLE);

            $documentBuilder = new HtmlDocumentBuilder($formElement);

            // render fields
            foreach ($formData->getFormFields() as $formField) {
                $this->renderFormField($formField, $documentBuilder);
            }

            // render deprecated form properties
            foreach ($formData->getFormProperties() as $formProperty) {
                $this->renderFormField(new FormPropertyAdapter($formProperty), $documentBuilder);
            }

            // end document element
            $documentBuilder->endElement();

            return $documentBuilder->getHtmlString();
        }
    }

    protected function renderFormField(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        // start group
        $divElement = (new HtmlElementWriter(self::DIV_ELEMENT))
            ->attribute(self::CLASS_ATTRIBUTE, self::FORM_GROUP_CLASS);

        $documentBuilder->startElement($divElement);

        $formFieldId = $formField->getId();
        $formFieldLabel = $formField->getLabel();

        // write label
        if ($formFieldLabel !== null && !empty($formFieldLabel)) {
            $labelElement = (new HtmlElementWriter(self::LABEL_ELEMENT))
                ->attribute(self::FOR_ATTRIBUTE, $formFieldId)
                ->textContent($formFieldLabel);

            // <label for="...">...</label>
            $documentBuilder->startElement($labelElement)->endElement();
        }

        // render form control
        if ($this->isEnum($formField)) {
            // <select ...>
            $this->renderSelectBox($formField, $documentBuilder);
        } elseif ($this->isDate($formField)) {
            $this->renderDatePicker($formField, $documentBuilder);
        } else {
            // <input ...>
            $this->renderInputField($formField, $documentBuilder);
        }

        $this->renderInvalidMessageElement($formField, $documentBuilder);
        // end group
        $documentBuilder->endElement();
    }

    protected function createInputField(FormFieldInterface $formField): HtmlElementWriter
    {
        $inputField = new HtmlElementWriter(self::INPUT_ELEMENT, true);

        $this->addCommonFormFieldAttributes($formField, $inputField);

        $inputField->attribute(self::TYPE_ATTRIBUTE, self::TEXT_INPUT_TYPE);

        return $inputField;
    }

    protected function renderDatePicker(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $isReadOnly = $this->isReadOnly($formField);

        // start input-group
        $inputGroupDivElement = (new HtmlElementWriter(self::DIV_ELEMENT))
            ->attribute(self::CLASS_ATTRIBUTE, self::INPUT_GROUP_CLASS);

        $formFieldId = $formField->getId();

        // <div>
        $documentBuilder->startElement($inputGroupDivElement);

        // input field
        $inputField = $this->createInputField($formField);

        if (!$isReadOnly) {
            $inputField
                ->attribute(self::DATEPICKER_POPUP_ATTRIBUTE, self::DATE_FORMAT)
                ->attribute(self::IS_OPEN_ATTRIBUTE, sprintf(self::DATE_FIELD_OPENED_ATTRIBUTE, $formFieldId));
        }

        // <input ... />
        $documentBuilder
            ->startElement($inputField)
            ->endElement();


        // if form field is read only, do not render date picker open button
        if (!$isReadOnly) {
            // input addon
            $addonElement = (new HtmlElementWriter(self::DIV_ELEMENT))
                ->attribute(self::CLASS_ATTRIBUTE, self::INPUT_GROUP_BTN_CLASS);

            // <div>
            $documentBuilder->startElement($addonElement);

            // button to open date picker
            $buttonElement = (new HtmlElementWriter(self::BUTTON_ELEMENT))
                ->attribute(self::TYPE_ATTRIBUTE, self::BUTTON_BUTTON_TYPE)
                ->attribute(self::CLASS_ATTRIBUTE, self::BUTTON_DEFAULT_CLASS)
                ->attribute(self::NG_CLICK_ATTRIBUTE, sprintf(self::OPEN_DATEPICKER_FUNCTION_SNIPPET, $formFieldId));

            // <button>
            $documentBuilder->startElement($buttonElement);

            $iconElement = (new HtmlElementWriter(self::I_ELEMENT))
                ->attribute(self::CLASS_ATTRIBUTE, self::CALENDAR_GLYPHICON);

            // <i ...></i>
            $documentBuilder
                ->startElement($iconElement)
                ->endElement();

            // </button>
            $documentBuilder->endElement();

            // </div>
            $documentBuilder->endElement();


            /*HtmlElementWriter scriptElement = new HtmlElementWriter(SCRIPT_ELEMENT)
                ->attribute(CAM_SCRIPT_ATTRIBUTE, null)
                ->attribute(TYPE_ATTRIBUTE, TEXT_FORM_SCRIPT_TYPE)
                ->textContent(String.format(OPEN_DATEPICKER_SNIPPET, $formFieldId, $formFieldId));*/

            // <script ...> </script>
            /*$documentBuilder
                ->startElement(scriptElement)
                ->endElement();*/
        }
        // </div>
        $documentBuilder->endElement();
    }

    protected function renderInputField(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $inputField = new HtmlElementWriter(self::INPUT_ELEMENT, true);
        $this->addCommonFormFieldAttributes($formField, $inputField);

        $inputType = !$this->isBoolean($formField) ? self::TEXT_INPUT_TYPE : self::CHECKBOX_INPUT_TYPE;

        $inputField->attribute(self::TYPE_ATTRIBUTE, $inputType);

        // add default value
        $defaultValue = $formField->getDefaultValue();
        if ($defaultValue !== null) {
            $inputField->attribute(self::VALUE_ATTRIBUTE, strval($defaultValue));
        }

        // <input ... />
        $documentBuilder->startElement($inputField)->endElement();
    }

    protected function renderSelectBox(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $selectBox = new HtmlElementWriter(self::SELECT_ELEMENT, false);

        $this->addCommonFormFieldAttributes($formField, $selectBox);

        // <select ...>
        $documentBuilder->startElement($selectBox);

        // <option ...>
        $this->renderSelectOptions($formField, $documentBuilder);

        // </select>
        $documentBuilder->endElement();
    }

    protected function renderSelectOptions(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $enumFormType = $formField->getType();
        $values = $enumFormType->getValues();

        foreach ($values as $key => $value) {
            // <option>
            $option = (new HtmlElementWriter(self::OPTION_ELEMENT, false))
                ->attribute(self::VALUE_ATTRIBUTE, $key)
                ->textContent($value);

            $documentBuilder->startElement(option)->endElement();
        }
    }

    protected function renderInvalidMessageElement(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $divElement = new HtmlElementWriter(self::DIV_ELEMENT);

        $formFieldId = $formField->getId();
        $ifExpression = sprintf(self::INVALID_EXPRESSION . " && " . self::DIRTY_EXPRESSION, $formFieldId, $formFieldId);

        $divElement
            ->attribute(self::NG_IF_ATTRIBUTE, $ifExpression)
            ->attribute(self::CLASS_ATTRIBUTE, self::HAS_ERROR_CLASS);

        // <div ng-if="....$invalid && ....$dirty"...>
        $documentBuilder->startElement($divElement);

        if (!isDate($formField)) {
            $this->renderInvalidValueMessage($formField, $documentBuilder);
            $this->renderInvalidTypeMessage($formField, $documentBuilder);
        } else {
            $this->renderInvalidDateMessage($formField, $documentBuilder);
        }

        $documentBuilder->endElement();
    }

    protected function renderInvalidValueMessage(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $divElement = new HtmlElementWriter(self::DIV_ELEMENT);

        $formFieldId = $formField->getId();

        $expression = sprintf(self::REQUIRED_ERROR_EXPRESSION, $formFieldId);

        $divElement
            ->attribute(self::NG_SHOW_ATTRIBUTE, $expression)
            ->attribute(self::CLASS_ATTRIBUTE, self::HELP_BLOCK_CLASS)
            ->textContent(self::REQUIRED_FIELD_MESSAGE);

        $documentBuilder
            ->startElement($divElement)
            ->endElement();
    }

    protected function renderInvalidTypeMessage(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $divElement = new HtmlElementWriter(self::DIV_ELEMENT);

        $formFieldId = $formField->getId();

        $expression = sprintf(self::TYPE_ERROR_EXPRESSION, $formFieldId);

        $typeName = $formField->getTypeName();

        if ($this->isEnum($formField)) {
            $typeName = StringFormType::TYPE_NAME;
        }

        $divElement
            ->attribute(self::NG_SHOW_ATTRIBUTE, $expression)
            ->attribute(self::CLASS_ATTRIBUTE, self::HELP_BLOCK_CLASS)
            ->textContent(sprintf(self::TYPE_FIELD_MESSAGE, $typeName));

        $documentBuilder
            ->startElement($divElement)
            ->endElement();
    }

    protected function renderInvalidDateMessage(FormFieldInterface $formField, HtmlDocumentBuilder $documentBuilder): void
    {
        $formFieldId = $formField->getId();

        $firstDivElement = new HtmlElementWriter(self::DIV_ELEMENT);

        $firstExpression = sprintf(self::REQUIRED_ERROR_EXPRESSION . " && !" . self::DATE_ERROR_EXPRESSION, $formFieldId, $formFieldId);

        $firstDivElement
            ->attribute(self::NG_SHOW_ATTRIBUTE, $firstExpression)
            ->attribute(self::CLASS_ATTRIBUTE, self::HELP_BLOCK_CLASS)
            ->textContent(self::REQUIRED_FIELD_MESSAGE);

        $documentBuilder
            ->startElement($firstDivElement)
            ->endElement();

        $secondDivElement = new HtmlElementWriter(self::DIV_ELEMENT);

        $secondExpression = sprintf(self::DATE_ERROR_EXPRESSION, $formFieldId);

        $secondDivElement
            ->attribute(self::NG_SHOW_ATTRIBUTE, $secondExpression)
            ->attribute(self::CLASS_ATTRIBUTE, self::HELP_BLOCK_CLASS)
            ->textContent(self::INVALID_DATE_FIELD_MESSAGE);

        $documentBuilder
            ->startElement($secondDivElement)
            ->endElement();
    }

    protected function addCommonFormFieldAttributes(FormFieldInterface $formField, HtmlElementWriter $formControl): void
    {
        $typeName = $formField->getTypeName();

        if ($this->isEnum($formField) || $this->isDate($formField)) {
            $typeName = StringFormType::TYPE_NAME;
        }

        $typeName = strtoupper(substr($typeName, 0, 1)) . substr($typeName, 1);

        $formFieldId = $formField->getId();

        $formControl
            ->attribute(self::CLASS_ATTRIBUTE, self::FORM_CONTROL_CLASS)
            ->attribute(self::NAME_ATTRIBUTE, $formFieldId);
            //->attribute(CAM_VARIABLE_TYPE_ATTRIBUTE, typeName)
            //->attribute(CAM_VARIABLE_NAME_ATTRIBUTE, $formFieldId);

        // add validation constraints
        foreach ($formField->getValidationConstraints() as $constraint) {
            $constraintName = $constraint->getName();
            $configuration = $constraint->getConfiguration();
            $formControl->attribute($constraintName, $configuration);
        }
    }

    // helper /////////////////////////////////////////////////////////////////////////////////////

    protected function isEnum(FormFieldInterface $formField): bool
    {
        return EnumFormType::TYPE_NAME == $formField->getTypeName();
    }

    protected function isDate(FormFieldInterface $formField): bool
    {
        return DateFormType::TYPE_NAME == $formField->getTypeName();
    }

    protected function isBoolean(FormFieldInterface $formField): bool
    {
        return BooleanFormType::TYPE_NAME == $formField->getTypeName();
    }

    protected function isReadOnly(FormFieldInterface $formField): bool
    {
        $validationConstraints = $formField->getValidationConstraints();
        if (!empty($validationConstraints)) {
            foreach ($validationConstraints as $validationConstraint) {
                if ("readonly" == $validationConstraint->getName()) {
                    return true;
                }
            }
        }
        return false;
    }
}
