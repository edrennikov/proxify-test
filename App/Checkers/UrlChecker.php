<?php

namespace App\Checkers;

use App\Url;

interface UrlChecker
{
    public function getHttpCode(Url $url): int;
}