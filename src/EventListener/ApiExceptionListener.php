<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'An error occurred';



        if ($exception instanceof UnprocessableEntityHttpException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $message = $exception->getMessage();
        } elseif ($exception instanceof ValidationFailedException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $violations = $exception->getViolations();
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            $message = implode(', ', $errors);
        } elseif ($exception instanceof \InvalidArgumentException) {
            $message = $exception->getMessage();
            if (str_contains($message, 'should not be blank') ||
                str_contains($message, 'should be greater than') ||
                str_contains($message, 'should be positive') ||
                str_contains($message, 'is not a valid email') ||
                str_contains($message, 'at least one item is required')) {
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            } else {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } else {
            $message = $exception->getMessage() ?: 'Unknown error';
            if (str_contains(strtolower($message), 'validation') ||
                str_contains(strtolower($message), 'constraint')) {
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        }

        $response = new JsonResponse([
            'error' => $message,
        ], $statusCode);

        $event->setResponse($response);
    }
}
