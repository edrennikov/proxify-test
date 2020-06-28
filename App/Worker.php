<?php

namespace App;

use App\Checkers\UrlChecker;
use App\Exceptions\CanNotStartTransaction;
use App\Loggers\Logger;
use App\Repositories\UrlRepository;

class Worker
{
    private $urlRepository;
    private $urlChecker;
    private $logger;

    public function __construct(UrlRepository $urlRepository, UrlChecker $urlChecker, Logger $logger)
    {
        $this->urlRepository = $urlRepository;
        $this->urlChecker = $urlChecker;
        $this->logger = $logger;
    }

    public function run()
    {
        try {
            $this->logger->log('Begin loop');
            while (true) {
                $url = $this->urlRepository->getUrlForProcessing();

                if (!$url) {
                    $this->logger->log('No more urls. Exit');
                    break;
                }

                $url->http_code = null;
                $url->status = Url::STATUS_ERROR;
                try {
                    $http_code = $this->urlChecker->getHttpCode($url);
                    if ($http_code) {
                        $url->http_code = $http_code;
                        $url->status = Url::STATUS_DONE;
                        $this->logger->log("DONE: HTTP CODE: $http_code, URL: {$url->url}");
                    } else {
                        $this->logger->log("ERROR: URL: {$url->url}");
                    }
                } catch (\Throwable $t) {
                    $this->logger->log("EXCEPTION: {$t->getMessage()} URL: {$url->url}");
                }
                $this->urlRepository->updateUrl($url);
            }
        } catch (CanNotStartTransaction $e) {
            $this->logger->log('Can not start transaction. Exit');
        } catch (\Throwable $t) {
            $this->logger->log("{$t->getMessage()}\n{$t->getTraceAsString()}");
        }
    }
}