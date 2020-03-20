<?php

namespace App\EventSubscriber;

use App\Annotations\MiddlewareAnnotationInterface;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ControllerSubscriber implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var Reader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    /**
     * @var ControllerEvent
     */
    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        [$controllerInstance, $methodName] = $controller;
        $request = $event->getRequest();

        $this->handleClassAnnotations($controllerInstance, $methodName, $request);
        $this->handleMethodAnnotations($controllerInstance, $methodName, $request);
    }

    private function handleClassAnnotations($controllerInstance, $methodName, Request $request): void
    {
        $controllerReflClass = new \ReflectionClass($controllerInstance);
        $controllerAnnotations = $this->annotationReader->getClassAnnotations($controllerReflClass);

        foreach ($controllerAnnotations as $annotation) {
            if ($annotation instanceof MiddlewareAnnotationInterface) {
                $annotation->handle($request);
            }
        }
    }

    private function handleMethodAnnotations($controllerInstance, $methodName, Request $request): void
    {
        $methodReflClass = new \ReflectionMethod($controllerInstance, $methodName);
        $methodAnnotations = $this->annotationReader->getMethodAnnotations($methodReflClass);

        foreach ($methodAnnotations as $annotation) {
            if ($annotation instanceof MiddlewareAnnotationInterface) {
                $annotation->handle($request);
            }
        }
    }
}
