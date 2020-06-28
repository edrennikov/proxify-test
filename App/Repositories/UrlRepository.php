<?php

namespace App\Repositories;

use App\Exceptions\CanNotStartTransaction;
use App\Url;

interface UrlRepository
{
    /**
     * @return Url|null
     * @throws CanNotStartTransaction
     * @throws \Throwable
     */
    public function getUrlForProcessing(): ?Url;
    public function updateUrl(Url $url): void;
}