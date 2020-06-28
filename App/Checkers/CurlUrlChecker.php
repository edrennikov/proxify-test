<?php

namespace App\Checkers;

use App\Url;

class CurlUrlChecker implements UrlChecker
{
    private $timeout;

    public function __construct(int $timout)
    {
        $this->timeout = $timout;
    }

    public function getHttpCode(Url $url): int
    {
        $ch = \curl_init($url->url);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        \curl_exec($ch);
        return intval(\curl_getinfo($ch, CURLINFO_RESPONSE_CODE));
    }
}