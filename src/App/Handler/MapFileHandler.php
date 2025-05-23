<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MapFileHandler implements RequestHandlerInterface
{
    private $containerName;

    private $router;

    private $template;

    public function __construct(
        Router\RouterInterface $router,
        ?Template\TemplateRendererInterface $template,
        string $containerName
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->containerName = $containerName;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $data = [];

        if ($session->has('map')) {
            $map = unserialize($session->get('map'));

            $temp = tempnam(sys_get_temp_dir(), 'mapfile-');

            (new \MapFile\Writer\Map($map))->save($temp);

            $data['mapfile'] = file_get_contents($temp);
        }

        return new HtmlResponse($this->template->render('app::mapfile', $data));
    }
}
