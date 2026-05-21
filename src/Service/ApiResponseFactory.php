<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponseFactory
{
    public function success(mixed $data = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse(['success' => true, 'data' => $data], $status);
    }

    public function error(string $code, string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }
}
