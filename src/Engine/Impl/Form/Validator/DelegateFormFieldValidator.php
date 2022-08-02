<?php

namespace Jabe\Engine\Impl\Form\Validator;

use Jabe\Engine\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\{
    DelegateExecutionInterface,
    ExpressionInterface
};
use Jabe\Engine\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Engine\Impl\Util\ReflectUtil;

class DelegateFormFieldValidator implements FormFieldValidatorInterface
{
    protected $clazz;
    protected $delegateExpression;

    public function __construct($data = null)
    {
        if ($data !== null) {
            if ($data instanceof ExpressionInterface) {
                $this->delegateExpression = $data;
            } elseif (is_string($data)) {
                $this->clazz = $data;
            }
        }
    }

    public function validate($submittedValue, FormFieldValidatorContextInterface $validatorContext): bool
    {
        $execution = $validatorContext->getExecution();
        if ($this->shouldPerformPaContextSwitch($validatorContext->getExecution())) {
            $processApplicationReference = ProcessApplicationContextUtil::getTargetProcessApplication($execution);
            $scope = $this;
            return Context::executeWithinProcessApplication(function () use ($scope, $submittedValue, $validatorContext) {
                return $scope->doValidate($submittedValue, $validatorContext);
            }, $processApplicationReference, new InvocationContext($execution));
        } else {
            return $this->doValidate($submittedValue, $validatorContext);
        }
    }

    protected function shouldPerformPaContextSwitch(?DelegateExecutionInterface $execution): bool
    {
        if ($execution === null) {
            return false;
        } else {
            $targetPa = ProcessApplicationContextUtil::getTargetProcessApplication($execution);
            return $targetPa !== null && $targetPa != Context::getCurrentProcessApplication();
        }
    }

    protected function doValidate($submittedValue, FormFieldValidatorContext $validatorContext): bool
    {
        $validator = null;

        if ($this->clazz !== null) {
            // resolve validator using Fully Qualified Classname
            $validatorObject = ReflectUtil::instantiate($this->clazz);
            if ($validatorObject instanceof FormFieldValidatorInterface) {
                $validator = $validatorObject;
            } else {
                throw new ProcessEngineException("Validator class '" . $this->clazz . "' is not an instance of " .  FormFieldValidatorInterface::class);
            }
        } else {
            //resolve validator using expression
            $validatorObject = $this->delegateExpression->getValue($validatorContext->getExecution());
            if ($validatorObject instanceof FormFieldValidatorInterface) {
                $validator = $validatorObject;
            } else {
                throw new ProcessEngineException("Validator expression '" . $this->delegateExpression . "' does not resolve to instance of " .  FormFieldValidatorInterface::class);
            }
        }

        $invocation = new FormFieldValidatorInvocation($validator, $submittedValue, $validatorContext);
        try {
            Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation($invocation);
        } catch (\Exception $e) {
            throw $e;
        }

        return $invocation->getInvocationResult();
    }
}
