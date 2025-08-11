<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Helpers\EnvironmentHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'An error occurred';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } elseif (!EnvironmentHelper::isInProdMode()) {
            $code = $exception->getCode();
            $statusCode = (is_int($code) && $code >= 100 && $code <= 599) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $exception->getMessage();
        }

        $response = new JsonResponse([
            'error' => [
                'message' => $message,
                'code' => $statusCode,
            ]
        ], $statusCode);

        $event->setResponse($response);
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
