<?php

namespace Tests\Bpmn;

class BpmnTestConstants
{
    public const COLLABORATION_ID = 'collaboration';
    public const PARTICIPANT_ID = 'participant';
    public const PROCESS_ID = 'process';
    public const START_EVENT_ID = 'startEvent';
    public const TASK_ID = 'task';
    public const USER_TASK_ID = 'userTask';
    public const SERVICE_TASK_ID = 'serviceTask';
    public const EXTERNAL_TASK_ID = 'externalTask';
    public const SEND_TASK_ID = 'sendTask';
    public const SCRIPT_TASK_ID = 'scriptTask';
    public const SEQUENCE_FLOW_ID = 'sequenceFlow';
    public const MESSAGE_FLOW_ID = 'messageFlow';
    public const DATA_INPUT_ASSOCIATION_ID = 'dataInputAssociation';
    public const ASSOCIATION_ID = 'association';
    public const CALL_ACTIVITY_ID = 'callActivity';
    public const BUSINESS_RULE_TASK = 'businessRuleTask';
    public const END_EVENT_ID = 'endEvent';
    public const EXCLUSIVE_GATEWAY = 'exclusiveGateway';
    public const SUB_PROCESS_ID = 'subProcess';
    public const TRANSACTION_ID = 'transaction';
    public const CONDITION_ID = 'condition';
    public const BOUNDARY_ID = 'boundary';
    public const CATCH_ID = 'catch';

    public const TEST_STRING_XML = 'test';
    public const TEST_STRING_API = 'api';
    public const TEST_CLASS_XML = 'org.camunda.test.Test';
    public const TEST_CLASS_API = 'org.camunda.test.Api';
    public const TEST_EXPRESSION_XML = '${' . TEST_STRING_XML . '}';
    public const TEST_EXPRESSION_API = '${' . TEST_STRING_API . '}';
    public const TEST_DELEGATE_EXPRESSION_XML = '${' . TEST_CLASS_XML . '}';
    public const TEST_DELEGATE_EXPRESSION_API = '${' . TEST_CLASS_API . '}';
    public const TEST_GROUPS_XML = 'group1, ${group2(a, b)}, group3';
    public const TEST_GROUPS_LIST_XML = ['group1', '${group2(a, b)}', 'group3'];
    public const TEST_GROUPS_API = '#{group1( c,d)}, group5';
    public const TEST_GROUPS_LIST_API = ['#{group1( c,d)}', 'group5'];
    public const TEST_USERS_XML = 'user1, ${user2(a, b)}, user3';
    public const TEST_USERS_LIST_XML = ['user1', '${user2(a, b)}', 'user3'];
    public const TEST_USERS_API = '#{user1( c,d)}, user5';
    public const TEST_USERS_LIST_API = ['#{user1( c,d)}', 'user5'];
    public const TEST_DUE_DATE_XML = '2014-02-27';
    public const TEST_DUE_DATE_API = '2015-03-28';
    public const TEST_FOLLOW_UP_DATE_API = '2015-01-01';
    public const TEST_PRIORITY_XML = '12';
    public const TEST_PRIORITY_API = '${dateVariable}';
    public const TEST_TYPE_XML = 'mail';
    public const TEST_TYPE_API = 'shell';
    public const TEST_EXECUTION_EVENT_XML = 'start';
    public const TEST_EXECUTION_EVENT_API = 'end';
    public const TEST_TASK_EVENT_XML = 'create';
    public const TEST_TASK_EVENT_API = 'complete';
    public const TEST_FLOW_NODE_JOB_PRIORITY = '${test}';
    public const TEST_PROCESS_JOB_PRIORITY = '15';
    public const TEST_PROCESS_TASK_PRIORITY = '13';
    public const TEST_SERVICE_TASK_PRIORITY = '${test}';
    public const TEST_EXTERNAL_TASK_TOPIC = '${externalTaskTopic}';
    public const TEST_HISTORY_TIME_TO_LIVE = 5;
    public const TEST_STARTABLE_IN_TASKLIST = false;
    public const TEST_VERSION_TAG = 'v1.0.0';

    public const TEST_CONDITION = '${true}';
    public const TEST_CONDITIONAL_VARIABLE_NAME = 'variable';
    public const TEST_CONDITIONAL_VARIABLE_EVENTS = 'create, update';
    public const TEST_CONDITIONAL_VARIABLE_EVENTS_LIST = ['create', 'update'];
}
