<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController
{
    #[Route(path: '/_error/{code}', name: 'app_error')]
    public function show(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();

        if ($statusCode === 404) {
            return new JsonResponse([
                'error' => 'Not Found',
                'message' => 'This route is more lost than a junior dev in a legacy monolith. Double-check your URL, your router config, and your life choices.',
            ], 404);
        }

        return new JsonResponse([
            'error' => 'Unexpected Error',
            'message' => $exception->getMessage(),
        ], $statusCode);
    }
}
