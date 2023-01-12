<?php

namespace Jabe\Impl\Form\Validator;

use Jabe\{
    ProcessEngineConfiguration,
    ProcessEngineException
};
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\El\ExpressionManager;
use Jabe\Impl\Util\StringUtil;
use Sax\Element;

class FormValidators
{
    /** the registry of configured validators. Populated through ProcessEngineConfiguration. */
    protected $validators = [];

    /**
     * factory method for creating validator instances
     *
     */
    public function createValidator(Element $constraint, BpmnParse $bpmnParse, ExpressionManager $expressionManager): ?FormFieldValidatorInterface
    {
        $name = $constraint->attribute("name");
        $config = $constraint->attribute("config");

        if ("validator" == $name) {
            // custom validators

            if ($config === null || empty($config)) {
                $bpmnParse->addError(
                    "validator configuration needs to provide either a fully " .
                    "qualified classname or an expression resolving to a custom FormFieldValidator implementation.",
                    $constraint
                );
            } else {
                if (StringUtil::isExpression($config)) {
                    // expression
                    $validatorExpression = $expressionManager->createExpression($config);
                    return new DelegateFormFieldValidator($validatorExpression);
                } else {
                    // classname
                    return new DelegateFormFieldValidator($config);
                }
            }
        } else {
            // built-in validators
            $validator = null;
            if (array_key_exists($name, $this->validators)) {
                $validator = $this->validators[$name];
            }
            if ($validator !== null) {
                $validatorInstance = $this->createValidatorInstance($validator);
                return $validatorInstance;
            } else {
                $bpmnParse->addError("Cannot find validator implementation for name '" . $name . "'.", $constraint);
            }
        }

        return null;
    }

    protected function createValidatorInstance(?string $validator): FormFieldValidatorInterface
    {
        try {
            return new $validator();
        } catch (\Exception $e) {
            throw new ProcessEngineException("Could not instantiate validator", $e);
        }
    }

    public function addValidator(?string $name, ?string $validatorType): void
    {
        $this->validators[$name] = $validatorType;
    }

    public function getValidators(): array
    {
        return $this->validators;
    }
}
