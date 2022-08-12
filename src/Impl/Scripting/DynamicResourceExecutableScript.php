<?php

namespace Jabe\Impl\Scripting;

use Jabe\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Persistence\Entity\DeploymentEntity;
use Jabe\Impl\Util\ResourceUtil;

class DynamicResourceExecutableScript extends DynamicExecutableScript
{
    public function __construct(string $language, ExpressionInterface $scriptResourceExpression)
    {
        parent::__construct($scriptResourceExpression, $language);
    }

    public function getScriptSource(VariableScopeInterface $variableScope): string
    {
        $scriptPath = $this->evaluateExpression($variableScope);
        return ResourceUtil::loadResourceContent($scriptPath, $this->getDeployment());
    }

    protected function getDeployment(): DeploymentEntity
    {
        return Context::getBpmnExecutionContext()->getDeployment();
    }
}
