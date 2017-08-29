<?php

namespace Autowp\ImageHost\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ImageControllerFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $filters = $container->get('InputFilterManager');
        return new ImageController(
            $container->get(\Autowp\Image\StorageInterface::class),
            $filters->get('ih_api_image_list'),
            $filters->get('ih_api_image_get'),
            $filters->get('ih_api_image_post'),
            $filters->get('ih_api_image_put')
        );
    }
}
