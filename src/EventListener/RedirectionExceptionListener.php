<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class RedirectionExceptionListener
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Récupère l'exception
        $exception = $event->getThrowable();

        // Vérifie si l'exception doit entraîner une redirection
        if ($exception instanceof HttpExceptionInterface || $exception instanceof NotFoundHttpException) {
            // Génère l'URL de la racine
            $url = $this->router->generate('app_event_index');

            // Crée et définit la réponse de redirection
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }
}