<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final class KernelSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $env;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param string              $env
     * @param TranslatorInterface $translator
     */
    public function __construct(string $env, TranslatorInterface $translator)
    {
        $this->env = $env;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    /**
     * Handle Kernel exception.
     *
     * @param ExceptionEvent $event Exception event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getException();

        if ($exception instanceof ApiException) {
            $data = $exception->getExceptionDetails();
            $response = new JsonResponse($data, $exception->getStatusCode());
            $event->setResponse($response);
        } elseif ($exception instanceof HttpException && in_array($exception->getStatusCode(), [401, 403], true)) {
            $response = new JsonResponse(['error' => $this->translator->trans($exception->getMessage())], $exception->getStatusCode());
            $event->setResponse($response);
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ('dev' === $this->env) {
            $response = $event->getResponse();
            $response->headers->set('Symfony-Debug-Toolbar-Replace', '1');
        }
    }
}
