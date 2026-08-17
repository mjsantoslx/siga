<?php
declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function error(string $message, ?\Throwable $exception = null): void
    {
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = '[' . date('Y-m-d H:i:s') . '] ERROR ' . $message;
        if ($exception) {
            $line .= ' | ' . get_class($exception) . ': ' . $exception->getMessage()
                . ' | ' . $exception->getFile() . ':' . $exception->getLine()
                . PHP_EOL . $exception->getTraceAsString();
        }
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'erros.log', $line . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
