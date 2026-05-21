<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use App\Service\ApiResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ApiResponseFactory $apiResponse,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', 10]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof ApiException) {
            $event->setResponse($this->apiResponse->error(
                $exception->errorCode,
                $exception->getMessage(),
                $exception->httpStatus,
            ));

            return;
        }

        $previous = $exception->getPrevious();
        if ($previous instanceof ValidationFailedException) {
            $violations = [];
            foreach ($previous->getViolations() as $violation) {
                $violations[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'validation_failed',
                    'message' => 'Validation failed.',
                    'violations' => $violations,
                ],
            ], 422));

            return;
        }

        if ($exception instanceof HttpExceptionInterface && $request->getPathInfo() !== '/api/docs') {
            $status = $exception->getStatusCode();
            if ($status >= 400 && $status < 600) {
                $code = match ($status) {
                    401 => 'unauthorized',
                    403 => 'forbidden',
                    404 => 'not_found',
                    405 => 'method_not_allowed',
                    default => 'http_error',
                };

                $event->setResponse($this->apiResponse->error(
                    $code,
                    $exception->getMessage() ?: 'Request could not be completed.',
                    $status,
                ));
            }
        }
    }
}
