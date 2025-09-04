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
        $title = 'Internal Server Error';
        $detail = 'An error occurred';
        $violations = [];

        if ($exception instanceof UnprocessableEntityHttpException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $title = 'Unprocessable Entity';
            $detail = $exception->getMessage();
        } elseif ($exception instanceof ValidationFailedException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $title = 'Validation Failed';
            $violationList = $exception->getViolations();
            
            foreach ($violationList as $violation) {
                $violations[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            
            $detail = sprintf(
                'Validation failed with %d violation(s)',
                count($violations)
            );
        } elseif ($exception instanceof \InvalidArgumentException) {
            $detail = $exception->getMessage();
            if (str_contains($detail, 'should not be blank') ||
                str_contains($detail, 'should be greater than') ||
                str_contains($detail, 'should be positive') ||
                str_contains($detail, 'is not a valid email') ||
                str_contains($detail, 'at least one item is required')) {
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
                $title = 'Unprocessable Entity';
            } else {
                $statusCode = Response::HTTP_BAD_REQUEST;
                $title = 'Bad Request';
            }
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $title = match ($statusCode) {
                404 => 'Not Found',
                400 => 'Bad Request',
                422 => 'Unprocessable Entity',
                default => 'HTTP Error',
            };
            $detail = $exception->getMessage();
        }

        $problemData = [
            'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
            'title' => $title,
            'status' => $statusCode,
            'detail' => $detail,
        ];

        if (!empty($violations)) {
            $problemData['violations'] = $violations;
        }

        $response = new JsonResponse(
            $problemData,
            $statusCode,
            ['Content-Type' => 'application/problem+json']
        );

        $event->setResponse($response);
    }
}
