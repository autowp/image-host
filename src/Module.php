<?php

namespace Autowp\ImageHost;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager        = $app->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        //handle the dispatch error (exception)
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'handleError']);
        //handle the view render error (exception)
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'handleError']);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'controllers'        => $provider->getControllersConfig(),
            'controller_plugins' => $provider->getControllerPluginsConfig(),
            'db'                 => $provider->getDbConfig(),
            'input_filters'      => $provider->getInputFilterConfig(),
            'input_filter_specs' => $provider->getInputFilterSpecsConfig(),
            'log'                => $provider->getLogConfig(),
            'router'             => $provider->getRouterConfig(),
            'validators'         => $provider->getValidatorsConfig(),
            'view_manager'       => $provider->getViewManagerConfig(),
        ];
    }

    public function handleError(MvcEvent $e)
    {
        $exception = $e->getParam('exception');
        if ($exception) {
            $serviceManager = $e->getApplication()->getServiceManager();
            $serviceManager->get('ErrorLog')->crit($exception);
        }
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ]
            ]
        ];
    }
}
