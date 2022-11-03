<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use MapFile\Model\Style;
use Mezzio\Router;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StyleHandler implements RequestHandlerInterface
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
            $class = intval($request->getAttribute('class'));

            $id = $request->getAttribute('id');

            if ($map->layer->containsKey($layer) && $map->layer->get($layer)->class->containsKey($class)) {
                if (is_null($id)) {
                    $style = new Style();

                    $map->layer->get($layer)->class->get($class)->style->add($style);

                    $session->set('map', serialize($map));
                } elseif ($map->layer->get($layer)->class->get($class)->style->containsKey(intval($id))) {
                    $style = $map->layer->get($layer)->class->get($class)->style->get(intval($id));
                }
            }

            if (isset($style)) {
                $data = [
                    'map'   => $map,
                    'layer' => $map->layer->get($layer),
                    'class' => $map->layer->get($layer)->class->get($class),
                    'style' => $style,
                ];

                return new HtmlResponse($this->template->render('app::style', $data));
            }
        }

        return new RedirectResponse($this->router->generateUri('map'));
    }
}
