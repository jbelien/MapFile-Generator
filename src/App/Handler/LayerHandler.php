<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use MapFile\Model\Layer;
use Mezzio\Router;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LayerHandler implements RequestHandlerInterface
{
    private $containerName;

    private $router;

    private $template;

    public function __construct(
        Router\RouterInterface $router,
        ?Template\TemplateRendererInterface $template = null,
        string $containerName
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->containerName = $containerName;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if ($session->has('map')) {
            $map = unserialize($session->get('map'));

            $id = $request->getAttribute('id');

            if (is_null($id)) {
                $layer = new Layer();

                $map->layer->add($layer);

                $session->set('map', serialize($map));
            } elseif ($map->layer->containsKey(intval($id))) {
                $layer = $map->layer->get(intval($id));
            }

            if (isset($layer)) {
                $data = [
                    'map'   => $map,
                    'layer' => $layer,
                ];

                return new HtmlResponse($this->template->render('app::layer', $data));
            }
        }

        return new RedirectResponse($this->router->generateUri('map'));
    }
}
