<?php

namespace Autowp\ImageHost;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'controllers'        => $this->getControllersConfig(),
            'controller_plugins' => $this->getControllerPluginsConfig(),
            'db'                 => $this->getDbConfig(),
            'input_filters'      => $this->getInputFilterConfig(),
            'input_filter_specs' => $this->getInputFilterSpecsConfig(),
            'log'                => $this->getLogConfig(),
            'router'             => $this->getRouterConfig(),
            'validators'         => $this->getValidatorsConfig(),
            'view_manager'       => $this->getViewManagerConfig(),
        ];
    }

    public function getControllersConfig(): array
    {
        return [
            'factories' => [
                Controller\FormatController::class => Controller\FormatControllerFactory::class,
                Controller\ImageController::class  => Controller\ImageControllerFactory::class,
            ]
        ];
    }

    public function getControllerPluginsConfig(): array
    {
        return [
            'invokables' => [
                'inputFilterResponse' => Controller\Plugin\InputFilterResponse::class,
                'inputResponse'       => Controller\Plugin\InputResponse::class
            ]
        ];
    }

    public function getRouterConfig(): array
    {
        return [
            'routes' => [
                'api' => [
                    'type' => 'Literal',
                    'options' => [
                        'route'    => '/api',
                    ],
                    'may_terminate' => false,
                    'child_routes' => [
                        'format' => [
                            'type' => 'Literal',
                            'options' => [
                                'route'    => '/format',
                                'defaults' => [
                                    'controller' => Controller\FormatController::class,
                                ],
                            ],
                            'may_terminate' => false,
                            'child_routes' => [
                                'get' => [
                                    'type' => 'Method',
                                    'options' => [
                                        'verb' => 'get',
                                        'defaults' => [
                                            'action' => 'index'
                                        ]
                                    ]
                                ],
                                'delete' => [
                                    'type' => 'Method',
                                    'options' => [
                                        'verb' => 'delete',
                                        'defaults' => [
                                            'action' => 'delete'
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        'image' => [
                            'type' => 'Literal',
                            'options' => [
                                'route'    => '/image',
                                'defaults' => [
                                    'controller' => Controller\ImageController::class,
                                ],
                            ],
                            'may_terminate' => false,
                            'child_routes' => [
                                'get' => [
                                    'type' => 'Method',
                                    'options' => [
                                        'verb' => 'get',
                                        'defaults' => [
                                            'action' => 'index'
                                        ]
                                    ]
                                ],
                                'post' => [
                                    'type' => 'Method',
                                    'options' => [
                                        'verb' => 'post',
                                        'defaults' => [
                                            'action' => 'post'
                                        ]
                                    ]
                                ],
                                'item' => [
                                    'type' => 'Segment',
                                    'options' => [
                                        'route' => '/:id'
                                    ],
                                    'may_terminate' => false,
                                    'child_routes' => [
                                        'get' => [
                                            'type' => 'Method',
                                            'options' => [
                                                'verb' => 'get',
                                                'defaults' => [
                                                    'action' => 'get'
                                                ]
                                            ]
                                        ],
                                        'delete' => [
                                            'type' => 'Method',
                                            'options' => [
                                                'verb' => 'delete',
                                                'defaults' => [
                                                    'action' => 'delete'
                                                ]
                                            ]
                                        ],
                                        'put' => [
                                            'type' => 'Method',
                                            'options' => [
                                                'verb' => 'put',
                                                'defaults' => [
                                                    'action' => 'put'
                                                ]
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    public function getInputFilterConfig(): array
    {
        return [
            'abstract_factories' => [
                \Zend\InputFilter\InputFilterAbstractServiceFactory::class
            ]
        ];
    }

    public function getInputFilterSpecsConfig(): array
    {
        return [
            'ih_api_format_list' => [
                'id' => [
                    'required' => true
                ],
                'format' => [
                    'required' => true,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'max' => 255
                            ]
                        ],
                        ['name' => 'ImageHostFormat'],
                    ]
                ],
                'fields' => [
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim']
                    ],
                ]
            ],
            'ih_api_format_delete' => [
                'id' => [
                    'required' => true,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits']
                    ]
                ],
                'format' => [
                    'required' => true,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'max' => 255
                            ]
                        ],
                        ['name' => 'ImageHostFormat'],
                    ]
                ]
            ],
            'ih_api_image_list' => [
                'id' => [
                    'required' => false
                ],
                'fields' => [
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim']
                    ],
                ]
            ],
            'ih_api_image_get' => [
                'fields' => [
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim']
                    ],
                ]
            ],
            'ih_api_image_put' => [
                'name' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'max' => 255
                            ]
                        ],
                    ]
                ],
                'flop' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ]
                ],
                'normalize' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ]
                ]
            ],
            'ih_api_image_post' => [
                'dir' => [
                    'required' => true,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'max' => 255
                            ]
                        ],
                        ['name' => 'ImageHostDir'],
                    ]
                ],
                'name' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'max' => 255
                            ]
                        ],
                    ]
                ],
                'file' => [
                    'required' => true,
                    'validators' => [
                        'upload' => [
                            'name' => 'FileUploadFile',
                            'break_chain_on_failure' => true
                        ],
                        'size' => [
                            'name'    => 'FileSize',
                            'options' => [
                                'max'           => 50 * 1024 * 1024,
                                'useByteString' => false
                            ],
                            'break_chain_on_failure' => true
                        ],
                        /*'extenstion' => [
                            'name' => 'FileExtension',
                            'options' => [
                                'extension' => 'jpg,jpeg,jpe,png,gif,bmp'
                            ]
                        ],*/
                        'isimage' => [
                            'name' => 'FileIsImage',
                            'break_chain_on_failure' => true
                        ],
                        'imagesize' => [
                            'name' => 'FileImageSize',
                            'options' => [
                                'maxWidth'  => 1024 * 8,
                                'maxHeight' => 1024 * 8
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    public function getValidatorsConfig(): array
    {
        return [
            'aliases' => [
                'ImageHostDir'    => Validator\ImageHostDir::class,
                'ImageHostFormat' => Validator\ImageHostFormat::class
            ],
            'factories' => [
                Validator\ImageHostDir::class    => Validator\ImageHostDirFactory::class,
                Validator\ImageHostFormat::class => Validator\ImageHostFormatFactory::class
            ]
        ];
    }

    public function getLogConfig(): array
    {
        return [
            'ErrorLog' => [
                'writers' => [
                    [
                        'name' => 'stream',
                        'priority' => \Zend\Log\Logger::ERR,
                        'options' => [
                            'stream' => '/var/log/image-host/zf-error.log',
                            'processors' => [
                                [
                                    'name' => Log\Processor\Url::class
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getViewManagerConfig(): array
    {
        return [
            'display_not_found_reason' => true,
            'display_exceptions'       => true,
            'doctype'                  => 'HTML5',
            'not_found_template'       => 'error/404',
            'forbidden_template'       => 'error/403',
            'exception_template'       => 'error/index',
            'template_map' => [
                'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
                'error/403'               => __DIR__ . '/../view/error/403.phtml',
                'error/404'               => __DIR__ . '/../view/error/404.phtml',
                'error/index'             => __DIR__ . '/../view/error/index.phtml',
            ],
            'template_path_stack' => [
                __DIR__ . '/../view',
            ],
            'strategies' => [
                'ViewJsonStrategy',
            ],
        ];
    }

    public function getDbConfig(): array
    {
        return [
            'driver'    => 'Pdo',
            'pdodriver' => 'mysql',
            'host'      => 'mysql',
            'charset'   => 'utf8',
            'dbname'    => 'image_host',
            'username'  => 'root',
            'password'  => 'password',
        ];
    }
}
