<?php

declare(strict_types=1);

namespace App\Handler\API;

use InvalidArgumentException;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use MapFile\Model\Style as StyleObject;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StyleHandler implements RequestHandlerInterface
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
                    $style = $class->style->get($id);

                    if (!is_null($style)) {
                        switch ($method) {
                            case 'PUT':
                                $params = $request->getParsedBody();

                                $style = self::put($style, $params);

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
                                $class->style->remove($id);

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

    private static function put(StyleObject $style, array &$params): StyleObject
    {
        if (isset($params['color-r'])) {
            $style->color[0] = intval($params['color-r']);
            unset($params['color-r']);
        }
        if (isset($params['color-g'])) {
            $style->color[1] = intval($params['color-g']);
            unset($params['color-g']);
        }
        if (isset($params['color-b'])) {
            $style->color[2] = intval($params['color-b']);
            unset($params['color-b']);
        }
        if (isset($params['maxscaledenom'])) {
            $style->maxscaledenom = !empty($params['maxscaledenom']) ? floatval($params['maxscaledenom']) : null;
            unset($params['maxscaledenom']);
        }
        if (isset($params['minscaledenom'])) {
            $style->minscaledenom = !empty($params['minscaledenom']) ? floatval($params['minscaledenom']) : null;
            unset($params['minscaledenom']);
        }
        if (isset($params['outlinecolor-r'])) {
            $style->outlinecolor[0] = intval($params['outlinecolor-r']);
            unset($params['outlinecolor-r']);
        }
        if (isset($params['outlinecolor-g'])) {
            $style->outlinecolor[1] = intval($params['outlinecolor-g']);
            unset($params['outlinecolor-g']);
        }
        if (isset($params['outlinecolor-b'])) {
            $style->outlinecolor[2] = intval($params['outlinecolor-b']);
            unset($params['outlinecolor-b']);
        }
        if (isset($params['size'])) {
            $style->size = trim($params['size']);
            unset($params['size']);
        }
        if (isset($params['symbol'])) {
            $style->symbol = trim($params['symbol']);
            unset($params['symbol']);
        }
        if (isset($params['width'])) {
            $style->width = trim($params['width']);
            unset($params['width']);
        }

        return $style;
    }
}
