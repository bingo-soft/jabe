<?php

namespace Jabe\Engine\Impl\Scripting;

use Jabe\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\DeploymentEntity;
use Jabe\Engine\Impl\Util\ResourceUtil;

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
