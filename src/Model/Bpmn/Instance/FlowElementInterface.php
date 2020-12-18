<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface FlowElementInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getAuditing(): AuditingInterface;

    public function setAuditing(AuditingInterface $auditing): void;

    public function getMonitoring(): MonitoringInterface;

    public function setMonitoring(MonitoringInterface $monitoring): void;

    public function getCategoryValueRefs(): array;
}
