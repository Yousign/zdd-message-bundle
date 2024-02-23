<?php

namespace Yousign\ZddMessageBundle\Tests\Fixtures\App\Logger;

use Psr\Log\AbstractLogger;

final class SpyLogger extends AbstractLogger
{
    private array $logs = [];

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function hasRecord(string|\Stringable $message, string $level, array $context = []): bool
    {
        foreach ($this->logs as $log) {
            if ($log['level'] === $level && $log['message'] === $message && $log['context'] === $context) {
                return true;
            }
        }

        return false;
    }
}
