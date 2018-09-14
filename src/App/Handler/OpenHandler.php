<?php

declare(strict_types=1);

namespace App\Handler;

use Exception;
use MapFile\Parser\Map as MapParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\UploadedFile;
use Zend\Expressive\Router;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template;

class OpenHandler implements RequestHandlerInterface
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

        $method = $request->getMethod();

        switch ($method) {
            case 'POST':
                try {
                    $files = $request->getUploadedFiles();

                    if (isset($files['mapfile'])) {
                        if ($files['mapfile']->getError() === UPLOAD_ERR_OK) {
                            $temp = tempnam(sys_get_temp_dir(), 'mapfile-');

                            $files['mapfile']->moveTo($temp);

                            $map = (new MapParser($temp))->parse();

                            $session->set('map', serialize($map));

                            return new RedirectResponse($this->router->generateUri('map'));
                        } else {
                            throw new Exception(UploadedFile::ERROR_MESSAGES[$files['mapfile']->getError()]);
                        }
                    } else {
                        throw new Exception(UploadedFile::ERROR_MESSAGES[UPLOAD_ERR_NO_FILE]);
                    }
                } catch (Exception $e) {
                    $data = [
                        'error' => $e->getMessage(),
                    ];

                    return new HtmlResponse($this->template->render('app::open', $data));
                }
                break;

            case 'GET':
                return new HtmlResponse($this->template->render('app::open', []));
                break;
        }
    }
}
