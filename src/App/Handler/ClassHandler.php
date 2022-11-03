<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use MapFile\Model\LayerClass;
use Mezzio\Router;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClassHandler implements RequestHandlerInterface
{
    private $containerName;

    private $router;

    private $template;

    public function __construct(
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null,
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

            $layer = intval($request->getAttribute('layer'));

            $id = $request->getAttribute('id');

            if ($map->layer->containsKey($layer)) {
                if (is_null($id)) {
                    $class = new LayerClass();

                    $map->layer->get($layer)->class->add($class);

                    $session->set('map', serialize($map));
                } elseif ($map->layer->get($layer)->class->containsKey(intval($id))) {
                    $class = $map->layer->get($layer)->class->get(intval($id));
                }

                if (isset($class)) {
                    $data = [
                        'map'   => $map,
                        'layer' => $map->layer->get($layer),
                        'class' => $class,
                    ];

                    return new HtmlResponse($this->template->render('app::class', $data));
                }
            }
        }

        return new RedirectResponse($this->router->generateUri('map'));
    }
}
