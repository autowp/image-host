<?php

namespace Autowp\ImageHost\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class FormatControllerFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $filters = $container->get('InputFilterManager');
        return new FormatController(
            $container->get(\Autowp\Image\StorageInterface::class),
            $filters->get('ih_api_format_list'),
            $filters->get('ih_api_format_delete')
        );
    }
}
