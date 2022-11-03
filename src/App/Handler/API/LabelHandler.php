<?php

declare(strict_types=1);

namespace App\Handler\API;

use InvalidArgumentException;
use MapFile\Model\Label as LabelObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Session\SessionMiddleware;

class LabelHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $method = $request->getMethod();

        if ($session->has('map')) {
            $map = unserialize($session->get('map'));

            $idLayer = $request->getAttribute('layer');
            $idClass = $request->getAttribute('class');
            $id = $request->getAttribute('id');

            $layer = $map->layer->get($idLayer);

            if (!is_null($layer)) {
                $class = $layer->class->get($idClass);

                if (!is_null($class)) {
                    $label = $class->label->get($id);

                    if (!is_null($label)) {
                        switch ($method) {
                            case 'PUT':
                                $params = $request->getParsedBody();

                                $label = self::put($label, $params);

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
                                $class->label->remove($id);

                                break;
                        }

                        $session->set('map', serialize($map));

                        return new JsonResponse([
                            'status' => 'success',
                        ]);
                    }
                }
            }
        }

        return (new EmptyResponse())->withStatus(400);
    }

    private static function put(LabelObject $label, array &$params): LabelObject
    {
        if (isset($params['align'])) {
            $label->align = trim($params['align']);
            unset($params['align']);
        }
        if (isset($params['color-r'])) {
            $label->color[0] = intval($params['color-r']);
            unset($params['color-r']);
        }
        if (isset($params['color-g'])) {
            $label->color[1] = intval($params['color-g']);
            unset($params['color-g']);
        }
        if (isset($params['color-b'])) {
            $label->color[2] = intval($params['color-b']);
            unset($params['color-b']);
        }
        if (isset($params['maxscaledenom'])) {
            $label->maxscaledenom = !empty($params['maxscaledenom']) ? floatval($params['maxscaledenom']) : null;
            unset($params['maxscaledenom']);
        }
        if (isset($params['minscaledenom'])) {
            $label->minscaledenom = !empty($params['minscaledenom']) ? floatval($params['minscaledenom']) : null;
            unset($params['minscaledenom']);
        }
        if (isset($params['outlinecolor-r'])) {
            $label->outlinecolor[0] = intval($params['outlinecolor-r']);
            unset($params['outlinecolor-r']);
        }
        if (isset($params['outlinecolor-g'])) {
            $label->outlinecolor[1] = intval($params['outlinecolor-g']);
            unset($params['outlinecolor-g']);
        }
        if (isset($params['outlinecolor-b'])) {
            $label->outlinecolor[2] = intval($params['coloutlinecoloror-b']);
            unset($params['outlinecolor-b']);
        }
        if (isset($params['position'])) {
            $label->position = trim($params['position']);
            unset($params['position']);
        }

        return $label;
    }
}
