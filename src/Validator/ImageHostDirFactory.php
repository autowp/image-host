<?php

namespace Autowp\ImageHost\Validator;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ImageHostDirFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ImageHostDir(array_merge($options ? $options : [], [
            'storage' => $container->get(\Autowp\Image\StorageInterface::class)
        ]));
    }
}
