<?php
declare(strict_types=1);

namespace WebmanMicro\PhpServiceDiscovery\Traits;

use Psr\Log\LoggerInterface;
use support\Log;

trait Logger
{
    protected ?string $logChannel = null;

    /**
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        if ($this->logChannel === null) {
            $this->logChannel = config('plugin.webman-micro.php-service-discovery.app.log_channel', 'default');
        }

        return Log::channel($this->logChannel);
    }
}
