<?php

declare(strict_types=1);

namespace App\Handler\API;

use InvalidArgumentException;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use MapFile\Model\LayerClass as LayerClassObject;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClassHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $method = $request->getMethod();

        if ($session->has('map')) {
            $map = unserialize($session->get('map'));

            $idLayer = $request->getAttribute('layer');
            $id = $request->getAttribute('id');

            $layer = $map->layer->get($idLayer);

            if (!is_null($layer)) {
                $class = $layer->class->get($id);

                if (!is_null($class)) {
                    switch ($method) {
                        case 'PUT':
                            $params = $request->getParsedBody();

                            $class = self::put($class, $params);

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
                            $layer->class->remove($id);

                            break;
                    }

                    $session->set('map', serialize($map));

                    return new JsonResponse([
                        'status' => 'success',
                    ]);
                }
            }
        }

        return (new EmptyResponse())->withStatus(400);
    }

    private static function put(LayerClassObject $class, array &$params): LayerClassObject
    {
        if (isset($params['name'])) {
            $class->name = trim($params['name']);
            unset($params['name']);
        }
        if (isset($params['expression'])) {
            $class->expression = trim($params['expression']);
            unset($params['expression']);
        }

        return $class;
    }
}
