<?php

namespace App\Loggers;

class ConsoleLogger implements Logger
{
    public function log(string $message): void
    {
        $date = \date('Y-m-d H:i:s T');
        echo "[$date] $message\n";
    }
}