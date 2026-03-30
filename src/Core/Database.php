<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getInstance(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }

    private static function connect(): void
    {
        try {
            $host = getenv('DB_HOST');
            $port = getenv('DB_PORT') ?: 3306;
            $database = getenv('DB_DATABASE');
            $username = getenv('DB_USERNAME');
            $password = getenv('DB_PASSWORD');

            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

            self::$connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            Response::error(
                'Database connection failed',
                500
            )->send();
        }
    }

    public static function close(): void
    {
        self::$connection = null;
    }
}
