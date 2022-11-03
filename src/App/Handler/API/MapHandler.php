<?php

declare(strict_types=1);

namespace App\Handler\API;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MapHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if ($session->has('map')) {
            $map = unserialize($session->get('map'));

            $params = $request->getParsedBody();

            if (isset($params['extent-minx'])) {
                $map->extent[0] = intval($params['extent-minx']);
                unset($params['extent-minx']);
            }
            if (isset($params['extent-miny'])) {
                $map->extent[1] = intval($params['extent-miny']);
                unset($params['extent-miny']);
            }
            if (isset($params['extent-maxx'])) {
                $map->extent[2] = intval($params['extent-maxx']);
                unset($params['extent-maxx']);
            }
            if (isset($params['extent-maxy'])) {
                $map->extent[3] = intval($params['extent-maxy']);
                unset($params['extent-maxy']);
            }
            if (isset($params['name'])) {
                $map->name = trim($params['name']);
                unset($params['name']);
            }
            if (isset($params['projection'])) {
                $map->projection = trim($params['projection']);
                unset($params['projection']);
            }
            if (isset($params['size-x'])) {
                $map->size[0] = intval($params['size-x']);
                unset($params['size-x']);
            }
            if (isset($params['size-y'])) {
                $map->size[1] = intval($params['size-y']);
                unset($params['size-y']);
            }
            if (isset($params['status'])) {
                $map->status = trim($params['status']);
                unset($params['status']);
            }
            if (isset($params['units'])) {
                $map->units = trim($params['units']);
                unset($params['units']);
            }

            $session->set('map', serialize($map));

            if (empty($params)) {
                return new JsonResponse([
                    'status' => 'success',
                ]);
            }
        }

        return (new EmptyResponse())->withStatus(400);
    }
}
