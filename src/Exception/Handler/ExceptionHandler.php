<?php

namespace App\Exception\Handler;

namespace App\Exception\Handler;

use App\Exception\AppException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionHandler
{
    private string $logFilePath;

    public function __construct(
        string $kernelLogsDir
    ) {
        $this->logFilePath = $kernelLogsDir.'/exceptions.log';
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Запись в лог-файл
        $this->logToFile($exception);

        // Чтение последних ошибок из файла (опционально)
        $lastErrors = $this->readLastErrors(5);

        if ($exception instanceof AppException) {
            $response = new JsonResponse([
                'error' => [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'context' => $exception->getContext(),
                    'last_errors' => $lastErrors // Добавляем последние ошибки
                ]
            ], $this->getStatusCode($exception));

            $event->setResponse($response);
        }
    }

    private function logToFile(\Throwable $exception): void
    {
        $logEntry = sprintf(
            "[%s] %s: %s in %s:%d\nContext: %s\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            json_encode($exception instanceof AppException ? $exception->getContext() : []),
            $exception->getTraceAsString()
        );

        file_put_contents(
            $this->logFilePath,
            $logEntry,
            FILE_APPEND | LOCK_EX
        );
    }

    private function readLastErrors(int $count): array
    {
        if (!file_exists($this->logFilePath)) {
            return [];
        }

        $content = file_get_contents($this->logFilePath);
        $entries = explode("\n\n", trim($content));

        return array_slice(array_reverse($entries), 0, $count);
    }

    private function getStatusCode(AppException $exception): int
    {
        return match (true) {
            $exception->getCode() >= 400 && $exception->getCode() < 500 => $exception->getCode(),
            default => Response::HTTP_BAD_REQUEST,
        };
    }
}