<?php

declare (strict_types = 1);

namespace App\Handler\API;

use InvalidArgumentException;
use MapFile\Model\Layer as LayerObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Session\SessionMiddleware;

class LayerHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $method = $request->getMethod();

        if ($session->has('map')) {
            $map = unserialize($session->get('map'));

            $id = $request->getAttribute('id');

            $layer = $map->layer->get($id);

            if (!is_null($layer)) {
                switch ($method) {
                    case 'PUT':
                        $params = $request->getParsedBody();

                        $layer = self::put($layer, $params);

                        if (!empty($params)) {
                            throw new InvalidArgumentException(
                                sprintf(
                                    'Invalid parameter(s): %s.',
                                    implode(', ', array_keys($params))
                                )
                            );
                        }
                        break;

                    case 'DELETE':
                        $map->layer->remove($id);

                        break;
                }

                $session->set('map', serialize($map));

                return new JsonResponse([
                    'status' => 'success',
                ]);
            }
        }

        return (new EmptyResponse())->withStatus(400);
    }

    private static function put(LayerObject $layer, array &$params): LayerObject
    {
        if (isset($params['classitem'])) {
            $layer->classitem = trim($params['classitem']);
            unset($params['classitem']);
        }
        if (isset($params['connection'])) {
            $layer->connection = trim($params['connection']);
            unset($params['connection']);
        }
        if (isset($params['connectiontype'])) {
            $layer->connectiontype = trim($params['connectiontype']);
            unset($params['connectiontype']);
        }
        if (isset($params['data'])) {
            $layer->data = trim($params['data']);
            unset($params['data']);
        }
        if (isset($params['filter'])) {
            $layer->filter = trim($params['filter']);
            unset($params['filter']);
        }
        if (isset($params['filteritem'])) {
            $layer->filteritem = trim($params['filteritem']);
            unset($params['filteritem']);
        }
        if (isset($params['group'])) {
            $layer->group = trim($params['group']);
            unset($params['group']);
        }
        if (isset($params['labelitem'])) {
            $layer->labelitem = trim($params['labelitem']);
            unset($params['labelitem']);
        }
        if (isset($params['maxscaledenom'])) {
            $layer->maxscaledenom = !empty($params['maxscaledenom']) ? floatval($params['maxscaledenom']) : null;
            unset($params['maxscaledenom']);
        }
        if (isset($params['minscaledenom'])) {
            $layer->minscaledenom = !empty($params['minscaledenom']) ? floatval($params['minscaledenom']) : null;
            unset($params['minscaledenom']);
        }
        if (isset($params['name'])) {
            $layer->name = trim($params['name']);
            unset($params['name']);
        }
        if (isset($params['projection'])) {
            $layer->projection = trim($params['projection']);
            unset($params['projection']);
        }
        if (isset($params['status'])) {
            $layer->status = trim($params['status']);
            unset($params['status']);
        }
        if (isset($params['type'])) {
            $layer->type = trim($params['type']);
            unset($params['type']);
        }

        return $layer;
    }
}
