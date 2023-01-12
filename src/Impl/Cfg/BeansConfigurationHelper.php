<?php

namespace Jabe\Impl\Cfg;

use Jabe\ProcessEngineConfiguration;

class BeansConfigurationHelper
{
    public static function parseProcessEngineConfiguration($resource, ?string $beanName): ?ProcessEngineConfiguration
    {
        /*DefaultListableBeanFactory beanFactory = new DefaultListableBeanFactory();
        XmlBeanDefinitionReader xmlBeanDefinitionReader = new XmlBeanDefinitionReader(beanFactory);
        xmlBeanDefinitionReader.setValidationMode(XmlBeanDefinitionReader.VALIDATION_XSD);
        xmlBeanDefinitionReader.loadBeanDefinitions(springResource);
        ProcessEngineConfigurationImpl processEngineConfiguration = (ProcessEngineConfigurationImpl) beanFactory.getBean(beanName);
        if (processEngineConfiguration.getBeans() == null) {
            processEngineConfiguration.setBeans(new SpringBeanFactoryProxyMap(beanFactory));
        }
        return processEngineConfiguration;*/
        $builder = new XMLConfigBuilder($resource);
        return $builder->build();
    }

    public static function parseProcessEngineConfigurationFromInputStream($inputStream, ?string $beanName): ?ProcessEngineConfiguration
    {
        return self::parseProcessEngineConfiguration($inputStream, $beanName);
    }

    public static function parseProcessEngineConfigurationFromResource($resource, ?string $beanName): ?ProcessEngineConfiguration
    {
        return self::parseProcessEngineConfiguration($resource, $beanName);
    }
}
