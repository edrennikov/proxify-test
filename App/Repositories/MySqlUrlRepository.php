<?php

namespace App\Repositories;

use App\Exceptions\CanNotStartTransaction;
use App\Exceptions\NoMoreUrls;
use App\Url;

class MySqlUrlRepository implements UrlRepository
{
    private $pdo;
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Url|null
     * @throws CanNotStartTransaction
     * @throws \Throwable
     */
    public function getUrlForProcessing(): ?Url
    {
        if (!$this->pdo->beginTransaction()) throw new CanNotStartTransaction();
        try {
            $getUrlStatement = $this->pdo->prepare(
                "SELECT id, url, status, http_code FROM urls WHERE status=:status LIMIT 1 FOR UPDATE"
            );
            $getUrlStatement->bindValue(':status', Url::STATUS_NEW, \PDO::PARAM_STR);
            $getUrlStatement->execute();

            if ($getUrlStatement->rowCount() == 0) return null;

            $row = $getUrlStatement->fetch();
            $url = new Url($row['id'], $row['url'], $row['status'], $row['http_code']);

            $updateStatement = $this->pdo->prepare("UPDATE urls SET status=:status WHERE id=:id");
            $updateStatement->bindValue(':id', $url->id, \PDO::PARAM_INT);
            $updateStatement->bindValue(':status', Url::STATUS_PROCESSING, \PDO::PARAM_STR);

            $this->pdo->commit();
            return $url;
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }

    public function updateUrl(Url $url): void
    {
        $statement = $this->pdo->prepare("UPDATE urls SET status=:status, http_code=:http_code WHERE id=:id");
        $statement->bindValue(':id', $url->id, \PDO::PARAM_INT);
        $statement->bindValue(':status', $url->status, \PDO::PARAM_STR);
        $statement->bindValue(':http_code', $url->http_code, \PDO::PARAM_INT);
        $statement->execute();
    }
}