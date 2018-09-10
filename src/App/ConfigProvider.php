<?php

declare(strict_types=1);

namespace App;

/**
 * The configuration provider for the App module.
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array.
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies.
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [
                Handler\API\PingHandler::class => Handler\API\PingHandler::class,

                Handler\API\MapHandler::class   => Handler\API\MapHandler::class,
                Handler\API\LayerHandler::class => Handler\API\LayerHandler::class,
                Handler\API\ClassHandler::class => Handler\API\ClassHandler::class,
            ],
            'factories'  => [
                Handler\ClassHandler::class   => Handler\ClassHandlerFactory::class,
                Handler\LabelHandler::class   => Handler\LabelHandlerFactory::class,
                Handler\LayerHandler::class   => Handler\LayerHandlerFactory::class,
                Handler\MapHandler::class     => Handler\MapHandlerFactory::class,
                Handler\MapFileHandler::class => Handler\MapFileHandlerFactory::class,
                Handler\StyleHandler::class   => Handler\StyleHandlerFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration.
     */
    public function getTemplates() : array
    {
        return [
            'paths' => [
                'app'    => ['templates/app'],
                'error'  => ['templates/error'],
                'layout' => ['templates/layout'],
            ],
        ];
    }
}
