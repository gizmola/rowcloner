<?php declare(strict_types=1);

namespace App\Gizmola;

trait DebugTrait {
    private function getDebugMode(): bool
    {
        $debugMode = $_ENV['RC_DEBUG'] ?? '';
        return ($debugMode == 'true');
    }

    /**
     * @param mixed $message 
     * @param mixed $data 
     * @return void
     * 
     * Only logs if configured for debugging 
     */
    public function debug($message, $data): void
    {
        
        if ($this->getDebugMode()) {
            $this->logger->debug($message, $data);
        }
    }
}