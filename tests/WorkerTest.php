<?php

namespace Tests;

use App\Checkers\UrlChecker;
use App\Exceptions\CanNotStartTransaction;
use App\Loggers\Logger;
use App\Repositories\UrlRepository;
use App\Url;
use App\Worker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WorkerTest extends TestCase
{
    public function dataProvider_testWorker()
    {
        return [
            'DONE' => [
                'url' => 'good-url',
                'http_code' => 357,
                'expected_status' => Url::STATUS_DONE,
                'expected_http_code' => 357,
                'expected_log' => "Begin loop\nDONE: HTTP CODE: 357, URL: good-url\nNo more urls. Exit\n",
            ],
            'ERROR' => [
                'url' => 'bad-url',
                'http_code' => 0,
                'expected_status' => Url::STATUS_ERROR,
                'expected_http_code' => null,
                'expected_log' => "Begin loop\nERROR: URL: bad-url\nNo more urls. Exit\n",
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_testWorker
     */
    public function testWorker($url, $http_code, $expected_status, $expected_http_code, $expected_log)
    {
        $id = 42;
        $urlObj = new Url($id, $url, null, null);
        $urlForUpdate = new Url($id, $url, $expected_status, $expected_http_code);

        /** @var UrlRepository|MockObject $urlRepository */
        $urlRepository = $this->getMockBuilder(UrlRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getUrlForProcessing',
                    'updateUrl',
                ]
            )
            ->getMockForAbstractClass();
        $urlRepository->expects($this->exactly(2))
            ->method('getUrlForProcessing')
            ->willReturnOnConsecutiveCalls($urlObj, null);
        $urlRepository->expects($this->once())
            ->method('updateUrl')
            ->with($this->callback(function ($arg) use ($urlForUpdate) {
                return $urlForUpdate->equals($arg);
            }));

        /** @var UrlChecker|MockObject $urlChecker */
        $urlChecker = $this->getMockBuilder(UrlChecker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getHttpCode',
                ]
            )
            ->getMockForAbstractClass();
        $urlChecker->expects($this->once())
            ->method('getHttpCode')
            ->with($this->callback(function ($arg) use ($urlObj) {
                return $urlObj->equals($arg);
            }))
            ->willReturn($http_code);

        $log = '';
        /** @var Logger|MockObject $logger */
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'log',
                ]
            )
            ->getMockForAbstractClass();
        $logger->method('log')
            ->willReturnCallback(function ($msg) use (&$log) {
                $log .= "$msg\n";
            });

        $worker = new Worker($urlRepository, $urlChecker, $logger);
        $worker->run();

        $this->assertEquals($expected_log, $log);
    }

    public function testExceptionWhileCheckingUrl()
    {
        $id = 42;
        $url = 'some-url';
        $urlObj = new Url($id, $url, null, null);
        $urlForUpdate = new Url($id, $url, Url::STATUS_ERROR, null);

        /** @var UrlRepository|MockObject $urlRepository */
        $urlRepository = $this->getMockBuilder(UrlRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getUrlForProcessing',
                    'updateUrl',
                ]
            )
            ->getMockForAbstractClass();
        $urlRepository->expects($this->exactly(2))
            ->method('getUrlForProcessing')
            ->willReturnOnConsecutiveCalls($urlObj, null);
        $urlRepository->expects($this->once())
            ->method('updateUrl')
            ->with($this->callback(function ($arg) use ($urlForUpdate) {
                return $urlForUpdate->equals($arg);
            }));

        /** @var UrlChecker|MockObject $urlChecker */
        $urlChecker = $this->getMockBuilder(UrlChecker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getHttpCode',
                ]
            )
            ->getMockForAbstractClass();
        $urlChecker->expects($this->once())
            ->method('getHttpCode')
            ->with($this->callback(function ($arg) use ($urlObj) {
                return $urlObj->equals($arg);
            }))
            ->willThrowException(new \Exception('Bad URL!!!'));

        $log = '';
        /** @var Logger|MockObject $logger */
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'log',
                ]
            )
            ->getMockForAbstractClass();
        $logger->method('log')
            ->willReturnCallback(function ($msg) use (&$log) {
                $log .= "$msg\n";
            });

        $worker = new Worker($urlRepository, $urlChecker, $logger);
        $worker->run();

        $this->assertEquals("Begin loop\nEXCEPTION: Bad URL!!! URL: $url\nNo more urls. Exit\n", $log);
    }

    public function testCanNotStartTransaction()
    {
        /** @var UrlRepository|MockObject $urlRepository */
        $urlRepository = $this->getMockBuilder(UrlRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getUrlForProcessing',
                    'updateUrl',
                ]
            )
            ->getMockForAbstractClass();
        $urlRepository->expects($this->once())
            ->method('getUrlForProcessing')
            ->willThrowException(new CanNotStartTransaction());
        $urlRepository->expects($this->never())
            ->method('updateUrl');

        /** @var UrlChecker|MockObject $urlChecker */
        $urlChecker = $this->getMockBuilder(UrlChecker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getHttpCode',
                ]
            )
            ->getMockForAbstractClass();
        $urlChecker->expects($this->never())
            ->method('getHttpCode');

        $log = '';
        /** @var Logger|MockObject $logger */
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'log',
                ]
            )
            ->getMockForAbstractClass();
        $logger->method('log')
            ->willReturnCallback(function ($msg) use (&$log) {
                $log .= "$msg\n";
            });

        $worker = new Worker($urlRepository, $urlChecker, $logger);
        $worker->run();

        $this->assertEquals("Begin loop\nCan not start transaction. Exit\n", $log);
    }
}