<?php

declare(strict_types=1);

namespace App\Torture\Infrastructure;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\{Connection, Middleware, Result, Statement};
use Doctrine\DBAL\Driver\Middleware\{AbstractConnectionMiddleware, AbstractDriverMiddleware, AbstractResultMiddleware, AbstractStatementMiddleware};

final class SqlMetricsMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new class($driver) extends AbstractDriverMiddleware {
            public function connect(array $params): Connection
            {
                $database = (string) ($params['dbname'] ?? 'unknown');
                $connection = parent::connect($params);
                if (RequestMetrics::$active && RequestMetrics::$fault === 'real-lock-timeout' && str_contains($database, '_pg_')) {
                    $connection->exec("SET lock_timeout = '100ms'");
                }
                return new class($connection, $database) extends AbstractConnectionMiddleware {
                    public function __construct(Connection $connection, private readonly string $database) { parent::__construct($connection); }
                    public function prepare(string $sql): Statement
                    {
                        if (RequestMetrics::$active && RequestMetrics::$fault === 'real-lock-timeout' && str_contains($this->database, '_pg_')) {
                            parent::exec("SET lock_timeout = '100ms'");
                        }
                        return new class(parent::prepare($sql), $sql, $this->database) extends AbstractStatementMiddleware {
                            public function __construct(Statement $statement, private readonly string $sql, private readonly string $database) { parent::__construct($statement); }
                            public function execute(): Result
                            {
                                RequestMetrics::query($this->sql, $this->database);
                                return SqlMetricsMiddleware::result(parent::execute());
                            }
                        };
                    }
                    public function query(string $sql): Result { RequestMetrics::query($sql, $this->database); return SqlMetricsMiddleware::result(parent::query($sql)); }
                    public function exec(string $sql): int|string { RequestMetrics::query($sql, $this->database); return parent::exec($sql); }
                    public function beginTransaction(): void { if (RequestMetrics::$active) { RequestMetrics::$transactions[] = ['event' => 'BEGIN', 'database' => $this->database]; } parent::beginTransaction(); }
                    public function commit(): void
                    {
                        if (RequestMetrics::$active) {
                            RequestMetrics::$transactions[] = ['event' => 'COMMIT', 'database' => $this->database];
                            if (RequestMetrics::$fault === 'commit:mysql' && str_contains($this->database, 'mysql')) { throw new \RuntimeException('Injected second-manager commit failure'); }
                        }
                        parent::commit();
                    }
                    public function rollBack(): void { if (RequestMetrics::$active) { RequestMetrics::$transactions[] = ['event' => 'ROLLBACK', 'database' => $this->database]; } parent::rollBack(); }
                };
            }
        };
    }

    public static function result(Result $result): Result
    {
        return new class($result) extends AbstractResultMiddleware {
            public function fetchNumeric(): array|false { $row = parent::fetchNumeric(); if (RequestMetrics::$active && $row !== false) { ++RequestMetrics::$rows; } return $row; }
            public function fetchAssociative(): array|false { $row = parent::fetchAssociative(); if (RequestMetrics::$active && $row !== false) { ++RequestMetrics::$rows; } return $row; }
            public function fetchOne(): mixed { $row = parent::fetchOne(); if (RequestMetrics::$active && $row !== false) { ++RequestMetrics::$rows; } return $row; }
            public function fetchAllNumeric(): array { $rows = parent::fetchAllNumeric(); if (RequestMetrics::$active) { RequestMetrics::$rows += count($rows); } return $rows; }
            public function fetchAllAssociative(): array { $rows = parent::fetchAllAssociative(); if (RequestMetrics::$active) { RequestMetrics::$rows += count($rows); } return $rows; }
            public function fetchFirstColumn(): array { $rows = parent::fetchFirstColumn(); if (RequestMetrics::$active) { RequestMetrics::$rows += count($rows); } return $rows; }
        };
    }
}
