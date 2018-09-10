<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

/*
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->get('/', App\Handler\MapHandler::class, 'map');
    $app->get('/layer/{id:\d+}', App\Handler\LayerHandler::class, 'layer');
    $app->get('/layer/{layer:\d+}/class/{id:\d+}', App\Handler\ClassHandler::class, 'class');
    $app->get('/layer/{layer:\d+}/class/{class:\d+}/label/{id:\d+}', App\Handler\LabelHandler::class, 'label');
    $app->get('/layer/{layer:\d+}/class/{class:\d+}/style/{id:\d+}', App\Handler\StyleHandler::class, 'style');
    $app->get('/mapfile', App\Handler\MapFileHandler::class, 'mapfile');
    $app->get('/mapfile/edit', App\Handler\MapFileHandler::class, 'mapfile.edit');

    $app->put('/api/map', App\Handler\API\MapHandler::class, 'api.map');
    $app->post('/api/layer', App\Handler\API\LayerHandler::class, 'api.layer.new');
    $app->route('/api/layer/{id:\d+}', App\Handler\API\LayerHandler::class, ['put', 'delete'], 'api.layer');
    $app->post('/api/layer/{layer:\d+}/class', App\Handler\API\ClassHandler::class, 'api.class.new');
    $app->route('/api/layer/{layer:\d+}/class/{id:\d+}', App\Handler\API\ClassHandler::class, ['put', 'delete'], 'api.class');
    $app->post('/api/layer/{layer:\d+}/class/{class:\d+}/label', App\Handler\API\LabelHandler::class, 'api.label.new');
    $app->route('/api/layer/{layer:\d+}/class/{class:\d+}/label/{id:\d+}', App\Handler\API\LabelHandler::class, ['put', 'delete'], 'api.label');
    $app->post('/api/layer/{layer:\d+}/class/{class:\d+}/style', App\Handler\API\StyleHandler::class, 'api.style.new');
    $app->route('/api/layer/{layer:\d+}/class/{class:\d+}/style/{id:\d+}', App\Handler\API\StyleHandler::class, ['put', 'delete'], 'api.style');

    $app->get('/api/ping', App\Handler\API\PingHandler::class, 'api.ping');
};
