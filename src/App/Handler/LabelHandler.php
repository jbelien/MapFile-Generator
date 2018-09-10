<?php

declare (strict_types = 1);

namespace App\Handler;

use MapFile\Parser\Map;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template;

class LabelHandler implements RequestHandlerInterface
{
    private $containerName;

    private $router;

    private $template;

    public function __construct(
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null,
        string $containerName
    ) {
        $this->router        = $router;
        $this->template      = $template;
        $this->containerName = $containerName;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if ($session->has('map')) {
            $map = unserialize($session->get('map'));

            $layer = intval($request->getAttribute('layer'));
            $class = intval($request->getAttribute('class'));
            $id    = intval($request->getAttribute('id'));

            if ($map->layer->containsKey($layer) &&
                $map->layer->get($layer)->class->containsKey($id) &&
                $map->layer->get($layer)->class->get($class)->label->containsKey($id)
            ) {
                    $data = [
                    'map'   => $map,
                    'layer' => $map->layer->get($layer),
                    'class' => $map->layer->get($layer)->class->get($class),
                    'label' => $map->layer->get($layer)->class->get($class)->label->get($id),
                ];

                return new HtmlResponse($this->template->render('app::label', $data));
            }
        }

        return new RedirectResponse($this->router->generateUri('map'));
    }
}
