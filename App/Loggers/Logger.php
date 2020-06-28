<?php

namespace App\Loggers;

interface Logger
{
    public function log(string $message): void;
}