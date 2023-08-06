<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Logger;

use Psr\Log\LoggerInterface;

final class SpyLogger implements LoggerInterface
{
    private array $logs = [];

    public function emergency(\Stringable|string $message, array $context = []): void
    {
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logs[$level] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function getLogs(string $level): ?array
    {
        return $this->logs[$level] ?? null;
    }
}
