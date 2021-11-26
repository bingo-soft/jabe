<?php

namespace BpmPlatform\Engine\Impl\Scripting;

use BpmPlatform\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Persistence\Entity\DeploymentEntity;
use BpmPlatform\Engine\Impl\Util\ResourceUtil;

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
