<?php

declare(strict_types=1);

namespace App\Torture\Infrastructure;

final class RequestMetrics
{
    public static bool $active = false;
    public static array $queries = [];
    public static array $transactions = [];
    public static int $rows = 0;
    public static int $updates = 0;
    public static string $fault = '';

    public static function start(string $fault): void
    {
        self::$active = true;
        self::$queries = self::$transactions = [];
        self::$rows = self::$updates = 0;
        self::$fault = $fault;
    }

    public static function query(string $sql, string $database): void
    {
        if (!self::$active) { return; }
        self::$queries[] = ['sql' => preg_replace('/\s+/', ' ', $sql), 'database' => $database];
        if (preg_match('/^UPDATE\b/i', $sql) && ++self::$updates === 2 && str_starts_with(self::$fault, 'sqlstate:')) {
            $state = substr(self::$fault, 9);
            $error = new \PDOException('Injected transaction failure');
            $error->errorInfo = [$state, 7, 'Injected transaction failure'];
            throw \Doctrine\DBAL\Driver\PDO\Exception::new($error);
        }
    }
}
