<?php

namespace App;

class Url
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_DONE = 'DONE';
    public const STATUS_ERROR = 'ERROR';

    public $id;
    public $url;
    public $status;
    public $http_code;

    public function __construct(int $id, string $url, ?string $status, ?int $http_code)
    {
        $this->id = $id;
        $this->url = $url;
        $this->status = $status;
        $this->http_code = $http_code;
    }

    public function equals($url): bool
    {
        return $url instanceof self
            && $this->id === $url->id
            && $this->url === $url->url
            && $this->status === $url->status
            && $this->http_code === $url->http_code;
    }
}